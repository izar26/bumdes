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
        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun')->get(); // Hanya ambil akun detail
        $unitUsahas = collect();

        // Admin BUMDes dan Bendahara bisa memilih unit usaha mana saja (termasuk "Pusat"/null)
        if ($user->hasRole(['bendahara_bumdes', 'admin_bumdes', 'direktur_bumdes'])) {
            $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        } 
        // Admin/Manajer Unit Usaha hanya bisa memilih unit usahanya sendiri
        elseif ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
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

        // Validasi dasar
        $rules = [
            'tanggal_transaksi' => 'required|date',
            'deskripsi' => 'required|string|max:500',
            'details' => 'required|array|min:2',
            'details.*.akun_id' => 'required|exists:akuns,akun_id',
            'details.*.debit' => 'required|numeric|min:0',
            'details.*.kredit' => 'required|numeric|min:0',
            'details.*.keterangan' => 'nullable|string|max:255',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id', // Boleh null untuk jurnal pusat
        ];

        // --- PERBAIKAN LOGIKA VALIDASI UNIT USAHA ---
        // Jika pengguna adalah admin/manajer unit, mereka WAJIB memilih unit usaha
        // dan unit usaha yang dipilih HARUS salah satu yang mereka kelola.
        if ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
            $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id')->toArray();
            $rules['unit_usaha_id'] = [
                'required', // Wajib diisi
                Rule::in($managedUnitUsahaIds) // Harus ada di dalam daftar unit yang dikelola
            ];
        }
        
        $request->validate($rules);

        try {
            DB::beginTransaction();

            $totalDebit = collect($request->details)->sum('debit');
            $totalKredit = collect($request->details)->sum('kredit');

            if (round($totalDebit, 2) !== round($totalKredit, 2) || $totalDebit == 0) {
                throw new \Exception('Total Debit dan Kredit tidak seimbang atau nol.');
            }

            $jurnal = JurnalUmum::create([
                'user_id' => $user->user_id,
                'unit_usaha_id' => $request->unit_usaha_id, // Langsung dari request
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'deskripsi' => $request->deskripsi,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                // Status default 'menunggu' sudah di-set di model/database
            ]);

            foreach ($request->details as $detail) {
                // Pastikan salah satu antara debit atau kredit tidak nol
                if ($detail['debit'] > 0 || $detail['kredit'] > 0) {
                    DetailJurnal::create([
                        'jurnal_id' => $jurnal->jurnal_id,
                        'akun_id' => $detail['akun_id'],
                        'debit' => $detail['debit'],
                        'kredit' => $detail['kredit'],
                        'keterangan' => $detail['keterangan'] ?? null,
                    ]);
                }
            }

            DB::commit();
            // Ganti nama route jika berbeda
            return redirect()->route('jurnal-umum.index')->with('success', 'Jurnal berhasil disimpan dan menunggu persetujuan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan jurnal: ' . $e->getMessage())->withInput();
        }
    }
}
