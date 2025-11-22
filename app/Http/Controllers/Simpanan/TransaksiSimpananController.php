<?php

namespace App\Http\Controllers\Simpanan;

use App\Models\RekeningSimpanan;
use App\Models\TransaksiSimpanan; // Pastikan Model ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TransaksiSimpananController extends Controller
{
    // =========================================================================
    // FITUR SETOR TUNAI
    // =========================================================================

    /**
     * Menampilkan Form Setor Tunai
     */
    public function createSetor()
    {
        // 1. Ambil data rekening untuk Dropdown Select2
        // Kita load relasi 'anggota' agar bisa menampilkan nama anggota di dropdown
        $rekenings = RekeningSimpanan::with('anggota')
            ->where('status', 'aktif') // Hanya tampilkan rekening aktif
            ->get();

        // 2. Kirim variabel $rekenings ke view
        // Pastikan path view sesuai dengan lokasi file setor.blade.php Anda
        return view('simpanan.transaksi.setor', compact('rekenings'));
    }

    /**
     * Menyimpan Transaksi Setor Tunai
     */
    public function storeSetor(Request $request)
    {
        $request->validate([
            'rekening_id' => 'required|exists:rekening_simpanans,rekening_id',
            'jumlah'      => 'required|numeric|min:1000',
            'keterangan'  => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // 1. Ambil Rekening
            $rekening = RekeningSimpanan::lockForUpdate()->find($request->rekening_id);

            // 2. Buat Kode Transaksi Unik
            $kodeTrans = 'TRX-' . time() . mt_rand(100, 999);

            // 3. Simpan Riwayat Transaksi
            TransaksiSimpanan::create([
                'rekening_id'       => $rekening->rekening_id,
                'jenis_transaksi'   => 'setor_tunai',
                'kode_transaksi'    => $kodeTrans,
                'jumlah'            => $request->jumlah,
                'tanggal_transaksi' => now(),
                'keterangan'        => $request->keterangan ?? 'Setoran Tunai',
                'user_id'           => auth()->id(), // Admin yang input
            ]);

            // 4. Update Saldo Rekening (Bertambah)
            $rekening->saldo += $request->jumlah;
            $rekening->save();

            DB::commit();

            return redirect()->route('simpanan.setor.create')
                ->with('success', 'Setoran Rp ' . number_format($request->jumlah) . ' berhasil disimpan ke rekening ' . $rekening->no_rekening);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses setoran: ' . $e->getMessage());
        }
    }


    // =========================================================================
    // FITUR TARIK TUNAI
    // =========================================================================

    /**
     * Menampilkan Form Tarik Tunai
     */
    public function createTarik()
    {
        // Ambil data rekening untuk Dropdown Select2
        $rekenings = RekeningSimpanan::with('anggota')
            ->where('status', 'aktif')
            ->get();

        return view('simpanan.transaksi.tarik', compact('rekenings'));
    }

    /**
     * Menyimpan Transaksi Tarik Tunai
     */
    public function storeTarik(Request $request)
    {
        $request->validate([
            'rekening_id' => 'required|exists:rekening_simpanans,rekening_id',
            'jumlah'      => 'required|numeric|min:1000',
            'keterangan'  => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $rekening = RekeningSimpanan::lockForUpdate()->find($request->rekening_id);

            // Validasi Saldo Cukup
            if ($rekening->saldo < $request->jumlah) {
                return back()->with('error', 'Saldo tidak mencukupi untuk penarikan ini. Saldo: Rp ' . number_format($rekening->saldo));
            }

            $kodeTrans = 'TRX-' . time() . mt_rand(100, 999);

            // Simpan Riwayat
            TransaksiSimpanan::create([
                'rekening_id'       => $rekening->rekening_id,
                'jenis_transaksi'   => 'tarik_tunai',
                'kode_transaksi'    => $kodeTrans,
                'jumlah'            => $request->jumlah,
                'tanggal_transaksi' => now(),
                'keterangan'        => $request->keterangan ?? 'Penarikan Tunai',
                'user_id'           => auth()->id(),
            ]);

            // Update Saldo (Berkurang)
            $rekening->saldo -= $request->jumlah;
            $rekening->save();

            DB::commit();

            return redirect()->route('simpanan.tarik.create')
                ->with('success', 'Penarikan Rp ' . number_format($request->jumlah) . ' berhasil. Sisa saldo: Rp ' . number_format($rekening->saldo));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses penarikan: ' . $e->getMessage());
        }
    }
}
