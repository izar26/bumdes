<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use Carbon\Carbon;

class NeracaSaldoController extends Controller
{
    /**
     * Menampilkan halaman form filter.
     */
    public function index()
    {
        return view('laporan.neraca_saldo.index');
    }

    /**
     * Memproses filter dan menampilkan laporan.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
        ]);

        $reportDate = Carbon::parse($request->report_date);

        // 1. Ambil semua akun detail
        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun')->get();
        
        $laporanData = [];
        $totalDebit = 0;
        $totalKredit = 0;

        // 2. Loop setiap akun untuk menghitung saldo akhirnya
        foreach ($akuns as $akun) {
            $debit = DetailJurnal::where('akun_id', $akun->akun_id)
                        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                        ->where('jurnal_umums.tanggal_transaksi', '<=', $reportDate)
                        ->sum('detail_jurnals.debit');
            
            $kredit = DetailJurnal::where('akun_id', $akun->akun_id)
                        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                        ->where('jurnal_umums.tanggal_transaksi', '<=', $reportDate)
                        ->sum('detail_jurnals.kredit');

            // Tentukan saldo normal berdasarkan tipe akun
            $saldo = 0;
            $saldoDebit = 0;
            $saldoKredit = 0;

            if (in_array($akun->tipe_akun, ['Aset', 'Beban'])) {
                // Saldo normal di Debit
                $saldo = $debit - $kredit;
                if ($saldo > 0) {
                    $saldoDebit = $saldo;
                } else {
                    $saldoKredit = abs($saldo);
                }
            } else { // Liabilitas, Ekuitas, Pendapatan
                // Saldo normal di Kredit
                $saldo = $kredit - $debit;
                 if ($saldo > 0) {
                    $saldoKredit = $saldo;
                } else {
                    $saldoDebit = abs($saldo);
                }
            }
            
            // Hanya tampilkan akun yang memiliki saldo
            if ($saldo != 0) {
                $laporanData[] = [
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'debit' => $saldoDebit,
                    'kredit' => $saldoKredit,
                ];
                $totalDebit += $saldoDebit;
                $totalKredit += $saldoKredit;
            }
        }
        
        return view('laporan.neraca_saldo.show', compact(
            'reportDate',
            'laporanData',
            'totalDebit',
            'totalKredit'
        ));
    }
}