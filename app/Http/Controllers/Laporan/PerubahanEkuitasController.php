<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use App\Models\Bungdes; // tambahkan ini supaya bisa kirim data bumdes ke view
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PerubahanEkuitasController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $unitUsahas = $user->hasRole('bendahara_bumdes')
            ? UnitUsaha::where('status_operasi', 'Aktif')->get()
            : $user->unitUsahas()->where('status_operasi', 'Aktif')->get();

        return view('laporan.perubahan_ekuitas.index', compact('unitUsahas'));
    }

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

        $baseQuery = DetailJurnal::join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('jurnal_umums.status', 'disetujui');

        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usaha_id');
            $baseQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
        } elseif ($user->hasRole('bendahara_bumdes') && !empty($unitUsahaId)) {
            $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }

        $calculateBalanceUpTo = function($akunKode, $endDate) use ($baseQuery) {
            $akun = Akun::where('kode_akun', $akunKode)->first();
            if (!$akun) return 0;
            $query = (clone $baseQuery)->where('akun_id', $akun->akun_id)->where('jurnal_umums.tanggal_transaksi', '<=', $endDate);
            $debit = (clone $query)->sum('detail_jurnals.debit');
            $kredit = (clone $query)->sum('detail_jurnals.kredit');
            return $kredit - $debit;
        };

        $calculateMutationInPeriod = function($akunKode, $startDate, $endDate) use ($baseQuery) {
            $akun = Akun::where('kode_akun', $akunKode)->first();
            if (!$akun) return 0;
            $query = (clone $baseQuery)->where('akun_id', $akun->akun_id)->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate]);
            $debit = (clone $query)->sum('detail_jurnals.debit');
            $kredit = (clone $query)->sum('detail_jurnals.kredit');
            return $kredit - $debit;
        };

        $calculateBebanMutationInPeriod = function($akunKode, $startDate, $endDate) use ($baseQuery) {
            $akun = Akun::where('kode_akun', $akunKode)->first();
            if (!$akun) return 0;
            $query = (clone $baseQuery)->where('akun_id', $akun->akun_id)->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate]);
            $debit = (clone $query)->sum('detail_jurnals.debit');
            $kredit = (clone $query)->sum('detail_jurnals.kredit');
            return $debit - $kredit;
        };

        $modalDesaAwal = $calculateBalanceUpTo('3.1.01.01', $startDate->copy()->subDay());
        $modalMasyarakatAwal = $calculateBalanceUpTo('3.1.02.01', $startDate->copy()->subDay());
        $penambahanModalDesa = $calculateMutationInPeriod('3.1.01.01', $startDate, $endDate);
        $penambahanModalMasyarakat = $calculateMutationInPeriod('3.1.02.01', $startDate, $endDate);
        $modalAkhir = $modalDesaAwal + $modalMasyarakatAwal + $penambahanModalDesa + $penambahanModalMasyarakat;

        $saldoLabaAwal = $calculateBalanceUpTo('3.2.01.01', $startDate->copy()->subDay());

        $totalPendapatanPeriodeIni = Akun::where('tipe_akun', 'Pendapatan')->get()->reduce(function($carry, $akun) use ($calculateMutationInPeriod, $startDate, $endDate){
            return $carry + $calculateMutationInPeriod($akun->kode_akun, $startDate, $endDate);
        }, 0);

        $totalBebanPeriodeIni = Akun::whereIn('tipe_akun', ['Beban', 'HPP'])->get()->reduce(function($carry, $akun) use ($calculateBebanMutationInPeriod, $startDate, $endDate){
            return $carry + $calculateBebanMutationInPeriod($akun->kode_akun, $startDate, $endDate);
        }, 0);

        $labaRugiPeriodeIni = $totalPendapatanPeriodeIni - $totalBebanPeriodeIni;

        $bagiHasilDesa = $calculateBebanMutationInPeriod('3.3.01.01', $startDate, $endDate);
        $bagiHasilMasyarakat = $calculateBebanMutationInPeriod('3.3.02.01', $startDate, $endDate);

        $saldoLabaAkhir = $saldoLabaAwal + $labaRugiPeriodeIni - $bagiHasilDesa - $bagiHasilMasyarakat;

        $modalDonasi = $calculateBalanceUpTo('3.4.01.01', $endDate);

        $ekuitasAkhir = $modalAkhir + $saldoLabaAkhir + $modalDonasi;

        $bumdes = Bungdes::first(); // kirim data bumdes ke view

        return view('laporan.perubahan_ekuitas.show', compact(
            'startDate', 'endDate',
            'modalDesaAwal', 'modalMasyarakatAwal',
            'penambahanModalDesa', 'penambahanModalMasyarakat', 'modalAkhir',
            'saldoLabaAwal', 'labaRugiPeriodeIni', 'bagiHasilDesa', 'bagiHasilMasyarakat', 'saldoLabaAkhir',
            'modalDonasi', 'ekuitasAkhir', 'bumdes'
        ));
    }
}
