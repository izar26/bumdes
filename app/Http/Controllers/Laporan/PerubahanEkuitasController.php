<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use App\Models\Bungdes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;

class PerubahanEkuitasController extends Controller
{
    /**
     * Menampilkan halaman form filter laporan Perubahan Ekuitas.
     */
    public function index()
    {
        $user = Auth::user();
        $unitUsahas = collect();

        if ($user->hasRole(['bendahara_bumdes', 'sekretaris_bumdes'])) {
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        } elseif ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            // FIX: Tambahkan nama tabel eksplisit untuk menghindari ambiguitas
            $unitUsahas = $user->unitUsahas()
                               ->where('status_operasi', 'Aktif')
                               ->select('unit_usahas.unit_usaha_id', 'unit_usahas.nama_unit')
                               ->get();
        }

        return view('laporan.perubahan_ekuitas.index', compact('unitUsahas'));
    }

    /**
     * Memproses filter dan menampilkan laporan Perubahan Ekuitas.
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

        $bumdes = Bungdes::first();

        // --- PERBAIKAN: Gunakan satu query untuk mengambil semua saldo ---
        $managedUnitIds = collect();
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
        } elseif ($user->hasRole(['bendahara_bumdes', 'sekretaris_bumdes'])) {
            if (!empty($unitUsahaId)) {
                $managedUnitIds = collect([$unitUsahaId]);
            }
        }

        // Helper untuk mendapatkan saldo awal dan mutasi dalam satu query yang efisien
        $getBalancesAndMutations = function(Carbon $date, $isStartBalance = false) use ($managedUnitIds, $unitUsahaId) {
            $query = Akun::select('akuns.kode_akun', 'akuns.tipe_akun')
                ->selectRaw('SUM(CASE WHEN jurnal_umums.tanggal_transaksi <= ? THEN detail_jurnals.debit ELSE 0 END) as total_debit', [$date])
                ->selectRaw('SUM(CASE WHEN jurnal_umums.tanggal_transaksi <= ? THEN detail_jurnals.kredit ELSE 0 END) as total_kredit', [$date]);

            $query->join('detail_jurnals', 'akuns.akun_id', '=', 'detail_jurnals.akun_id')
                  ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                  ->where('jurnal_umums.status', 'disetujui');

            if ($managedUnitIds->isNotEmpty()) {
                $query->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
            } elseif (!empty($unitUsahaId)) {
                $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
            }

            return $query->groupBy('akuns.kode_akun', 'akuns.tipe_akun')->get();
        };

        $saldoAwalPeriod = $startDate->copy()->subDay();
        $balancesUpToStart = $getBalancesAndMutations($saldoAwalPeriod);
        $balancesUpToEnd = $getBalancesAndMutations($endDate);

        $getSaldo = function($kode_akun, $balances) {
            $akun = $balances->where('kode_akun', $kode_akun)->first();
            if (!$akun) return 0;
            if ($akun->tipe_akun == 'Aset' || $akun->tipe_akun == 'Beban' || $akun->tipe_akun == 'HPP') {
                return $akun->total_debit - $akun->total_kredit;
            }
            return $akun->total_kredit - $akun->total_debit;
        };

        $getMutation = function($kode_akun, $balancesStart, $balancesEnd) use ($getSaldo) {
            return $getSaldo($kode_akun, $balancesEnd) - $getSaldo($kode_akun, $balancesStart);
        };

        // Perhitungan saldo awal
        $modalDesaAwal = $getSaldo('3.1.01.01', $balancesUpToStart);
        $modalMasyarakatAwal = $getSaldo('3.1.02.01', $balancesUpToStart);
        $saldoLabaAwal = $getSaldo('3.3.01.01', $balancesUpToStart);
        $modalDonasiAwal = $getSaldo('3.4.01.01', $balancesUpToStart);

        // Perhitungan mutasi selama periode
        $penambahanModalDesa = $getMutation('3.1.01.01', $balancesUpToStart, $balancesUpToEnd);
        $penambahanModalMasyarakat = $getMutation('3.1.02.01', $balancesUpToStart, $balancesUpToEnd);

        $totalPendapatan = $getMutation('4.00.00.00', $balancesUpToStart, $balancesUpToEnd);
        $totalHpp = $getMutation('5.00.00.00', $balancesUpToStart, $balancesUpToEnd);
        $totalBeban = $getMutation('6.00.00.00', $balancesUpToStart, $balancesUpToEnd);
        $totalPendapatanBebanLainnya = $getMutation('7.00.00.00', $balancesUpToStart, $balancesUpToEnd);

        $labaRugiPeriodeIni = $totalPendapatan - $totalHpp - $totalBeban + $totalPendapatanBebanLainnya;

        $bagiHasilDesa = $getMutation('3.2.01.01', $balancesUpToStart, $balancesUpToEnd); // Ini akan negatif
        $bagiHasilMasyarakat = $getMutation('3.2.02.01', $balancesUpToStart, $balancesUpToEnd); // Ini akan negatif

        // Perhitungan saldo akhir
        $modalDesaAkhir = $modalDesaAwal + $penambahanModalDesa;
        $modalMasyarakatAkhir = $modalMasyarakatAwal + $penambahanModalMasyarakat;
        $saldoLabaAkhir = $saldoLabaAwal + $labaRugiPeriodeIni + $bagiHasilDesa + $bagiHasilMasyarakat;
        $modalDonasiAkhir = $getSaldo('3.4.01.01', $balancesUpToEnd);

        $ekuitasAkhir = $modalDesaAkhir + $modalMasyarakatAkhir + $saldoLabaAkhir + $modalDonasiAkhir;

        return view('laporan.perubahan_ekuitas.show', compact(
            'startDate', 'endDate', 'bumdes',
            'modalDesaAwal', 'modalMasyarakatAwal',
            'penambahanModalDesa', 'penambahanModalMasyarakat', 'modalDesaAkhir', 'modalMasyarakatAkhir',
            'saldoLabaAwal', 'labaRugiPeriodeIni', 'bagiHasilDesa', 'bagiHasilMasyarakat', 'saldoLabaAkhir',
            'modalDonasiAkhir', 'ekuitasAkhir'
        ));
    }
}
