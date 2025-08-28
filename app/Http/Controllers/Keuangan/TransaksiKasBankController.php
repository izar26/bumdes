<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\JurnalUmum;
use App\Models\KasBank;
use App\Models\TransaksiKasBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;

class TransaksiKasBankController extends Controller
{
    // Tambahkan middleware otorisasi jika perlu
    public function __construct()
    {
        // Asumsi hanya Bendahara dan Admin BUMDes yang bisa membuat transaksi kas bank
        // Jika manajer unit usaha juga bisa, tambahkan peran mereka
        $this->middleware('role:bendahara_bumdes|admin_bumdes|direktur_bumdes');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Dapatkan unit usaha yang dikelola user
        $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');

        // Validasi input
        $request->validate([
            'kas_bank_id' => [
                'required',
                Rule::exists('kas_banks', 'kas_bank_id')
            ],
            'unit_usaha_id' => [ // Wajib ada untuk otorisasi
                'required',
                Rule::in($managedUnitUsahaIds)
            ],
            'akun_id' => 'required|exists:akuns,akun_id',
            'tanggal_transaksi' => 'required|date',
            'jenis_transaksi' => 'required|in:debit,kredit',
            'jumlah' => 'required|numeric|min:1',
            'deskripsi' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $kasBank = KasBank::findOrFail($request->kas_bank_id);
            $akunTerkait = Akun::findOrFail($request->akun_id);
            $jumlah = $request->jumlah;
            $unitUsahaId = $request->unit_usaha_id;

            // --- Jurnal Umum ---
            $jurnal = JurnalUmum::create([
                'user_id' => $user->user_id,
                'unit_usaha_id' => $unitUsahaId, // Hubungkan ke unit usaha
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'deskripsi' => $request->deskripsi,
                'total_debit' => $jumlah,
                'total_kredit' => $jumlah,
            ]);

            if ($request->jenis_transaksi == 'debit') {
                DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $kasBank->akun_id, 'debit' => $jumlah, 'kredit' => 0]);
                DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $akunTerkait->akun_id, 'debit' => 0, 'kredit' => $jumlah]);
            } else {
                DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $akunTerkait->akun_id, 'debit' => $jumlah, 'kredit' => 0]);
                DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $kasBank->akun_id, 'debit' => 0, 'kredit' => $jumlah]);
            }

            // --- REKOMENDASI: Hapus tabel TransaksiKasBank dan kolom saldo_saat_ini ---
            // Cukup andalkan jurnal umum sebagai single source of truth.
            // Transaksi ini bisa dicatat sebagai 'jurnal_id' di tabel KasBank jika perlu relasi.

            DB::commit();

            return redirect()->route('kas-bank.show', $request->kas_bank_id)
                             ->with('success', 'Transaksi berhasil ditambahkan dan Jurnal Umum telah dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage())->withInput();
        }
    }
}
