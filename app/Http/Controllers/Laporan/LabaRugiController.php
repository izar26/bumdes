<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use Carbon\Carbon;

class LabaRugiController extends Controller
{
    /**
     * Menampilkan halaman form filter laporan Laba Rugi.
     */
    public function index()
    {
        return view('laporan.laba_rugi.index');
    }

    /**
     * Memproses filter dan menampilkan laporan Laba Rugi.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // 1. Ambil semua akun Pendapatan
        $akunPendapatans = Akun::where('tipe_akun', 'Pendapatan')->get();
        $pendapatans = [];
        $totalPendapatan = 0;

        foreach ($akunPendapatans as $akun) {
            $debit = DetailJurnal::where('akun_id', $akun->akun_id)
                        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                        ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])
                        ->sum('detail_jurnals.debit');

            $kredit = DetailJurnal::where('akun_id', $akun->akun_id)
                        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                        ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])
                        ->sum('detail_jurnals.kredit');
            
            // Saldo normal pendapatan adalah di kredit
            $saldo = $kredit - $debit;
            if ($saldo > 0) {
                $pendapatans[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                $totalPendapatan += $saldo;
            }
        }

        // 2. Ambil semua akun Beban
        $akunBebans = Akun::where('tipe_akun', 'Beban')->get();
        $bebans = [];
        $totalBeban = 0;
        
        foreach ($akunBebans as $akun) {
            $debit = DetailJurnal::where('akun_id', $akun->akun_id)
                        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                        ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])
                        ->sum('detail_jurnals.debit');

            $kredit = DetailJurnal::where('akun_id', $akun->akun_id)
                        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                        ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])
                        ->sum('detail_jurnals.kredit');

            // Saldo normal beban adalah di debit
            $saldo = $debit - $kredit;
            if ($saldo > 0) {
                $bebans[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                $totalBeban += $saldo;
            }
        }

        // 3. Hitung Laba Rugi
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