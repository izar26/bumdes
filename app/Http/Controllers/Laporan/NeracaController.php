<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use Carbon\Carbon;

class NeracaController extends Controller
{
    /**
     * Menampilkan halaman form filter laporan Neraca.
     */
    public function index()
    {
        return view('laporan.neraca.index');
    }

    /**
     * Memproses filter dan menampilkan laporan Neraca.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
        ]);

        $reportDate = Carbon::parse($request->report_date);

        // Helper function untuk menghitung saldo akun
        $calculateBalance = function($akunId, $endDate) {
            $debit = DetailJurnal::where('akun_id', $akunId)
                        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                        ->where('jurnal_umums.tanggal_transaksi', '<=', $endDate)
                        ->sum('detail_jurnals.debit');
            $kredit = DetailJurnal::where('akun_id', $akunId)
                        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                        ->where('jurnal_umums.tanggal_transaksi', '<=', $endDate)
                        ->sum('detail_jurnals.kredit');
            return ['debit' => $debit, 'kredit' => $kredit];
        };

        // 1. Hitung ASET
        $akunAsets = Akun::where('tipe_akun', 'Aset')->where('is_header', 0)->get();
        $asets = [];
        $totalAset = 0;
        foreach ($akunAsets as $akun) {
            $balances = $calculateBalance($akun->akun_id, $reportDate);
            $saldo = $balances['debit'] - $balances['kredit']; // Saldo normal Aset di Debit
            if ($saldo != 0) {
                $asets[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                $totalAset += $saldo;
            }
        }

        // 2. Hitung KEWAJIBAN
        $akunKewajibans = Akun::where('tipe_akun', 'Liabilitas')->where('is_header', 0)->get();
        $kewajibans = [];
        $totalKewajiban = 0;
        foreach ($akunKewajibans as $akun) {
            $balances = $calculateBalance($akun->akun_id, $reportDate);
            $saldo = $balances['kredit'] - $balances['debit']; // Saldo normal Kewajiban di Kredit
            if ($saldo != 0) {
                $kewajibans[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                $totalKewajiban += $saldo;
            }
        }

        // 3. Hitung EKUITAS
        $akunEkuitas = Akun::where('tipe_akun', 'Ekuitas')->where('is_header', 0)->get();
        $ekuitas = [];
        $totalModalAwal = 0;
        foreach ($akunEkuitas as $akun) {
            $balances = $calculateBalance($akun->akun_id, $reportDate);
            $saldo = $balances['kredit'] - $balances['debit']; // Saldo normal Ekuitas di Kredit
            if ($saldo != 0) {
                $ekuitas[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                $totalModalAwal += $saldo;
            }
        }
        
        // Hitung Laba Ditahan (Total Pendapatan - Total Beban) s/d tanggal laporan
        $totalPendapatan = Akun::where('tipe_akun', 'Pendapatan')->get()->reduce(function ($carry, $akun) use ($calculateBalance, $reportDate) {
            $balances = $calculateBalance($akun->akun_id, $reportDate);
            return $carry + ($balances['kredit'] - $balances['debit']);
        }, 0);

        $totalBeban = Akun::where('tipe_akun', 'Beban')->get()->reduce(function ($carry, $akun) use ($calculateBalance, $reportDate) {
            $balances = $calculateBalance($akun->akun_id, $reportDate);
            return $carry + ($balances['debit'] - $balances['kredit']);
        }, 0);
        
        $labaDitahan = $totalPendapatan - $totalBeban;
        $totalEkuitas = $totalModalAwal + $labaDitahan;

        return view('laporan.neraca.show', compact(
            'reportDate',
            'asets', 'totalAset',
            'kewajibans', 'totalKewajiban',
            'ekuitas', 'totalModalAwal',
            'labaDitahan', 'totalEkuitas'
        ));
    }
}