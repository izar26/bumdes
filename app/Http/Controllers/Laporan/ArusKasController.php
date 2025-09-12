<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\JurnalUmum;
use App\Models\UnitUsaha;
use App\Models\Bungdes;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArusKasController extends Controller
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

        return view('laporan.arus_kas.index', compact('unitUsahas', 'user'));
    }

    /**
     * Memproses filter dan menampilkan laporan.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id',
            'tanggal_cetak' => 'nullable|date', // 1. Tambahkan validasi
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $unitUsahaId = $request->unit_usaha_id;
        $bumdes = Bungdes::first();
        
        // 2. Proses tanggal cetak
        $tanggalCetak = $request->tanggal_cetak ? Carbon::parse($request->tanggal_cetak) : now();

        $baseQuery = JurnalUmum::with('detailJurnals.akun')
            ->where('status', 'disetujui')
            ->whereHas('detailJurnals.akun', function ($q) {
                $q->where('kode_akun', 'like', '1.1.01.%');
            });

        $baseQuery->whereBetween('tanggal_transaksi', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $baseQuery->where(function($q) use ($managedUnitIds) {
                $q->whereIn('unit_usaha_id', $managedUnitIds);
                if(empty(request('unit_usaha_id'))) {
                    $q->orWhereNull('unit_usaha_id');
                }
            });
        } elseif ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
            if (!empty($unitUsahaId)) {
                $baseQuery->where('unit_usaha_id', $unitUsahaId);
            }
        }
        
        $jurnals = $baseQuery->get();
        
        $arusOperasi = ['items' => [], 'total' => 0];
        $arusInvestasi = ['items' => [], 'total' => 0];
        $arusPendanaan = ['items' => [], 'total' => 0];

        foreach ($jurnals as $jurnal) {
            $kasMasuk = 0; $kasKeluar = 0; $akunLawan = null;
            foreach ($jurnal->detailJurnals as $detail) {
                if (str_starts_with($detail->akun->kode_akun, '1.1.01.')) {
                    $kasMasuk += $detail->debit;
                    $kasKeluar += $detail->kredit;
                } else {
                    $akunLawan = $detail->akun;
                }
            }
            if (!$akunLawan) continue;

            $pergerakanKas = $kasMasuk - $kasKeluar;
            $item = ['deskripsi' => $jurnal->deskripsi, 'jumlah' => $pergerakanKas];
            
            switch ($akunLawan->tipe_akun) {
                case 'Pendapatan': case 'Beban': case 'HPP':
                    $arusOperasi['items'][] = $item; $arusOperasi['total'] += $pergerakanKas; break;
                case 'Aset':
                    if(str_starts_with($akunLawan->kode_akun, '1.1.03.') || str_starts_with($akunLawan->kode_akun, '1.1.05.')){
                         $arusOperasi['items'][] = $item; $arusOperasi['total'] += $pergerakanKas;
                    } else {
                         $arusInvestasi['items'][] = $item; $arusInvestasi['total'] += $pergerakanKas;
                    }
                    break;
                case 'Kewajiban': case 'Ekuitas':
                    if(str_starts_with($akunLawan->kode_akun, '2.1.01.')){
                         $arusOperasi['items'][] = $item; $arusOperasi['total'] += $pergerakanKas;
                    } else {
                        $arusPendanaan['items'][] = $item; $arusPendanaan['total'] += $pergerakanKas;
                    }
                    break;
            }
        }
        
        $saldoKasAwalQuery = DB::table('detail_jurnals')
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
            ->where('jurnal_umums.status', 'disetujui')
            ->where('akuns.kode_akun', 'like', '1.1.01.%')
            ->where('jurnal_umums.tanggal_transaksi', '<', $startDate->toDateString());

        if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $saldoKasAwalQuery->where(function($q) use ($managedUnitIds) {
                $q->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds)->orWhereNull('jurnal_umums.unit_usaha_id');
            });
        } elseif ($unitUsahaId) {
             $saldoKasAwalQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }

        $saldoKasAwal = $saldoKasAwalQuery->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));
        $kenaikanPenurunanKas = $arusOperasi['total'] + $arusInvestasi['total'] + $arusPendanaan['total'];
        $saldoKasAkhir = $saldoKasAwal + $kenaikanPenurunanKas;

        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];

        return view('laporan.arus_kas.show', compact(
            'bumdes', 'startDate', 'endDate', 'tanggalCetak',
            'arusOperasi', 'arusInvestasi', 'arusPendanaan',
            'saldoKasAwal', 'kenaikanPenurunanKas', 'saldoKasAkhir',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}

