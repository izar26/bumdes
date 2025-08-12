<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use Carbon\Carbon;
use App\Models\UnitUsaha;
use Illuminate\Support\Facades\Auth;

class ArusKasController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $unitUsahas = collect();
        if ($user->hasRole('bendahara_bumdes')) {
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        } else {
            $unitUsahas = $user->unitUsahas()->where('status_operasi', 'Aktif')->get();
        }

        return view('laporan.arus_kas.index', compact('unitUsahas'));
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

        // 1. Dapatkan semua ID akun yang tergolong Kas & Bank
        $akunKasBankIds = Akun::where('tipe_akun', 'Aset')
                                 ->where(function ($query) {
                                     $query->where('nama_akun', 'LIKE', '%Kas%')
                                           ->orWhere('nama_akun', 'LIKE', '%Bank%');
                                 })
                                 ->where('is_header', 0)
                                 ->pluck('akun_id');

        // Query dasar yang sudah difilter berdasarkan status 'disetujui'
        $baseQuery = DetailJurnal::whereIn('akun_id', $akunKasBankIds)
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('jurnal_umums.status', 'disetujui');

        // Terapkan filter unit usaha berdasarkan peran
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usaha_id');
            $baseQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
        } elseif ($user->hasRole('bendahara_bumdes') && !empty($unitUsahaId)) {
            $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }

        // 2. Hitung Saldo Kas Awal dari transaksi yang sudah disetujui
        $saldoAwalQuery = (clone $baseQuery)->where('jurnal_umums.tanggal_transaksi', '<', $startDate);
        $debitAwal = (clone $saldoAwalQuery)->sum('detail_jurnals.debit');
        $kreditAwal = (clone $saldoAwalQuery)->sum('detail_jurnals.kredit');
        $saldoKasAwal = $debitAwal - $kreditAwal;

        // 3. Ambil semua transaksi yang melibatkan kas dalam periode ini yang sudah disetujui
        $transaksiKas = (clone $baseQuery)
            ->with('jurnal.detailJurnals.akun')
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
                    if ($akunLawan) { // Pastikan akun lawan ada
                        $item = ['nama' => $akunLawan->nama_akun, 'jumlah' => $jumlah];

                        // Kelompokkan berdasarkan tipe akun lawan
                        if (in_array($akunLawan->tipe_akun, ['Pendapatan', 'HPP', 'Beban', 'Piutang', 'Kewajiban', 'Persediaan'])) {
                            $arusOperasi[] = $item;
                        } elseif ($akunLawan->tipe_akun == 'Aset') { // Aset Tetap, dll.
                            $arusInvestasi[] = $item;
                        } elseif ($akunLawan->tipe_akun == 'Ekuitas') {
                            $arusPendanaan[] = $item;
                        }
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
