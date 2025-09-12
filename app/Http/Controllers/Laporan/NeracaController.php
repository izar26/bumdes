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

class NeracaController extends Controller
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

        return view('laporan.neraca.index', compact('unitUsahas', 'user'));
    }

    /**
     * Memproses filter dan menampilkan laporan komparatif.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id',
            'tanggal_cetak' => 'nullable|date',
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $unitUsahaId = $request->unit_usaha_id;
        $bumdes = Bungdes::first();
        $tanggalCetak = $request->tanggal_cetak ? Carbon::parse($request->tanggal_cetak) : now();

        // Helper function untuk menghitung data Neraca pada tanggal tertentu
        $getNeracaData = function(Carbon $reportDate) use ($user, $unitUsahaId) {
            $startOfYear = $reportDate->copy()->startOfYear();

            $baseQuery = DB::table('akuns')
                ->leftJoin('detail_jurnals', 'akuns.akun_id', '=', 'detail_jurnals.akun_id')
                ->leftJoin('jurnal_umums', function($join) use ($reportDate) {
                    $join->on('detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                         ->where('jurnal_umums.status', 'disetujui')
                         ->where('jurnal_umums.tanggal_transaksi', '<=', $reportDate);
                })
                ->where('akuns.is_header', 0);

            if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
                $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
                 $baseQuery->where(function($q) use ($managedUnitIds) {
                    $q->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
                    // Jika tidak ada filter unit, jurnal pusat (null) juga bisa masuk jika relevan
                    if(empty(request('unit_usaha_id'))) {
                        $q->orWhereNull('jurnal_umums.unit_usaha_id');
                    }
                });
            }
            elseif ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
                if (!empty($unitUsahaId)) {
                    $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
                }
            }

            $allAccountsBalance = (clone $baseQuery)
                ->select('akuns.nama_akun', 'akuns.tipe_akun',
                         DB::raw('SUM(detail_jurnals.debit) as total_debit'),
                         DB::raw('SUM(detail_jurnals.kredit) as total_kredit'))
                ->groupBy('akuns.akun_id', 'akuns.nama_akun', 'akuns.tipe_akun')
                ->get();
                
            $labaRugiAccounts = (clone $baseQuery)
                ->whereIn('akuns.tipe_akun', ['Pendapatan', 'Pendapatan & Beban Lainnya', 'Beban', 'HPP'])
                ->whereBetween('jurnal_umums.tanggal_transaksi', [$startOfYear, $reportDate])
                ->select('akuns.tipe_akun',
                         DB::raw('SUM(detail_jurnals.debit) as total_debit'),
                         DB::raw('SUM(detail_jurnals.kredit) as total_kredit'))
                ->groupBy('akuns.tipe_akun')
                ->get();

            $data = ['Aset' => [], 'Kewajiban' => [], 'Ekuitas' => []];
            $totalEkuitas = 0;

            foreach ($allAccountsBalance as $akun) {
                if ($akun->tipe_akun === 'Aset') {
                    $saldo = ($akun->total_debit ?? 0) - ($akun->total_kredit ?? 0);
                    if($saldo != 0) $data['Aset'][$akun->nama_akun] = $saldo;
                } elseif ($akun->tipe_akun === 'Kewajiban') {
                    $saldo = ($akun->total_kredit ?? 0) - ($akun->total_debit ?? 0);
                    if($saldo != 0) $data['Kewajiban'][$akun->nama_akun] = $saldo;
                } elseif ($akun->tipe_akun === 'Ekuitas') {
                    $saldo = ($akun->total_kredit ?? 0) - ($akun->total_debit ?? 0);
                    if($saldo != 0) {
                        $data['Ekuitas'][$akun->nama_akun] = $saldo;
                        $totalEkuitas += $saldo;
                    }
                }
            }

            $totalPendapatan = 0;
            $totalBeban = 0;
            foreach($labaRugiAccounts as $akun) {
                if (in_array($akun->tipe_akun, ['Pendapatan', 'Pendapatan & Beban Lainnya'])) {
                    $totalPendapatan += (($akun->total_kredit ?? 0) - ($akun->total_debit ?? 0));
                } elseif (in_array($akun->tipe_akun, ['Beban', 'HPP'])) {
                    $totalBeban += (($akun->total_debit ?? 0) - ($akun->total_kredit ?? 0));
                }
            }

            $data['labaDitahan'] = $totalPendapatan - $totalBeban;
            $data['totalEkuitas'] = $totalEkuitas + $data['labaDitahan'];

            return $data;
        };

        $dataAwal = $getNeracaData($startDate->copy()->subDay());
        $dataAkhir = $getNeracaData($endDate);

        $allAsetNames = array_unique(array_merge(array_keys($dataAwal['Aset']), array_keys($dataAkhir['Aset'])));
        $allKewajibanNames = array_unique(array_merge(array_keys($dataAwal['Kewajiban']), array_keys($dataAkhir['Kewajiban'])));
        $allEkuitasNames = array_unique(array_merge(array_keys($dataAwal['Ekuitas']), array_keys($dataAkhir['Ekuitas'])));
        
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];
        
        return view('laporan.neraca.show', compact(
            'bumdes', 'startDate', 'endDate', 'tanggalCetak',
            'dataAwal', 'dataAkhir',
            'allAsetNames', 'allKewajibanNames', 'allEkuitasNames',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}

