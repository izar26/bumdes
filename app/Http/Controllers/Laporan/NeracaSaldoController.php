<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\UnitUsaha;
use App\Models\Bungdes;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if ($user->hasAnyRole(['bendahara_bumdes', 'direktur_bumdes', 'admin_bumdes', 'sekretaris_bumdes'])) {
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->orderBy('nama_unit')->get();
        } elseif ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $unitUsahas = UnitUsaha::whereIn('unit_usaha_id', $unitUsahaIds)
                ->where('status_operasi', 'Aktif')
                ->orderBy('nama_unit')
                ->get();
        }

        return view('laporan.neraca_saldo.index', compact('unitUsahas', 'user'));
    }

    /**
     * Memproses filter dan menampilkan laporan.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            // --- PERUBAHAN 1: Mengizinkan nilai 'pusat' ---
            'unit_usaha_id' => 'nullable|string',
            'tanggal_cetak' => 'nullable|date',
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $unitUsahaId = $request->unit_usaha_id;
        $bumdes = Bungdes::first();
        $tanggalCetak = $request->tanggal_cetak ? Carbon::parse($request->tanggal_cetak) : now();

        $getNeracaSaldoData = function(Carbon $reportDate) use ($user, $unitUsahaId) {
            $baseQuery = DB::table('akuns')
                ->leftJoin('detail_jurnals', 'akuns.akun_id', '=', 'detail_jurnals.akun_id')
                ->leftJoin('jurnal_umums', function($join) use ($reportDate) {
                    $join->on('detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                         ->where('jurnal_umums.status', 'disetujui')
                         ->where('jurnal_umums.tanggal_transaksi', '<=', $reportDate);
                })
                ->where('akuns.is_header', 0)
                ->select('akuns.akun_id', 'akuns.kode_akun', 'akuns.nama_akun', 'akuns.tipe_akun',
                         DB::raw('COALESCE(SUM(detail_jurnals.debit), 0) as total_debit'),
                         DB::raw('COALESCE(SUM(detail_jurnals.kredit), 0) as total_kredit'));

            // --- PERUBAHAN 2: Menambahkan logika filter unit usaha ---
            if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
                $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
                $baseQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
            } elseif ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
                if ($unitUsahaId === 'pusat') {
                    $baseQuery->whereNull('jurnal_umums.unit_usaha_id');
                } elseif (!empty($unitUsahaId)) {
                    $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
                }
            }
            // --- AKHIR PERUBAHAN ---

            return $baseQuery
                ->groupBy('akuns.akun_id', 'akuns.kode_akun', 'akuns.nama_akun', 'akuns.tipe_akun')
                ->orderBy('akuns.kode_akun')
                ->get()
                ->keyBy('akun_id');
        };

        // Hitung data untuk Saldo Awal dan Saldo Akhir
        $dataAwal = $getNeracaSaldoData($startDate->copy()->subDay());
        $dataAkhir = $getNeracaSaldoData($endDate);
        
        $allAkunIds = $dataAwal->keys()->merge($dataAkhir->keys())->unique();
        $allAkuns = Akun::whereIn('akun_id', $allAkunIds)->orderBy('kode_akun')->get();

        $laporanData = [];
        $akunNormalDebit = ['Aset', 'HPP', 'Beban'];
        
        foreach ($allAkuns as $akun) {
            $akunAwal = $dataAwal->get($akun->akun_id);
            $akunAkhir = $dataAkhir->get($akun->akun_id);

            // Hitung Saldo Awal
            $debitAwal = 0; $kreditAwal = 0;
            if($akunAwal){
                if (in_array($akunAwal->tipe_akun, $akunNormalDebit)) {
                    $saldo = $akunAwal->total_debit - $akunAwal->total_kredit;
                    if (str_contains($akunAwal->nama_akun, 'Akumulasi Penyusutan') || str_contains($akunAwal->nama_akun, 'Penyisihan Piutang')) {
                        // Akun Kontra Aset, saldo normal kredit
                        $saldo = $akunAwal->total_kredit - $akunAwal->total_debit;
                        if ($saldo >= 0) { $kreditAwal = $saldo; } else { $debitAwal = abs($saldo); }
                    } else {
                        if ($saldo >= 0) { $debitAwal = $saldo; } else { $kreditAwal = abs($saldo); }
                    }
                } else { // Kewajiban, Ekuitas, Pendapatan
                    $saldo = $akunAwal->total_kredit - $akunAwal->total_debit;
                    if ($saldo >= 0) { $kreditAwal = $saldo; } else { $debitAwal = abs($saldo); }
                }
            }

            // Hitung Saldo Akhir
            $debitAkhir = 0; $kreditAkhir = 0;
            if($akunAkhir){
                 if (in_array($akunAkhir->tipe_akun, $akunNormalDebit)) {
                    $saldo = $akunAkhir->total_debit - $akunAkhir->total_kredit;
                     if (str_contains($akunAkhir->nama_akun, 'Akumulasi Penyusutan') || str_contains($akunAkhir->nama_akun, 'Penyisihan Piutang')) {
                        // Akun Kontra Aset, saldo normal kredit
                        $saldo = $akunAkhir->total_kredit - $akunAkhir->total_debit;
                        if ($saldo >= 0) { $kreditAkhir = $saldo; } else { $debitAkhir = abs($saldo); }
                    } else {
                        if ($saldo >= 0) { $debitAkhir = $saldo; } else { $kreditAkhir = abs($saldo); }
                    }
                } else { // Kewajiban, Ekuitas, Pendapatan
                    $saldo = $akunAkhir->total_kredit - $akunAkhir->total_debit;
                    if ($saldo >= 0) { $kreditAkhir = $saldo; } else { $debitAkhir = abs($saldo); }
                }
            }

            if ($debitAwal != 0 || $kreditAwal != 0 || $debitAkhir != 0 || $kreditAkhir != 0) {
                 $laporanData[] = (object)[
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'debit_awal' => $debitAwal,
                    'kredit_awal' => $kreditAwal,
                    'debit_akhir' => $debitAkhir,
                    'kredit_akhir' => $kreditAkhir,
                ];
            }
        }
        
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];

        return view('laporan.neraca_saldo.show', compact(
            'bumdes', 'startDate', 'endDate', 'tanggalCetak', 'laporanData',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}
