<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        $baseQuery = JurnalUmum::with('detailJurnals.akun')
            ->where('status', 'disetujui')
            ->whereHas('detailJurnals.akun', function ($q) {
                // Hanya ambil jurnal yang melibatkan akun Kas & Setara Kas
                $q->where('kode_akun', 'like', '1.1.01.%');
            })
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate]);

        // --- PERUBAHAN 2: Menambahkan logika filter unit usaha ---
        if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $baseQuery->whereIn('unit_usaha_id', $managedUnitIds);
        } elseif ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
            if ($unitUsahaId === 'pusat') {
                $baseQuery->whereNull('unit_usaha_id');
            } elseif (!empty($unitUsahaId)) {
                $baseQuery->where('unit_usaha_id', $unitUsahaId);
            }
        }
        
        $jurnals = $baseQuery->get();
        
        $arusOperasi = ['items' => [], 'total' => 0];
        $arusInvestasi = ['items' => [], 'total' => 0];
        $arusPendanaan = ['items' => [], 'total' => 0];

        foreach ($jurnals as $jurnal) {
            $kasMasuk = 0; $kasKeluar = 0; $akunLawanList = [];
            
            foreach ($jurnal->detailJurnals as $detail) {
                if (str_starts_with($detail->akun->kode_akun, '1.1.01.')) {
                    $kasMasuk += $detail->debit;
                    $kasKeluar += $detail->kredit;
                } else {
                    $akunLawanList[] = $detail->akun;
                }
            }
            
            // Logika sederhana: ambil akun lawan pertama sebagai representasi
            if (empty($akunLawanList)) continue;
            $akunLawan = $akunLawanList[0]; 

            $pergerakanKas = $kasMasuk - $kasKeluar;
            $item = ['deskripsi' => $jurnal->deskripsi, 'jumlah' => $pergerakanKas];
            
            // Klasifikasi berdasarkan tipe akun lawan
            switch ($akunLawan->tipe_akun) {
                case 'Pendapatan': case 'Beban': case 'HPP':
                case 'Pendapatan & Beban Lainnya':
                    $arusOperasi['items'][] = $item; $arusOperasi['total'] += $pergerakanKas; break;
                case 'Aset':
                    // Piutang, Persediaan, Beban Dibayar Dimuka dianggap Operasi
                    if(str_starts_with($akunLawan->kode_akun, '1.1.03.') || str_starts_with($akunLawan->kode_akun, '1.1.05.') || str_starts_with($akunLawan->kode_akun, '1.1.07.')){
                         $arusOperasi['items'][] = $item; $arusOperasi['total'] += $pergerakanKas;
                    } else { // Aset tetap/jangka panjang dianggap Investasi
                         $arusInvestasi['items'][] = $item; $arusInvestasi['total'] += $pergerakanKas;
                    }
                    break;
                case 'Kewajiban': case 'Ekuitas':
                    // Utang Usaha dianggap Operasi
                    if(str_starts_with($akunLawan->kode_akun, '2.1.01.')){
                         $arusOperasi['items'][] = $item; $arusOperasi['total'] += $pergerakanKas;
                    } else { // Modal dan Utang Jangka Panjang dianggap Pendanaan
                        $arusPendanaan['items'][] = $item; $arusPendanaan['total'] += $pergerakanKas;
                    }
                    break;
            }
        }
        
        // --- PERUBAHAN 3: Menambahkan logika filter pada query saldo awal ---
        $saldoKasAwalQuery = DB::table('detail_jurnals')
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
            ->where('jurnal_umums.status', 'disetujui')
            ->where('akuns.kode_akun', 'like', '1.1.01.%')
            ->where('jurnal_umums.tanggal_transaksi', '<', $startDate->toDateString());

        if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $saldoKasAwalQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
        } elseif ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
            if ($unitUsahaId === 'pusat') {
                $saldoKasAwalQuery->whereNull('jurnal_umums.unit_usaha_id');
            } elseif (!empty($unitUsahaId)) {
                $saldoKasAwalQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
            }
        }
        // --- AKHIR PERUBAHAN ---

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
