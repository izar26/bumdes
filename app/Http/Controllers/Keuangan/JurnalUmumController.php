<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmum;
use App\Models\Akun;
use App\Models\DetailJurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // <-- Pastikan ini ada

class JurnalUmumController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Mulai query builder
        $jurnalQuery = JurnalUmum::with('detailJurnals.akun')
                                 ->latest('tanggal_transaksi');

        // Terapkan filter berdasarkan peran
        if ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
            // 1. Ambil ID semua unit usaha yang dikelola oleh user ini
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usaha_id');

            // 2. Filter jurnal agar hanya menampilkan yang unit_usaha_id-nya cocok
            $jurnalQuery->whereIn('unit_usaha_id', $unitUsahaIds);
        }
        // Jika user adalah bendahara atau peran lain yang lebih tinggi, tidak ada filter yang diterapkan (melihat semua)

        // Eksekusi query untuk mendapatkan hasilnya
        $jurnals = $jurnalQuery->get();

        return view('keuangan.jurnal.index', compact('jurnals'));
    }

    public function edit(JurnalUmum $jurnalUmum)
    {
        $jurnal = $jurnalUmum->load('detailJurnals');
        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun')->get();
        return view('keuangan.jurnal.edit', compact('jurnal', 'akuns'));
    }

    public function update(Request $request, JurnalUmum $jurnalUmum)
    {
        $request->validate([
            'tanggal_transaksi' => 'required|date',
            'deskripsi' => 'required|string|max:500',
            'details' => 'required|array|min:2',
            'details.*.akun_id' => 'required|exists:akuns,akun_id',
            'details.*.debit' => 'required|numeric|min:0',
            'details.*.kredit' => 'required|numeric|min:0',
            'details.*.keterangan' => 'nullable|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();

            $totalDebit = 0; $totalKredit = 0;
            foreach ($request->details as $detail) {
                if ($detail['debit'] > 0 && $detail['kredit'] > 0) throw new \Exception('Satu baris tidak boleh memiliki Debit dan Kredit sekaligus.');
                if ($detail['debit'] == 0 && $detail['kredit'] == 0) throw new \Exception('Setiap baris harus memiliki nilai Debit atau Kredit.');
                $totalDebit += $detail['debit'];
                $totalKredit += $detail['kredit'];
            }
            if (round($totalDebit, 2) !== round($totalKredit, 2)) throw new \Exception('Total Debit dan Kredit tidak seimbang.');

            $jurnalUmum->update([
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'deskripsi' => $request->deskripsi,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
            ]);

            $jurnalUmum->detailJurnals()->delete();
            foreach ($request->details as $detail) {
                DetailJurnal::create([
                    'jurnal_id' => $jurnalUmum->jurnal_id,
                    'akun_id' => $detail['akun_id'],
                    'debit' => $detail['debit'],
                    'kredit' => $detail['kredit'],
                    'keterangan' => $detail['keterangan'],
                ]);
            }

            DB::commit();
            return redirect()->route('jurnal-umum.index')->with('success', 'Jurnal berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui jurnal: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(JurnalUmum $jurnalUmum)
    {
        $jurnalUmum->delete();
        return redirect()->route('jurnal-umum.index')->with('success', 'Jurnal berhasil dihapus.');
    }
}