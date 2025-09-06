<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use App\Models\Bungdes;
use App\Models\User; // Pastikan model User di-import
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LabaRugiController extends Controller
{
    /**
     * Menampilkan halaman form filter laporan Laba Rugi.
     */
    public function index()
    {
        $user = Auth::user();
        $unitUsahas = null;

        if ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')
                                        ->orderBy('nama_unit')
                                        ->get();
        }
        elseif ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $unitUsahas = UnitUsaha::whereIn('unit_usaha_id', $unitUsahaIds)
                                    ->where('status_operasi', 'Aktif')
                                    ->orderBy('nama_unit')
                                    ->get();
        }
        else {
            $unitUsahas = collect();
        }

        return view('laporan.laba_rugi.index', compact('unitUsahas', 'user'));
    }

    /**
     * Memproses filter dan menampilkan laporan Laba Rugi.
     */
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

        $bumdes = Bungdes::first();

        $pendapatanTipes = ['Pendapatan'];
        $hppTipes = ['HPP'];
        $bebanTipes = ['Beban'];
        $pendapatanBebanLainTipes = ['Pendapatan & Beban Lainnya'];

        $calculateSaldoes = function($tipeAkunArray) use ($user, $startDate, $endDate, $unitUsahaId) {
            $query = DetailJurnal::query()
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->whereIn('akuns.tipe_akun', $tipeAkunArray)
                ->where('jurnal_umums.status', 'disetujui')
                ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate->toDateString(), $endDate->toDateString()]);

            if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
                $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
                $query->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
            }
            elseif (!empty($unitUsahaId)) {
                $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
            }

            return $query->select(
                'akuns.nama_akun',
                DB::raw('SUM(detail_jurnals.debit) as total_debit'),
                DB::raw('SUM(detail_jurnals.kredit) as total_kredit')
            )
            ->groupBy('akuns.akun_id', 'akuns.nama_akun')
            ->get();
        };

        // ... (Logika perhitungan Laba Rugi tetap sama) ...
        $pendapatanResults = $calculateSaldoes($pendapatanTipes);
        $pendapatans = [];
        $totalPendapatan = 0;
        foreach ($pendapatanResults as $result) {
            $saldo = $result->total_kredit - $result->total_debit;
            if ($saldo > 0) {
                $pendapatans[] = ['nama_akun' => $result->nama_akun, 'total' => $saldo];
                $totalPendapatan += $saldo;
            }
        }
        $hppResults = $calculateSaldoes($hppTipes);
        $hpps = [];
        $totalHpp = 0;
        foreach ($hppResults as $result) {
            $saldo = $result->total_debit - $result->total_kredit;
            if ($saldo > 0) {
                $hpps[] = ['nama_akun' => $result->nama_akun, 'total' => $saldo];
                $totalHpp += $saldo;
            }
        }
        $labaKotor = $totalPendapatan - $totalHpp;
        $bebanResults = $calculateSaldoes($bebanTipes);
        $bebans = [];
        $totalBeban = 0;
        foreach ($bebanResults as $result) {
            $saldo = $result->total_debit - $result->total_kredit;
            if ($saldo > 0) {
                $bebans[] = ['nama_akun' => $result->nama_akun, 'total' => $saldo];
                $totalBeban += $saldo;
            }
        }
        $labaOperasional = $labaKotor - $totalBeban;
        $pendapatanBebanLainResults = $calculateSaldoes($pendapatanBebanLainTipes);
        $pendapatanLains = [];
        $totalPendapatanLain = 0;
        $bebanLains = [];
        $totalBebanLain = 0;
        foreach ($pendapatanBebanLainResults as $result) {
            $namaAkun = strtolower($result->nama_akun);
            $saldo = $result->total_kredit - $result->total_debit;
            if (str_contains($namaAkun, 'beban') || str_contains($namaAkun, 'kerugian')) {
                $saldo = $result->total_debit - $result->total_kredit;
                if ($saldo > 0) {
                    $bebanLains[] = ['nama_akun' => $result->nama_akun, 'total' => $saldo];
                    $totalBebanLain += $saldo;
                }
            } else {
                if ($saldo > 0) {
                    $pendapatanLains[] = ['nama_akun' => $result->nama_akun, 'total' => $saldo];
                    $totalPendapatanLain += $saldo;
                }
            }
        }
        $labaRugiBersih = $labaOperasional + $totalPendapatanLain - $totalBebanLain;


        // --- LOGIKA BARU PENANDATANGAN DINAMIS ---
        $penandaTangan1 = []; // Penyetuju
        $penandaTangan2 = []; // Penanggung Jawab

        if ($unitUsahaId) {
            // Laporan untuk Unit Usaha Spesifik
            $unitUsaha = UnitUsaha::with('penanggungJawab.anggota')->find($unitUsahaId);
            $manajer = $unitUsaha ? $unitUsaha->penanggungJawab : null;
            
            $penandaTangan1 = [
                'jabatan' => 'Manajer Unit Usaha',
                'nama'    => $manajer && $manajer->anggota ? $manajer->anggota->nama_lengkap : '....................'
            ];

            $adminUnit = User::role('admin_unit_usaha')
                            ->whereHas('unitUsahas', function ($query) use ($unitUsahaId) {
                                $query->where('unit_usahas.unit_usaha_id', $unitUsahaId);
                            })
                            ->with('anggota')->first();
            
            $penandaTangan2 = [
                'jabatan' => 'Admin Unit Usaha',
                'nama'    => $adminUnit && $adminUnit->anggota ? $adminUnit->anggota->nama_lengkap : '....................'
            ];
        } else {
            // Laporan Keseluruhan / Pusat
            $direktur = User::role('direktur_bumdes')->with('anggota')->first();
            $penandaTangan1 = [
                'jabatan' => 'Direktur',
                'nama'    => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'
            ];

            $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
            $penandaTangan2 = [
                'jabatan' => 'Bendahara',
                'nama'    => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'
            ];
        }
        // --- AKHIR LOGIKA BARU ---

        return view('laporan.laba_rugi.show', compact(
            'bumdes', 'startDate', 'endDate',
            'pendapatans', 'totalPendapatan',
            'hpps', 'totalHpp', 'labaKotor',
            'bebans', 'totalBeban', 'labaOperasional',
            'pendapatanLains', 'totalPendapatanLain',
            'bebanLains', 'totalBebanLain', 'labaRugiBersih',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}

