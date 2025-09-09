<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use App\Models\Bungdes;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NeracaController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:bendahara_bumdes|sekretaris_bumdes|direktur_bumdes');
    }

    public function index()
    {
        $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        return view('laporan.neraca.index', compact('unitUsahas'));
    }

    public function generate(Request $request)
    {
        // 1. VALIDASI DIUBAH UNTUK MENERIMA DUA TIPE FILTER
        $request->validate([
            'filter_type' => 'required|in:monthly,daily',
            'month' => 'required_if:filter_type,monthly|nullable|date_format:Y-m',
            'report_date' => 'required_if:filter_type,daily|nullable|date',
            'unit_usaha_id' => 'nullable'
        ]);

        $bumdes = Bungdes::first();
        $unitUsahaId = $request->unit_usaha_id;

        // 2. TENTUKAN TANGGAL LAPORAN BERDASARKAN TIPE FILTER
        $reportDate = null;
        if ($request->filter_type === 'monthly') {
            $reportDate = Carbon::parse($request->month)->endOfMonth();
        } else { // daily
            $reportDate = Carbon::parse($request->report_date);
        }

        // --- PERBAIKAN LOGIKA AKUNTANSI DIMULAI DI SINI ---

        // Tentukan tanggal awal tahun fiskal berdasarkan tanggal laporan
        $startOfYear = $reportDate->copy()->startOfYear();

        // Query dasar yang akan kita gunakan kembali
        $baseQuery = DetailJurnal::join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
            ->where('jurnal_umums.status', 'disetujui');

        // Terapkan filter unit usaha jika ada
        if (!empty($unitUsahaId)) {
            $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }

        // Query #1: Ambil saldo untuk AKUN NERACA (Aset, Kewajiban, Ekuitas)
        // Dihitung kumulatif dari awal waktu hingga tanggal laporan
        $balanceSheetAccounts = (clone $baseQuery)
            ->whereIn('akuns.tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas'])
            ->where('jurnal_umums.tanggal_transaksi', '<=', $reportDate)
            ->select('akuns.nama_akun', 'akuns.tipe_akun',
                     DB::raw('SUM(detail_jurnals.debit) as total_debit'),
                     DB::raw('SUM(detail_jurnals.kredit) as total_kredit'))
            ->groupBy('akuns.akun_id', 'akuns.nama_akun', 'akuns.tipe_akun')
            ->get();

        // Query #2: Ambil saldo untuk AKUN LABA RUGI (Pendapatan, Beban)
        // Dihitung HANYA untuk TAHUN BERJALAN
        $incomeStatementAccounts = (clone $baseQuery)
            ->whereIn('akuns.tipe_akun', ['Pendapatan', 'Pendapatan & Beban Lainnya', 'Beban', 'HPP'])
            ->whereBetween('jurnal_umums.tanggal_transaksi', [$startOfYear, $reportDate])
            ->select('akuns.tipe_akun',
                     DB::raw('SUM(detail_jurnals.debit) as total_debit'),
                     DB::raw('SUM(detail_jurnals.kredit) as total_kredit'))
            ->groupBy('akuns.tipe_akun')
            ->get();

        // Proses saldo Akun Neraca
        $asets = []; $totalAset = 0;
        $kewajibans = []; $totalKewajiban = 0;
        $ekuitas = []; $totalEkuitas = 0;

        foreach ($balanceSheetAccounts as $akun) {
             if ($akun->tipe_akun === 'Aset') {
                $saldo = $akun->total_debit - $akun->total_kredit;
                if($saldo != 0) {
                    $asets[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                    $totalAset += $saldo;
                }
            } elseif ($akun->tipe_akun === 'Kewajiban') {
                $saldo = $akun->total_kredit - $akun->total_debit;
                if($saldo != 0) {
                    $kewajibans[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                    $totalKewajiban += $saldo;
                }
            } elseif ($akun->tipe_akun === 'Ekuitas') {
                $saldo = $akun->total_kredit - $akun->total_debit;
                if($saldo != 0) {
                    $ekuitas[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                    $totalEkuitas += $saldo;
                }
            }
        }

        // Proses saldo Akun Laba Rugi untuk mendapatkan Laba Tahun Berjalan
        $totalPendapatan = 0;
        $totalBeban = 0;
        foreach($incomeStatementAccounts as $akun) {
            if (in_array($akun->tipe_akun, ['Pendapatan', 'Pendapatan & Beban Lainnya'])) {
                $totalPendapatan += ($akun->total_kredit - $akun->total_debit);
            } elseif (in_array($akun->tipe_akun, ['Beban', 'HPP'])) {
                $totalBeban += ($akun->total_debit - $akun->total_kredit);
            }
        }

        // Laba Tahun Berjalan dihitung dan ditambahkan ke Ekuitas
        $labaDitahan = $totalPendapatan - $totalBeban;
        $totalEkuitas += $labaDitahan;

        $totalKewajibanDanEkuitas = $totalKewajiban + $totalEkuitas;

        $penandaTangan1 = [ 'jabatan' => 'Direktur', 'nama' => 'Nama Direktur Anda' ];
        $penandaTangan2 = [ 'jabatan' => 'Bendahara', 'nama' => 'Nama Bendahara Anda' ];

        return view('laporan.neraca.show', compact(
            'reportDate', 'asets', 'totalAset', 'kewajibans',
            'totalKewajiban', 'ekuitas', 'totalEkuitas',
            'labaDitahan', 'totalKewajibanDanEkuitas', 'bumdes',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}
