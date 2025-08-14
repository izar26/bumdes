<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use App\Models\Bungdes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Pastikan DB sudah di-import
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

        if ($user->hasRole(['bendahara_bumdes', 'admin_bumdes'])) {
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

        // Fungsi Closure untuk mengambil saldo (menghindari duplikasi kode)
        $calculateSaldoes = function($tipeAkun) use ($user, $startDate, $endDate, $unitUsahaId) {

            $query = DetailJurnal::query()
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->where('akuns.tipe_akun', $tipeAkun)
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
            elseif ($user->hasRole(['bendahara_bumdes', 'admin_bumdes'])) {
                if (!empty($unitUsahaId)) {
                    $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
                }
            }

            // Satu query efisien ke database
            return $query->select(
                            'akuns.nama_akun',
                            DB::raw('SUM(detail_jurnals.debit) as total_debit'),
                            DB::raw('SUM(detail_jurnals.kredit) as total_kredit')
                        )
                        ->groupBy('akuns.akun_id', 'akuns.nama_akun')
                        ->get();
        };

        // Menghitung Pendapatan
        $pendapatanResults = $calculateSaldoes('Pendapatan');
        $pendapatans = [];
        $totalPendapatan = 0;
        foreach ($pendapatanResults as $result) {
            $saldo = $result->total_kredit - $result->total_debit;
            if ($saldo > 0) {
                $pendapatans[] = ['nama_akun' => $result->nama_akun, 'total' => $saldo];
                $totalPendapatan += $saldo;
            }
        }

        // Menghitung Beban
        $bebanResults = $calculateSaldoes('Beban');
        $bebans = [];
        $totalBeban = 0;
        foreach ($bebanResults as $result) {
            $saldo = $result->total_debit - $result->total_kredit;
            if ($saldo > 0) {
                $bebans[] = ['nama_akun' => $result->nama_akun, 'total' => $saldo];
                $totalBeban += $saldo;
            }
        }

        $labaRugi = $totalPendapatan - $totalBeban;

        // --- BLOK BARU: MENYIAPKAN DATA UNTUK TANDA TANGAN ---
        // Logika ini bisa Anda kembangkan lebih lanjut, misalnya mengambil dari tabel 'pengurus'
        // atau dari data user yang memiliki peran tertentu.
        $penandaTangan1 = [
            'jabatan' => 'Direktur',
            'nama'    => 'Nama Direktur Anda'
        ];
        $penandaTangan2 = [
            'jabatan' => 'Bendahara',
            'nama'    => 'Nama Bendahara Anda'
        ];
        // --- AKHIR BLOK BARU ---


        // --- PERUBAHAN: KIRIM VARIABEL BARU KE VIEW ---
        return view('laporan.laba_rugi.show', compact(
            'bumdes',
            'startDate',
            'endDate',
            'pendapatans',
            'totalPendapatan',
            'bebans',
            'totalBeban',
            'labaRugi',
            'penandaTangan1',
            'penandaTangan2'
                ));
    }
}
