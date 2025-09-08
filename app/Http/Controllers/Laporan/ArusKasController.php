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
     * Menampilkan halaman form filter laporan Arus Kas.
     */
    public function index()
    {
        $user = Auth::user();
        $unitUsahas = collect();

        if ($user->hasAnyRole(['bendahara_bumdes', 'direktur_bumdes', 'admin_bumdes', 'sekretaris_bumdes'])) {
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->orderBy('nama_unit')->get();
        } elseif ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $unitUsahas = $user->unitUsahas()->where('status_operasi', 'Aktif')->orderBy('nama_unit')->get();
        }

        return view('laporan.arus_kas.index', compact('unitUsahas', 'user'));
    }

    /**
     * Memproses filter dan menampilkan laporan Arus Kas.
     */
    public function generate(Request $request)
    {
        // ... (validasi dan kode lain tetap sama)
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
        
        // ... (Logika perhitungan arus kas tetap sama)
        $baseQuery = JurnalUmum::with('detailJurnals.akun')
            ->where('status', 'disetujui')
            ->whereHas('detailJurnals.akun', function ($q) {
                $q->where('kode_akun', 'like', '1.1.01.%');
            });

        $baseQuery->whereBetween('tanggal_transaksi', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $baseQuery->whereIn('unit_usaha_id', $managedUnitIds);
             if ($unitUsahaId) {
                $baseQuery->where('unit_usaha_id', $unitUsahaId);
            }
        } elseif ($unitUsahaId) {
            $baseQuery->where('unit_usaha_id', $unitUsahaId);
        }
        
        $jurnals = $baseQuery->get();
        
        $arusOperasi = ['items' => [], 'total' => 0];
        $arusInvestasi = ['items' => [], 'total' => 0];
        $arusPendanaan = ['items' => [], 'total' => 0];

        foreach ($jurnals as $jurnal) {
            $kasMasuk = 0;
            $kasKeluar = 0;
            $akunLawan = null;

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
            $item = [
                'deskripsi' => $jurnal->deskripsi,
                'jumlah' => $pergerakanKas
            ];
            
            switch ($akunLawan->tipe_akun) {
                case 'Pendapatan':
                case 'Beban':
                case 'HPP':
                    $arusOperasi['items'][] = $item;
                    $arusOperasi['total'] += $pergerakanKas;
                    break;
                case 'Aset':
                    if(str_starts_with($akunLawan->kode_akun, '1.1.03.') || str_starts_with($akunLawan->kode_akun, '1.1.05.')){
                         $arusOperasi['items'][] = $item;
                         $arusOperasi['total'] += $pergerakanKas;
                    } else {
                         $arusInvestasi['items'][] = $item;
                         $arusInvestasi['total'] += $pergerakanKas;
                    }
                    break;
                case 'Kewajiban':
                case 'Ekuitas':
                    if(str_starts_with($akunLawan->kode_akun, '2.1.01.')){
                         $arusOperasi['items'][] = $item;
                         $arusOperasi['total'] += $pergerakanKas;
                    } else {
                        $arusPendanaan['items'][] = $item;
                        $arusPendanaan['total'] += $pergerakanKas;
                    }
                    break;
            }
        }
        
        $saldoKasAwal = DB::table('detail_jurnals')
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
            ->where('jurnal_umums.status', 'disetujui')
            ->where('akuns.kode_akun', 'like', '1.1.01.%')
            ->where('jurnal_umums.tanggal_transaksi', '<', $startDate->toDateString())
            ->when($unitUsahaId, fn($q) => $q->where('jurnal_umums.unit_usaha_id', $unitUsahaId))
            ->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));

        $kenaikanPenurunanKas = $arusOperasi['total'] + $arusInvestasi['total'] + $arusPendanaan['total'];
        $saldoKasAkhir = $saldoKasAwal + $kenaikanPenurunanKas;
        
        // --- PERBAIKAN DIMULAI: Logika penandatangan dinamis ---
        
        // Selalu cari Direktur sebagai penandatangan utama (menyetujui)
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = [
            'jabatan' => 'Direktur',
            'nama'    => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'
        ];

        $penandaTangan2 = []; // Kosongkan dulu

        // Jika laporan ini untuk unit usaha spesifik
        if ($unitUsahaId) {
            // Cari unit usaha tersebut dan penanggung jawabnya
            $unitUsaha = UnitUsaha::with('penanggungJawab.anggota')->find($unitUsahaId);
            $manajer = $unitUsaha ? $unitUsaha->penanggungJawab : null;
            
            $penandaTangan2 = [
                'jabatan' => 'Manajer Unit Usaha',
                'nama'    => $manajer && $manajer->anggota ? $manajer->anggota->nama_lengkap : '....................'
            ];
        } 
        // Jika laporan ini untuk keseluruhan (tidak ada unit usaha yang dipilih)
        else {
            // Cari Bendahara sebagai penanggung jawab
            $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
            $penandaTangan2 = [
                'jabatan' => 'Bendahara',
                'nama'    => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'
            ];
        }

        // --- AKHIR PERBAIKAN ---

        return view('laporan.arus_kas.show', compact(
            'bumdes', 'startDate', 'endDate',
            'arusOperasi', 'arusInvestasi', 'arusPendanaan',
            'saldoKasAwal', 'kenaikanPenurunanKas', 'saldoKasAkhir',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}


