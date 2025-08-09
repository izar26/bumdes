<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use Carbon\Carbon;

class ArusKasController extends Controller
{
    public function index()
    {
        return view('laporan.arus_kas.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // 1. Dapatkan semua ID akun yang tergolong Kas & Bank
        $akunKasBankIds = Akun::where('tipe_akun', 'Aset')
                              ->where(function ($query) {
                                  $query->where('nama_akun', 'LIKE', '%Kas%')
                                        ->orWhere('nama_akun', 'LIKE', '%Bank%');
                              })
                              ->where('is_header', 0)
                              ->pluck('akun_id');

        // 2. Hitung Saldo Kas Awal
        $debitAwal = DetailJurnal::whereIn('akun_id', $akunKasBankIds)
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('jurnal_umums.tanggal_transaksi', '<', $startDate)
            ->sum('detail_jurnals.debit');
        $kreditAwal = DetailJurnal::whereIn('akun_id', $akunKasBankIds)
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('jurnal_umums.tanggal_transaksi', '<', $startDate)
            ->sum('detail_jurnals.kredit');
        $saldoKasAwal = $debitAwal - $kreditAwal;

        // 3. Ambil semua transaksi yang melibatkan kas dalam periode ini
        $transaksiKas = DetailJurnal::with('jurnal.detailJurnals.akun')
            ->whereIn('akun_id', $akunKasBankIds)
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])
            ->select('detail_jurnals.*')
            ->get();

        $arusOperasi = [];
        $arusInvestasi = [];
        $arusPendanaan = [];

        // 4. Kelompokkan transaksi
        foreach ($transaksiKas as $transaksi) {
            $jumlah = $transaksi->debit - $transaksi->kredit; // Positif jika kas masuk, negatif jika keluar
            
            // Cari akun lawan dalam satu jurnal yang sama
            foreach ($transaksi->jurnal->detailJurnals as $detailLawan) {
                if ($detailLawan->akun_id != $transaksi->akun_id) {
                    $akunLawan = $detailLawan->akun;
                    $item = ['nama' => $akunLawan->nama_akun, 'jumlah' => $jumlah];

                    // Kelompokkan berdasarkan tipe akun lawan
                    if (in_array($akunLawan->tipe_akun, ['Pendapatan', 'HPP', 'Beban', 'Piutang', 'Kewajiban', 'Persediaan'])) {
                        $arusOperasi[] = $item;
                    } elseif ($akunLawan->tipe_akun == 'Aset') { // Aset Tetap
                        $arusInvestasi[] = $item;
                    } elseif ($akunLawan->tipe_akun == 'Ekuitas') {
                        $arusPendanaan[] = $item;
                    }
                    break; // Hanya ambil satu akun lawan untuk menghindari duplikasi
                }
            }
        }
        
        $totalOperasi = collect($arusOperasi)->sum('jumlah');
        $totalInvestasi = collect($arusInvestasi)->sum('jumlah');
        $totalPendanaan = collect($arusPendanaan)->sum('jumlah');

        $kenaikanPenurunanKas = $totalOperasi + $totalInvestasi + $totalPendanaan;
        $saldoKasAkhir = $saldoKasAwal + $kenaikanPenurunanKas;

        return view('laporan.arus_kas.show', compact(
            'startDate', 'endDate', 'saldoKasAwal',
            'arusOperasi', 'totalOperasi',
            'arusInvestasi', 'totalInvestasi',
            'arusPendanaan', 'totalPendanaan',
            'kenaikanPenurunanKas', 'saldoKasAkhir'
        ));
    }
}
