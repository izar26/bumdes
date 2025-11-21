<?php

namespace App\Http\Controllers\Simpanan;

use App\Models\RekeningSimpanan;
use App\Models\TransaksiSimpanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;

class TransaksiSimpananController extends Controller
{
    // --- Setoran Tunai (Deposit) ---

    public function createSetoran()
    {
        // Tampilkan form untuk Setoran.
        return view('simpanan.setor.create');
    }

    public function storeSetoran(Request $request)
    {
        $request->validate([
            'rekening_id' => 'required|exists:rekening_simpanans,rekening_id',
            'jumlah' => 'required|integer|min:1000',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $rekening = RekeningSimpanan::find($request->rekening_id);

        // **PENTING: Gunakan Database Transaction**
        try {
            DB::beginTransaction();

            // 1. Hitung Saldo Baru
            $saldo_baru = $rekening->saldo + $request->jumlah;

            // 2. Catat Transaksi (LEDGER)
            $transaksi = TransaksiSimpanan::create([
                'rekening_id' => $rekening->rekening_id,
                'tanggal_transaksi' => now(),
                'jenis_transaksi' => 'setor',
                'jumlah' => $request->jumlah,
                'saldo_setelah_transaksi' => $saldo_baru,
                'keterangan' => $request->keterangan,
                'user_id_admin' => auth()->id(),
            ]);

            // 3. Update Saldo Rekening
            $rekening->saldo = $saldo_baru;
            $rekening->save();

            DB::commit();

            return redirect()->route('rekening.show', $rekening->anggota_id)
                             ->with('success', 'Setoran sebesar Rp. ' . number_format($request->jumlah) . ' berhasil dicatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for debugging
            \Log::error("Setoran Gagal: " . $e->getMessage());

            return back()->withInput()->with('error', 'Setoran gagal diproses. Silakan coba lagi.');
        }
    }

    // --- Penarikan Tunai (Withdrawal) ---

    public function createPenarikan()
    {
        return view('simpanan.tarik.create');
    }

    public function storePenarikan(Request $request)
    {
        $request->validate([
            'rekening_id' => 'required|exists:rekening_simpanans,rekening_id',
            'jumlah' => 'required|integer|min:1000',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $rekening = RekeningSimpanan::find($request->rekening_id);

        // **Validasi Saldo: Cek apakah saldo mencukupi sebelum ditarik**
        if ($rekening->saldo < $request->jumlah) {
            throw ValidationException::withMessages([
                'jumlah' => ['Saldo tidak mencukupi untuk penarikan ini. Saldo saat ini: Rp. ' . number_format($rekening->saldo)],
            ]);
        }

        try {
            DB::beginTransaction();

            // 1. Hitung Saldo Baru
            $saldo_baru = $rekening->saldo - $request->jumlah;

            // 2. Catat Transaksi (LEDGER)
            TransaksiSimpanan::create([
                'rekening_id' => $rekening->rekening_id,
                'tanggal_transaksi' => now(),
                'jenis_transaksi' => 'tarik',
                'jumlah' => $request->jumlah,
                'saldo_setelah_transaksi' => $saldo_baru,
                'keterangan' => $request->keterangan,
                'user_id_admin' => auth()->id(),
            ]);

            // 3. Update Saldo Rekening
            $rekening->saldo = $saldo_baru;
            $rekening->save();

            DB::commit();

            return redirect()->route('rekening.show', $rekening->anggota_id)
                             ->with('success', 'Penarikan sebesar Rp. ' . number_format($request->jumlah) . ' berhasil dicatat.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Penarikan Gagal: " . $e->getMessage());

            return back()->withInput()->with('error', 'Penarikan gagal diproses. Silakan coba lagi.');
        }
    }
}
