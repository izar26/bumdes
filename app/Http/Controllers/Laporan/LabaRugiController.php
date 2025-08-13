<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LabaRugiController extends Controller
{
    /**
     * Menampilkan halaman form filter laporan Laba Rugi.
     */
    public function index()
    {
        $user = Auth::user();
        $unitUsahas = collect();
        if ($user->hasRole('bendahara_bumdes')) {
            // Bendahara bisa filter semua unit usaha
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        } else {
            // Peran lain hanya melihat unit usahanya sendiri
            $unitUsahas = $user->unitUsahas()->where('status_operasi', 'Aktif')->get();
        }

        return view('laporan.laba_rugi.index', compact('unitUsahas'));
    }

    /**
     * Memproses filter dan menampilkan laporan Laba Rugi.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id'
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $unitUsahaId = $request->unit_usaha_id;

        $pendapatans = [];
        $totalPendapatan = 0;
        $bebans = [];
        $totalBeban = 0;
        
        // Ambil semua akun Pendapatan
        $akunPendapatans = Akun::where('tipe_akun', 'Pendapatan')->get();
        
        foreach ($akunPendapatans as $akun) {
            $baseQuery = DetailJurnal::where('akun_id', $akun->akun_id)
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->where('jurnal_umums.status', 'disetujui') // Filter status jurnal
                ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate]);

            // Terapkan filter unit usaha berdasarkan peran
            if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
                $managedUnitIds = $user->unitUsahas()->pluck('unit_usaha_id');
                $baseQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
            } elseif ($user->hasRole('bendahara_bumdes') && !empty($unitUsahaId)) {
                $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
            }

            $debit = (clone $baseQuery)->sum('detail_jurnals.debit');
            $kredit = (clone $baseQuery)->sum('detail_jurnals.kredit');
            
            // Saldo normal pendapatan adalah di kredit
            $saldo = $kredit - $debit;
            if ($saldo > 0) {
                $pendapatans[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                $totalPendapatan += $saldo;
            }
        }

        // Ambil semua akun Beban
        $akunBebans = Akun::where('tipe_akun', 'Beban')->get();
        
        foreach ($akunBebans as $akun) {
            $baseQuery = DetailJurnal::where('akun_id', $akun->akun_id)
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->where('jurnal_umums.status', 'disetujui') // Filter status jurnal
                ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate]);

            // Terapkan filter unit usaha berdasarkan peran
            if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
                $managedUnitIds = $user->unitUsahas()->pluck('unit_usaha_id');
                $baseQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
            } elseif ($user->hasRole('bendahara_bumdes') && !empty($unitUsahaId)) {
                $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
            }

            $debit = (clone $baseQuery)->sum('detail_jurnals.debit');
            $kredit = (clone $baseQuery)->sum('detail_jurnals.kredit');

            // Saldo normal beban adalah di debit
            $saldo = $debit - $kredit;
            if ($saldo > 0) {
                $bebans[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                $totalBeban += $saldo;
            }
        }

        // Hitung Laba Rugi
        $labaRugi = $totalPendapatan - $totalBeban;
        
        return view('laporan.laba_rugi.show', compact(
            'startDate',
            'endDate',
            'pendapatans',
            'totalPendapatan',
            'bebans',
            'totalBeban',
            'labaRugi'
        ));
    }
}
