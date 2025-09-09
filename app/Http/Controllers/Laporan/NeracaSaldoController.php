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

class NeracaSaldoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $unitUsahas = collect();

        if ($user->hasRole(['bendahara_bumdes', 'sekretaris_bumdes', 'manajer_unit_usaha', 'admin_unit_usaha'])) {
             if ($user->hasRole(['bendahara_bumdes', 'sekretaris_bumdes'])) {
                $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
            } else {
                $unitUsahas = $user->unitUsahas()->where('status_operasi', 'Aktif')->select('unit_usahas.*')->get();
            }
        }
        return view('laporan.neraca_saldo.index', compact('unitUsahas'));
    }

    public function generate(Request $request)
    {
        // 1. VALIDASI DIUBAH UNTUK MENERIMA DUA TIPE FILTER
        $request->validate([
            'filter_type' => 'required|in:monthly,daily',
            'month' => 'required_if:filter_type,monthly|nullable|date_format:Y-m',
            'report_date' => 'required_if:filter_type,daily|nullable|date',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id'
        ]);

        $user = Auth::user();
        $unitUsahaId = $request->unit_usaha_id;
        $bumdes = Bungdes::first();

        // 2. TENTUKAN TANGGAL LAPORAN BERDASARKAN TIPE FILTER
        $reportDate = null;
        if ($request->filter_type === 'monthly') {
            $reportDate = Carbon::parse($request->month)->endOfMonth();
        } else { // daily
            $reportDate = Carbon::parse($request->report_date);
        }

        // Query untuk Neraca Saldo sudah benar (mengambil semua akun secara kumulatif).
        // Tidak perlu diubah, hanya menggunakan $reportDate yang baru.
        $query = Akun::select('akuns.kode_akun', 'akuns.nama_akun', 'akuns.saldo_normal')
            ->selectRaw('SUM(detail_jurnals.debit) as total_debit')
            ->selectRaw('SUM(detail_jurnals.kredit) as total_kredit')
            ->join('detail_jurnals', 'akuns.akun_id', '=', 'detail_jurnals.akun_id')
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('jurnal_umums.status', 'disetujui')
            ->where('jurnal_umums.tanggal_transaksi', '<=', $reportDate);

        // Logika filter unit usaha yang disatukan
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $query->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
        }
        elseif ($user->hasRole(['bendahara_bumdes', 'sekretaris_bumdes']) && !empty($unitUsahaId)) {
            $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }

        $results = $query->groupBy('akuns.akun_id', 'akuns.kode_akun', 'akuns.nama_akun', 'akuns.saldo_normal')
            ->orderBy('akuns.kode_akun')
            ->get();

        // Memproses data untuk memisahkan saldo ke kolom Debit dan Kredit akhir
        $laporanData = [];
        $totalDebit = 0;
        $totalKredit = 0;

        foreach ($results as $akun) {
            $saldo = 0;
            $saldoDebit = 0;
            $saldoKredit = 0;

            if ($akun->saldo_normal == 'D') {
                $saldo = $akun->total_debit - $akun->total_kredit;
                if ($saldo > 0) {
                    $saldoDebit = $saldo;
                } else {
                    $saldoKredit = abs($saldo);
                }
            } else { // Saldo normal Kredit
                $saldo = $akun->total_kredit - $akun->total_debit;
                 if ($saldo > 0) {
                    $saldoKredit = $saldo;
                } else {
                    $saldoDebit = abs($saldo);
                }
            }

            // Hanya tampilkan akun yang memiliki saldo
            if($saldo != 0) {
                $laporanData[] = (object)[
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'debit' => $saldoDebit,
                    'kredit' => $saldoKredit,
                ];
                $totalDebit += $saldoDebit;
                $totalKredit += $saldoKredit;
            }
        }

        $penandaTangan1 = [ 'jabatan' => 'Direktur', 'nama' => 'Nama Direktur Anda' ];
        $penandaTangan2 = [ 'jabatan' => 'Bendahara', 'nama' => 'Nama Bendahara Anda' ];

        return view('laporan.neraca_saldo.show', compact(
            'reportDate',
            'laporanData',
            'totalDebit',
            'totalKredit',
            'bumdes',
            'penandaTangan1',
            'penandaTangan2'
        ));
    }
}
