<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use Carbon\Carbon;

class PerubahanEkuitasController extends Controller
{
    public function index()
    {
        return view('laporan.perubahan_ekuitas.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Helper function untuk menghitung saldo sebuah akun spesifik HINGGA tanggal tertentu
        $calculateBalanceUpTo = function($kode_akun, $endDate) {
            $akun = Akun::where('kode_akun', $kode_akun)->first();
            if (!$akun) return 0;

            $debit = DetailJurnal::where('akun_id', $akun->akun_id)
                        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                        ->where('jurnal_umums.tanggal_transaksi', '<=', $endDate)
                        ->sum('detail_jurnals.debit');
            $kredit = DetailJurnal::where('akun_id', $akun->akun_id)
                        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                        ->where('jurnal_umums.tanggal_transaksi', '<=', $endDate)
                        ->sum('detail_jurnals.kredit');

            // Saldo normal Ekuitas di Kredit
            return $kredit - $debit;
        };
        
        // Helper function untuk menghitung perubahan (mutasi) DALAM periode tertentu
        $calculateMutationByCode = function($kode_akun, $startDate, $endDate) {
            $akun = Akun::where('kode_akun', $kode_akun)->first();
            if (!$akun) return 0;
            
            $debit = DetailJurnal::where('akun_id', $akun->akun_id)->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])->sum('detail_jurnals.debit');
            $kredit = DetailJurnal::where('akun_id', $akun->akun_id)->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])->sum('detail_jurnals.kredit');

            return $kredit - $debit;
        };

        // 1. PERHITUNGAN PENYERTAAN MODAL
        $modalDesaAwal = $calculateBalanceUpTo('3.1.01.01', $startDate->copy()->subDay());
        $modalMasyarakatAwal = $calculateBalanceUpTo('3.1.02.01', $startDate->copy()->subDay());
        $penambahanModalDesa = $calculateMutationByCode('3.1.01.01', $startDate, $endDate);
        $penambahanModalMasyarakat = $calculateMutationByCode('3.1.02.01', $startDate, $endDate);
        $modalAkhir = $modalDesaAwal + $modalMasyarakatAwal + $penambahanModalDesa + $penambahanModalMasyarakat;

        // 2. PERHITUNGAN SALDO LABA
        // Laba ditahan dari periode-periode sebelumnya
        $totalPendapatanTerdahulu = Akun::where('tipe_akun', 'Pendapatan')->get()->reduce(function($carry, $akun) use ($calculateBalanceUpTo, $startDate){
            return $carry + $calculateBalanceUpTo($akun->kode_akun, $startDate->copy()->subDay());
        });
        $totalBebanTerdahulu = Akun::whereIn('tipe_akun', ['Beban', 'HPP'])->get()->reduce(function($carry, $akun) use ($startDate){
            $akunModel = Akun::where('kode_akun', $akun->kode_akun)->first();
            $debit = DetailJurnal::where('akun_id', $akunModel->akun_id)->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')->where('jurnal_umums.tanggal_transaksi', '<', $startDate)->sum('detail_jurnals.debit');
            $kredit = DetailJurnal::where('akun_id', $akunModel->akun_id)->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')->where('jurnal_umums.tanggal_transaksi', '<', $startDate)->sum('detail_jurnals.kredit');
            return $carry + ($debit - $kredit);
        });
        $saldoLabaAwal = $totalPendapatanTerdahulu - $totalBebanTerdahulu;

        // Laba rugi periode berjalan (sama seperti di Laporan Laba Rugi)
        $labaRugiPeriodeIni = Akun::where('tipe_akun', 'Pendapatan')->get()->reduce(function($carry, $akun) use ($calculateMutationByCode, $startDate, $endDate){
            return $carry + $calculateMutationByCode($akun->kode_akun, $startDate, $endDate);
        }) - Akun::whereIn('tipe_akun', ['Beban', 'HPP'])->get()->reduce(function($carry, $akun) use ($startDate, $endDate){
            $akunModel = Akun::where('kode_akun', $akun->kode_akun)->first();
            $debit = DetailJurnal::where('akun_id', $akunModel->akun_id)->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])->sum('detail_jurnals.debit');
            $kredit = DetailJurnal::where('akun_id', $akunModel->akun_id)->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])->sum('detail_jurnals.kredit');
            return $carry + ($debit - $kredit);
        });

        // Bagi hasil dianggap sebagai pengurang laba (normalnya didebit)
        $bagiHasilDesa = $calculateMutationByCode('3.3.01.01', $startDate, $endDate) * -1;
        $bagiHasilMasyarakat = $calculateMutationByCode('3.3.02.01', $startDate, $endDate) * -1;
        
        $saldoLabaAkhir = $saldoLabaAwal + $labaRugiPeriodeIni - $bagiHasilDesa - $bagiHasilMasyarakat;

        // 3. PERHITUNGAN MODAL DONASI
        $modalDonasi = $calculateBalanceUpTo('3.4.01.01', $endDate);

        // 4. EKUITAS AKHIR
        $ekuitasAkhir = $modalAkhir + $saldoLabaAkhir + $modalDonasi;
        
        return view('laporan.perubahan_ekuitas.show', compact(
            'startDate', 'endDate',
            'modalDesaAwal', 'modalMasyarakatAwal',
            'penambahanModalDesa', 'penambahanModalMasyarakat', 'modalAkhir',
            'saldoLabaAwal', 'labaRugiPeriodeIni', 'bagiHasilDesa', 'bagiHasilMasyarakat', 'saldoLabaAkhir',
            'modalDonasi', 'ekuitasAkhir'
        ));
    }
}