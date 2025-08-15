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
use Illuminate\Auth\Access\AuthorizationException;

class NeracaController extends Controller
{
    // Tambahkan middleware otorisasi di sini
    public function __construct()
    {
        $this->middleware('role:bendahara_bumdes|admin_bumdes');
    }

    /**
     * Menampilkan halaman form filter laporan Neraca.
     */
    public function index()
    {
        // Peran non-BUMDes tidak bisa mencapai sini karena middleware
        $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        return view('laporan.neraca.index', compact('unitUsahas'));
    }

    /**
     * Memproses filter dan menampilkan laporan Neraca.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id'
        ]);

        $user = Auth::user();
        $reportDate = Carbon::parse($request->report_date);
        $unitUsahaId = $request->unit_usaha_id;
        $bumdes = Bungdes::first();

        // Logika filter unit usaha hanya untuk bendahara/admin BUMDes
        $managedUnitIds = collect();
        if (!empty($unitUsahaId)) {
            $managedUnitIds = collect([$unitUsahaId]);
        }

        // Gunakan satu query yang efisien untuk mengambil semua saldo
        $query = Akun::select('akuns.nama_akun', 'akuns.tipe_akun')
            ->selectRaw('SUM(detail_jurnals.debit) as total_debit')
            ->selectRaw('SUM(detail_jurnals.kredit) as total_kredit')
            ->join('detail_jurnals', 'akuns.akun_id', '=', 'detail_jurnals.akun_id')
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('jurnal_umums.status', 'disetujui')
            ->where('jurnal_umums.tanggal_transaksi', '<=', $reportDate);

        if ($managedUnitIds->isNotEmpty()) {
            $query->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
        }

        $allBalances = $query->groupBy('akuns.akun_id', 'akuns.nama_akun', 'akuns.tipe_akun')->get();

        $asets = []; $totalAset = 0;
        $kewajibans = []; $totalKewajiban = 0;
        $ekuitas = []; $totalEkuitas = 0;
        $totalPendapatan = 0;
        $totalBeban = 0;
        $totalHpp = 0;

        foreach ($allBalances as $akun) {
            $saldo = 0;
            if (in_array($akun->tipe_akun, ['Aset', 'Beban', 'HPP'])) {
                $saldo = $akun->total_debit - $akun->total_kredit;
            } else {
                $saldo = $akun->total_kredit - $akun->total_debit;
            }

            if ($saldo != 0) {
                if ($akun->tipe_akun === 'Aset') {
                    $asets[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                    $totalAset += $saldo;
                } elseif ($akun->tipe_akun === 'Kewajiban') {
                    $kewajibans[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                    $totalKewajiban += $saldo;
                } elseif ($akun->tipe_akun === 'Ekuitas') {
                    // Akun Ekuitas selain laba ditahan
                    $ekuitas[] = ['nama_akun' => $akun->nama_akun, 'total' => $saldo];
                    $totalEkuitas += $saldo;
                } elseif ($akun->tipe_akun === 'Pendapatan') {
                    $totalPendapatan += $saldo;
                } elseif ($akun->tipe_akun === 'Beban') {
                    $totalBeban += $saldo;
                } elseif ($akun->tipe_akun === 'HPP') {
                    $totalHpp += $saldo;
                }
            }
        }

        // Perhitungan laba ditahan
        $labaDitahan = ($totalPendapatan - $totalHpp) - $totalBeban;
        $totalEkuitas += $labaDitahan;

        $totalKewajibanDanEkuitas = $totalKewajiban + $totalEkuitas;

        // Menyiapkan data penanda tangan
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
