<?php

namespace App\Http\Controllers\Simpanan;

use App\Models\AngsuranPinjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AngsuranPinjamanController extends Controller
{
    /**
     * Menampilkan form untuk mencatat pembayaran angsuran.
     */
    public function createPembayaran(AngsuranPinjaman $angsuran)
    {
        if ($angsuran->status === 'lunas') {
            return back()->with('error', 'Angsuran ini sudah lunas.');
        }
        $angsuran->load('pinjaman.anggota');
        return view('simpanan.pinjaman.angsuran.bayar', compact('angsuran'));
    }

    /**
     * Memproses dan mencatat pembayaran angsuran.
     */
    public function storePembayaran(Request $request, AngsuranPinjaman $angsuran)
    {
        if ($angsuran->status === 'lunas') {
            return back()->with('error', 'Angsuran ini sudah lunas.');
        }

        $request->validate([
            'tanggal_bayar' => 'required|date',
            // Kita asumsikan jumlah bayar harus sesuai dengan yang seharusnya (jumlah_bayar di tabel).
            // Bisa ditambahkan validasi untuk pembayaran parsial jika dibutuhkan.
        ]);

        try {
            DB::beginTransaction();

            // 1. Catat Angsuran
            $angsuran->update([
                'tanggal_bayar' => $request->tanggal_bayar,
                'status' => 'lunas',
                'user_id_admin_terima' => auth()->id(),
                'keterangan' => $request->input('keterangan'),
            ]);

            // 2. Cek apakah semua angsuran pinjaman ini sudah lunas
            $pinjaman = $angsuran->pinjaman;
            $sisaAngsuran = $pinjaman->angsuran()->where('status', '!=', 'lunas')->count();

            if ($sisaAngsuran === 0) {
                // Jika sudah lunas semua, update status pinjaman induk
                $pinjaman->update(['status' => 'lunas']);
                $message = 'Pembayaran angsuran ke-' . $angsuran->angsuran_ke . ' berhasil dicatat, dan Pinjaman dinyatakan LUNAS.';
            } else {
                $message = 'Pembayaran angsuran ke-' . $angsuran->angsuran_ke . ' berhasil dicatat. Sisa ' . $sisaAngsuran . ' angsuran lagi.';
            }

            // *OPSIONAL: Di sini bisa ditambahkan logika Jurnal/Kas untuk mencatat Uang Masuk (Angsuran)*

            DB::commit();

            return redirect()->route('pinjaman.show', $pinjaman->pinjaman_id)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Pembayaran Angsuran Gagal: " . $e->getMessage());
            return back()->withInput()->with('error', 'Pencatatan pembayaran gagal diproses.');
        }
    }
}
