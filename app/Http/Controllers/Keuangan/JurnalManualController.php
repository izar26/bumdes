<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Akun;
use App\Models\JurnalUmum;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;

class JurnalManualController extends Controller
{
    /**
     * Menampilkan form untuk membuat jurnal baru.
     */
    public function create()
    {
        $user = Auth::user();
        // Mengambil semua akun detail, diurutkan berdasarkan kode
        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun')->get();
        $unitUsahas = collect();

        if ($user->hasAnyRole(['bendahara_bumdes', 'admin_bumdes', 'direktur_bumdes', 'sekretaris_bumdes'])) {
            $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        } 
        elseif ($user->hasAnyRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
            $unitUsahas = $user->unitUsahas()
                                ->where('status_operasi', 'Aktif')
                                ->select('unit_usahas.unit_usaha_id', 'unit_usahas.nama_unit')
                                ->orderBy('nama_unit')
                                ->get();
        }

        return view('keuangan.jurnal_manual.create', compact('akuns', 'unitUsahas'));
    }

    /**
     * Menyimpan jurnal baru ke database.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'tanggal_transaksi' => 'required|date',
            'deskripsi' => 'required|string|max:500',
            'details' => 'required|array|min:2',
            'details.*.akun_id' => 'required|exists:akuns,akun_id',
            'details.*.debit' => 'required|numeric|min:0',
            'details.*.kredit' => 'required|numeric|min:0',
            'details.*.keterangan' => 'nullable|string|max:255',
            'unit_usaha_id' => 'nullable|string', // Boleh null atau 'pusat'
        ];

        // Validasi khusus untuk user unit usaha
        if ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
            $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id')->toArray();
            $rules['unit_usaha_id'] = [
                'required',
                Rule::in($managedUnitUsahaIds)
            ];
        } else {
             // Untuk admin BUMDes, pastikan jika bukan 'pusat', ID-nya ada di database
             if ($request->unit_usaha_id !== 'pusat') {
                 $rules['unit_usaha_id'] = 'nullable|exists:unit_usahas,unit_usaha_id';
             }
        }
        
        $request->validate($rules);

        try {
            DB::beginTransaction();

            $totalDebit = collect($request->details)->sum(function($detail) {
                return (float) str_replace(['.', ','], ['', '.'], $detail['debit']);
            });
            $totalKredit = collect($request->details)->sum(function($detail) {
                return (float) str_replace(['.', ','], ['', '.'], $detail['kredit']);
            });

            // Pengecekan keseimbangan yang lebih aman
            if (abs($totalDebit - $totalKredit) > 0.01 || $totalDebit == 0) {
                throw new \Exception('Total Debit dan Kredit tidak seimbang atau nol.');
            }

            $jurnal = JurnalUmum::create([
                'user_id' => $user->user_id,
                // Handle 'pusat' sebagai NULL
                'unit_usaha_id' => $request->unit_usaha_id === 'pusat' ? null : $request->unit_usaha_id,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'deskripsi' => $request->deskripsi,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
            ]);

            foreach ($request->details as $detail) {
                 $debitValue = (float) str_replace(['.', ','], ['', '.'], $detail['debit']);
                 $kreditValue = (float) str_replace(['.', ','], ['', '.'], $detail['kredit']);

                if ($debitValue > 0 || $kreditValue > 0) {
                    DetailJurnal::create([
                        'jurnal_id' => $jurnal->jurnal_id,
                        'akun_id' => $detail['akun_id'],
                        'debit' => $debitValue,
                        'kredit' => $kreditValue,
                        'keterangan' => $detail['keterangan'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('jurnal-umum.index')->with('success', 'Jurnal berhasil disimpan dan menunggu persetujuan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan jurnal: ' . $e->getMessage())->withInput();
        }
    }
}
