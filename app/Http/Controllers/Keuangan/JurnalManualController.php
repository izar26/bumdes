<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\JurnalUmum;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class JurnalManualController extends Controller
{
    public function create()
    {
        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun')->get();
        
        $user = auth()->user();
        $unitUsahas = collect(); // Buat koleksi kosong

        if ($user->hasRole('bendahara_bumdes')) {
            // Jika bendahara, ambil semua unit usaha
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        } else {
            // Jika bukan, ambil hanya unit usaha yang dia kelola
            $unitUsahas = $user->unitUsahas()->where('status_operasi', 'Aktif')->get();
        }

        return view('keuangan.jurnal_manual.create', compact('akuns', 'unitUsahas'));
    }

    public function store(Request $request)
    {
        // 1. Validasi data utama
        $request->validate([
            'tanggal_transaksi' => 'required|date',
            'deskripsi' => 'required|string|max:500',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id',
            'details' => 'required|array|min:2',
            'details.*.akun_id' => 'required|exists:akuns,akun_id',
            'details.*.debit' => 'required|numeric|min:0',
            'details.*.kredit' => 'required|numeric|min:0',
            'details.*.keterangan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            
            // 2. Validasi Keseimbangan & Logika Debit/Kredit
            $totalDebit = 0;
            $totalKredit = 0;
            foreach ($request->details as $detail) {
                if ($detail['debit'] > 0 && $detail['kredit'] > 0) {
                    throw new \Exception('Satu baris tidak boleh memiliki Debit dan Kredit sekaligus.');
                }
                if ($detail['debit'] == 0 && $detail['kredit'] == 0) {
                    throw new \Exception('Setiap baris harus memiliki nilai Debit atau Kredit.');
                }
                $totalDebit += $detail['debit'];
                $totalKredit += $detail['kredit'];
            }

            if (round($totalDebit, 2) !== round($totalKredit, 2)) {
                throw new \Exception('Total Debit dan Kredit tidak seimbang.');
            }

            // 3. Simpan Jurnal Umum (Header)
            $jurnal = JurnalUmum::create([
                'bungdes_id' => 1, // Asumsi
                'user_id' => Auth::id(),
                'unit_usaha_id' => $request->unit_usaha_id,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'deskripsi' => $request->deskripsi,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
            ]);

            // 4. Simpan Detail Jurnal
            foreach ($request->details as $detail) {
                DetailJurnal::create([
                    'jurnal_id' => $jurnal->jurnal_id,
                    'akun_id' => $detail['akun_id'],
                    'debit' => $detail['debit'],
                    'kredit' => $detail['kredit'],
                    'keterangan' => $detail['keterangan'],
                ]);
            }

            DB::commit();
            return redirect()->route('jurnal-umum.index')->with('success', 'Jurnal berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan jurnal: ' . $e->getMessage())->withInput();
        }
    }
}