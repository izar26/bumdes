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

class LabaRugiController extends Controller
{
    /**
     * Menampilkan halaman form filter laporan Laba Rugi.
     */
    public function index()
    {
        $user = Auth::user();
        $unitUsahas = null;

        if ($user->hasRole(['bendahara_bumdes', 'sekretaris_bumdes'])) {
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')
                                    ->orderBy('nama_unit')
                                    ->get();
        }
        elseif ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $unitUsahas = $user->unitUsahas()
                               ->where('status_operasi', 'Aktif')
                               ->select('unit_usahas.*')
                               ->orderBy('nama_unit')
                               ->get();
        }
        else {
            $unitUsahas = collect();
        }

        return view('laporan.laba_rugi.index', compact('unitUsahas'));
    }

    /**
     * Memproses filter dan menampilkan laporan Laba Rugi. (VERSI FINAL)
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

        // --- PERUBAHAN: DAFTAR LENGKAP TIPE AKUN UNTUK LAPORAN LABA RUGI ---
        $pendapatanTipes = ['Pendapatan'];
        $hppTipes = ['HPP'];
        $bebanTipes = ['Beban'];
        $pendapatanBebanLainTipes = ['Pendapatan & Beban Lainnya'];
        // --- AKHIR PERUBAHAN ---

        // Fungsi Closure untuk mengambil saldo (menghindari duplikasi kode)
        $calculateSaldoes = function($tipeAkunArray) use ($user, $startDate, $endDate, $unitUsahaId) {
            $query = DetailJurnal::query()
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->whereIn('akuns.tipe_akun', $tipeAkunArray)
                ->where('jurnal_umums.status', 'disetujui')
                ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate]);

            // Blok logika hak akses (RBAC) yang sudah aman
            if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
                $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
                $query->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
                if (!empty($unitUsahaId)) {
                    $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
                }
            }
            elseif ($user->hasRole(['bendahara_bumdes', 'sekretaris_bumdes'])) {
                if (!empty($unitUsahaId)) {
                    $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
                }
            }

            return $query->select(
                'akuns.nama_akun',
                DB::raw('SUM(detail_jurnals.debit) as total_debit'),
                DB::raw('SUM(detail_jurnals.kredit) as total_kredit')
            )
            ->groupBy('akuns.akun_id', 'akuns.nama_akun')
            ->get();
        };

        // Menghitung Pendapatan
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

        // Menghitung Harga Pokok Penjualan (HPP)
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

        // Laba Kotor
        $labaKotor = $totalPendapatan - $totalHpp;

        // Menghitung Beban Operasional
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

        // Laba/Rugi Operasional
        $labaOperasional = $labaKotor - $totalBeban;

        // Menghitung Pendapatan dan Beban Lain-lain
        $pendapatanBebanLainResults = $calculateSaldoes($pendapatanBebanLainTipes);
        $pendapatanLains = [];
        $totalPendapatanLain = 0;
        $bebanLains = [];
        $totalBebanLain = 0;

        foreach ($pendapatanBebanLainResults as $result) {
            $namaAkun = strtolower($result->nama_akun);
            $saldo = $result->total_kredit - $result->total_debit; // Default untuk pendapatan

            // Logika untuk membedakan Pendapatan Lain dan Beban Lain
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

        // Laba/Rugi Bersih Sebelum Pajak
        $labaRugiBersih = $labaOperasional + $totalPendapatanLain - $totalBebanLain;

        // Menyiapkan data untuk tanda tangan
        $penandaTangan1 = [
            'jabatan' => 'Direktur',
            'nama'    => 'Nama Direktur Anda'
        ];
        $penandaTangan2 = [
            'jabatan' => 'Bendahara',
            'nama'    => 'Nama Bendahara Anda'
        ];

        // --- PERUBAHAN: KIRIM VARIABEL BARU KE VIEW ---
        return view('laporan.laba_rugi.show', compact(
            'bumdes',
            'startDate',
            'endDate',
            'pendapatans',
            'totalPendapatan',
            'hpps',
            'totalHpp',
            'labaKotor',
            'bebans',
            'totalBeban',
            'labaOperasional',
            'pendapatanLains',
            'totalPendapatanLain',
            'bebanLains',
            'totalBebanLain',
            'labaRugiBersih',
            'penandaTangan1',
            'penandaTangan2'
        ));
    }
}
