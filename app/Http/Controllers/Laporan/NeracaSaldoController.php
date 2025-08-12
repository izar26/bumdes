<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NeracaSaldoController extends Controller
{
    /**
     * Menampilkan halaman form filter.
     */
    public function index()
    {
        $user = Auth::user();
        $unitUsahas = collect();
        if ($user->hasRole('bendahara_bumdes')) {
            // Bendahara bisa filter semua unit usaha
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        } else {
            // Peran lain hanya melihat unit usahanya sendiri
            $unitUsahas = $user->unitUsahas()->where('status_operasi', 'Aktif')->get();
        }
        return view('laporan.neraca_saldo.index', compact('unitUsahas'));
    }

    /**
     * Memproses filter dan menampilkan laporan.
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

        // Query dasar yang sudah difilter berdasarkan status jurnal dan unit usaha
        $baseQuery = DetailJurnal::join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('jurnal_umums.status', 'disetujui') // Filter utama
            ->where('jurnal_umums.tanggal_transaksi', '<=', $reportDate);

        // Terapkan filter unit usaha berdasarkan peran
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usaha_id');
            $baseQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
        } elseif ($user->hasRole('bendahara_bumdes') && !empty($unitUsahaId)) {
            $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }

        // Ambil semua akun detail
        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun')->get();
        
        $laporanData = [];
        $totalDebit = 0;
        $totalKredit = 0;

        // Loop setiap akun untuk menghitung saldo akhirnya
        foreach ($akuns as $akun) {
            $query = (clone $baseQuery)->where('akun_id', $akun->akun_id);
            $debit = (clone $query)->sum('detail_jurnals.debit');
            $kredit = (clone $query)->sum('detail_jurnals.kredit');

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
            } else { // Kewajiban, Ekuitas, Pendapatan
                // Saldo normal di Kredit
                $saldo = $kredit - $debit;
                if ($saldo > 0) {
                    $saldoKredit = $saldo;
                } else {
                    $saldoDebit = abs($saldo);
                }
            }
            
            // Hanya tampilkan akun yang memiliki saldo
            if ($saldoDebit != 0 || $saldoKredit != 0) {
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
