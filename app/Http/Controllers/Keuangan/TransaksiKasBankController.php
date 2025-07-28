<?php

// app/Http/Controllers/Keuangan/TransaksiKasBankController.php
namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Akun; // Tambahkan
use App\Models\DetailJurnal; // Tambahkan
use App\Models\JurnalUmum; // Tambahkan
use App\Models\KasBank;
use App\Models\TransaksiKasBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransaksiKasBankController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input (dengan tambahan akun_id)
        $request->validate([
            'kas_bank_id' => 'required|exists:kas_banks,kas_bank_id',
            'akun_id' => 'required|exists:akuns,akun_id', // validasi baru
            'tanggal_transaksi' => 'required|date',
            'jenis_transaksi' => 'required|in:debit,kredit',
            'jumlah' => 'required|numeric|min:1',
            'deskripsi' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            $kasBank = KasBank::findOrFail($request->kas_bank_id);
            $akunTerkait = Akun::findOrFail($request->akun_id);
            $jumlah = $request->jumlah;
            $user_id = Auth::id();
            // Asumsi BUMDes ID = 1 untuk sementara, sesuaikan jika perlu
            $bungdes_id = 1; 
            
            // 2. Buat Jurnal Umum (Header)
            $jurnal = JurnalUmum::create([
                'bungdes_id' => $bungdes_id,
                'user_id' => $user_id,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'deskripsi' => $request->deskripsi,
                'total_debit' => $jumlah, // Total debit dan kredit jurnal harus sama
                'total_kredit' => $jumlah,
            ]);

            // 3. Buat Detail Jurnal (Double Entry)
            if ($request->jenis_transaksi == 'debit') { // Pemasukan
                // Debit: Akun Kas/Bank bertambah
                DetailJurnal::create([
                    'jurnal_id' => $jurnal->jurnal_id,
                    'akun_id' => $kasBank->akun_id, // Ambil akun_id dari relasi kasBank
                    'debit' => $jumlah,
                    'kredit' => 0,
                ]);
                // Kredit: Akun terkait (misal: Pendapatan) bertambah
                DetailJurnal::create([
                    'jurnal_id' => $jurnal->jurnal_id,
                    'akun_id' => $akunTerkait->akun_id,
                    'debit' => 0,
                    'kredit' => $jumlah,
                ]);
            } else { // Pengeluaran
                // Debit: Akun terkait (misal: Beban) bertambah
                DetailJurnal::create([
                    'jurnal_id' => $jurnal->jurnal_id,
                    'akun_id' => $akunTerkait->akun_id,
                    'debit' => $jumlah,
                    'kredit' => 0,
                ]);
                // Kredit: Akun Kas/Bank berkurang
                DetailJurnal::create([
                    'jurnal_id' => $jurnal->jurnal_id,
                    'akun_id' => $kasBank->akun_id,
                    'debit' => 0,
                    'kredit' => $jumlah,
                ]);
            }
            
            // 4. Catat Transaksi Kas & Update Saldo (Logika lama tetap berjalan)
            TransaksiKasBank::create([
                'kas_bank_id' => $kasBank->kas_bank_id,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'jumlah_debit' => $request->jenis_transaksi == 'debit' ? $jumlah : 0,
                'jumlah_kredit' => $request->jenis_transaksi == 'kredit' ? $jumlah : 0,
                'deskripsi' => $request->deskripsi,
                'user_id' => $user_id,
            ]);
            
            if ($request->jenis_transaksi == 'debit') {
                $kasBank->saldo_saat_ini += $jumlah;
            } else {
                $kasBank->saldo_saat_ini -= $jumlah;
            }
            $kasBank->save();
        });

        return redirect()->route('kas-bank.show', $request->kas_bank_id)
                         ->with('success', 'Transaksi berhasil ditambahkan dan Jurnal Umum telah dibuat.');
    }
}