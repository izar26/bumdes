<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Bungdes;
use App\Models\User;

class BukuBesarController extends Controller
{
    /**
     * Menampilkan halaman form filter laporan Buku Besar.
     */
    public function index()
    {
        $user = Auth::user();
        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun')->get();

        $unitUsahas = collect();
        if ($user->hasAnyRole(['bendahara_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
            // Peran ini bisa filter semua unit usaha
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->orderBy('nama_unit')->get();
        } else {
            // Peran lain hanya melihat unit usahanya sendiri
            $unitUsahaIds = $user->unitUsahaIds();
            $unitUsahas = UnitUsaha::whereIn('unit_usaha_id', $unitUsahaIds)
                                    ->where('status_operasi', 'Aktif')
                                    ->orderBy('nama_unit')
                                    ->get();
        }

        return view('laporan.buku_besar.index', compact('akuns', 'unitUsahas', 'user'));
    }

    /**
     * Memproses filter dan menampilkan laporan Buku Besar.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'akun_id' => 'required|exists:akuns,akun_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id'
        ]);

        $user = Auth::user();
        $akunId = $request->akun_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $akun = Akun::findOrFail($akunId);
        $unitUsahaId = $request->unit_usaha_id;
        
        $bumdes = Bungdes::first();

        // Query dasar untuk detail jurnal
        $baseQuery = DetailJurnal::where('akun_id', $akunId)
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('jurnal_umums.status', 'disetujui');

        // Terapkan filter unit usaha berdasarkan peran
        if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $baseQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
        } elseif (!empty($unitUsahaId)) {
             $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }

        // Hitung Saldo Awal
        $saldoAwalQuery = (clone $baseQuery)->where('jurnal_umums.tanggal_transaksi', '<', $startDate->toDateString());
        $saldoAwalDebit = $saldoAwalQuery->sum('detail_jurnals.debit');
        $saldoAwalKredit = $saldoAwalQuery->sum('detail_jurnals.kredit');
        
        // --- PERBAIKAN DIMULAI: Logika Saldo Awal berdasarkan Tipe Akun ---
        $akunNormalDebit = ['Aset', 'HPP', 'Beban'];

        if (in_array($akun->tipe_akun, $akunNormalDebit)) {
            $saldoAwal = $saldoAwalDebit - $saldoAwalKredit; // Saldo Normal Debit
        } else {
            $saldoAwal = $saldoAwalKredit - $saldoAwalDebit; // Saldo Normal Kredit (Kewajiban, Ekuitas, Pendapatan)
        }
        // --- AKHIR PERBAIKAN ---

        // Ambil daftar transaksi sesuai rentang tanggal
        $transaksis = (clone $baseQuery)
            ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('jurnal_umums.tanggal_transaksi', 'asc')
            ->orderBy('detail_jurnals.detail_jurnal_id', 'asc')
            ->select('detail_jurnals.*') // Pilih semua kolom dari detail_jurnals
            ->get();

        // Siapkan data penandatangan dinamis
        $penandaTangan1 = []; 
        $penandaTangan2 = [];

        if ($unitUsahaId) {
            $unitUsaha = UnitUsaha::with('penanggungJawab.anggota')->find($unitUsahaId);
            $manajer = $unitUsaha ? $unitUsaha->penanggungJawab : null;
            $penandaTangan1 = ['jabatan' => 'Manajer Unit Usaha', 'nama' => $manajer && $manajer->anggota ? $manajer->anggota->nama_lengkap : '....................'];
            $adminUnit = User::role('admin_unit_usaha')->whereHas('unitUsahas', fn($q) => $q->where('unit_usahas.unit_usaha_id', $unitUsahaId))->with('anggota')->first();
            $penandaTangan2 = ['jabatan' => 'Admin Unit Usaha', 'nama' => $adminUnit && $adminUnit->anggota ? $adminUnit->anggota->nama_lengkap : '....................'];
        } else {
            $direktur = User::role('direktur_bumdes')->with('anggota')->first();
            $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
            $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
            $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];
        }

        return view('laporan.buku_besar.show', compact(
            'akun',
            'startDate',
            'endDate',
            'saldoAwal',
            'transaksis',
            'bumdes',
            'penandaTangan1',
            'penandaTangan2'
        ));
    }
}
