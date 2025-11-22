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
            return back()->with('error', 'Angsuran ini sudah lunas sebelumnya.');
        }

        // Load relasi pinjaman dan anggota agar nama anggota muncul di View
        $angsuran->load('pinjaman.anggota');

        return view('pinjaman.angsuran.bayar', compact('angsuran'));
    }

    /**
     * Memproses dan mencatat pembayaran angsuran.
     */
    public function storePembayaran(Request $request, AngsuranPinjaman $angsuran)
    {
        if ($angsuran->status === 'lunas') {
            return back()->with('error', 'Angsuran ini sudah lunas.');
        }

        // 1. Validasi Input (Termasuk nominal_bayar)
        $request->validate([
            'nominal_bayar' => 'required|numeric|min:100', // PENTING: Wajib ada agar input fleksibel terbaca
            'tanggal_bayar' => 'required|date',
            'keterangan'    => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Ambil ID User yang sedang login
            $adminId = auth()->user()->user_id ?? auth()->id();

            // 2. Update Data Angsuran
            // Kita timpa 'jumlah_bayar' dengan nominal yang diinput admin (nominal_bayar)
            $angsuran->update([
                'jumlah_bayar' => $request->nominal_bayar, // Simpan nominal realisasi
                'tanggal_bayar' => $request->tanggal_bayar,
                'status' => 'lunas',
                'user_id_admin_terima' => $adminId,
                'keterangan' => $request->input('keterangan'),
            ]);

            // 3. Cek Pelunasan Pinjaman Induk
            $pinjaman = $angsuran->pinjaman;

            // Hitung sisa angsuran yang belum lunas
            $sisaAngsuran = $pinjaman->angsuran()->where('status', '!=', 'lunas')->count();

            // Format angka untuk pesan sukses
            $nominalFormat = number_format($request->nominal_bayar, 0, ',', '.');

            if ($sisaAngsuran === 0) {
                // Jika tidak ada sisa, tandai pinjaman induk LUNAS
                $pinjaman->update(['status' => 'lunas']);
                $message = "Pembayaran Rp {$nominalFormat} berhasil. Seluruh pinjaman kini LUNAS.";
            } else {
                $message = "Pembayaran Rp {$nominalFormat} berhasil dicatat. Sisa {$sisaAngsuran} angsuran lagi.";
            }

            DB::commit();

            // --- REVISI: REDIRECT KE INDEX, BUKAN SHOW ---
            return redirect()->route('simpanan.pinjaman.index')
                             ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Pembayaran Angsuran Gagal: " . $e->getMessage());
            return back()->withInput()->with('error', 'Pencatatan pembayaran gagal diproses: ' . $e->getMessage());
        }
    }
}
