<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha; // <-- Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Tambahkan ini
use Carbon\Carbon;

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
        if ($user->hasRole('bendahara_bumdes')) {
            // Bendahara bisa filter semua unit usaha
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        } else {
            // Peran lain hanya melihat unit usahanya sendiri
            $unitUsahas = $user->unitUsahas()->where('status_operasi', 'Aktif')->get();
        }

        return view('laporan.buku_besar.index', compact('akuns', 'unitUsahas'));
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

        // Query dasar untuk detail jurnal
        $baseQuery = DetailJurnal::where('akun_id', $akunId)
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id');

        // Terapkan filter unit usaha berdasarkan peran
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usaha_id');
            $baseQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
        } elseif ($user->hasRole('bendahara_bumdes') && !empty($unitUsahaId)) {
            $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }

        // Hitung Saldo Awal
        $saldoAwalQuery = (clone $baseQuery)->where('jurnal_umums.tanggal_transaksi', '<', $startDate);
        $saldoAwalDebit = (clone $saldoAwalQuery)->sum('detail_jurnals.debit');
        $saldoAwalKredit = (clone $saldoAwalQuery)->sum('detail_jurnals.kredit');
        $saldoAwal = $saldoAwalDebit - $saldoAwalKredit;

        // Ambil daftar transaksi sesuai rentang tanggal
        $transaksis = (clone $baseQuery)
            ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])
            ->orderBy('jurnal_umums.tanggal_transaksi', 'asc')
            ->orderBy('detail_jurnals.detail_jurnal_id', 'asc')
            ->select('detail_jurnals.*')
            ->get();
            
        return view('laporan.buku_besar.show', compact(
            'akun', 
            'startDate', 
            'endDate', 
            'saldoAwal', 
            'transaksis'
        ));
    }
}
