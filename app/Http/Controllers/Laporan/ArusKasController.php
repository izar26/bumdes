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
     * Memproses filter dan menampilkan laporan dengan logika terpusat.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'unit_usaha_id' => 'nullable|string',
            'tanggal_cetak' => 'nullable|date',
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $unitUsahaId = $request->unit_usaha_id;
        $bumdes = Bungdes::first();
        $tanggalCetak = $request->tanggal_cetak ? Carbon::parse($request->tanggal_cetak) : now();

        $polaAkunKasBank = ['1.1.%'];

        $baseQueryBuilder = function() use ($startDate, $endDate, $user, $unitUsahaId) {
            $query = DB::table('detail_jurnals')
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->where('jurnal_umums.status', 'disetujui')
                ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate]);

            if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
                $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
                $query->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
            } elseif ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
                if ($unitUsahaId === 'pusat') {
                    $query->whereNull('jurnal_umums.unit_usaha_id');
                } elseif (!empty($unitUsahaId)) {
                    $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
                }
            }
            return $query;
        };

        $calculateKasMovement = function($kodeAkunPattern) use ($baseQueryBuilder, $polaAkunKasBank) {
            $jurnalIds = (clone $baseQueryBuilder())
                ->where('akuns.kode_akun', 'like', $kodeAkunPattern)
                ->pluck('jurnal_umums.jurnal_id')->unique();
            
            if ($jurnalIds->isEmpty()) {
                return 0;
            }

            $query = DB::table('detail_jurnals')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->whereIn('detail_jurnals.jurnal_id', $jurnalIds);

            $query->where(function($q) use ($polaAkunKasBank) {
                foreach ($polaAkunKasBank as $pola) {
                    $q->orWhere('akuns.kode_akun', 'like', $pola);
                }
            });

            return $query->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));
        };

        // --- PENYESUAIAN POLA AKUN SESUAI JURNAL CUSTOMER DIMULAI DI SINI ---
        
        // Pola spesifik yang digunakan di jurnal customer Anda
        $pola_gaji = '6.2.01.01';
        $pola_biaya_prod_1 = '1.1.05.02'; // Pembelian ke Persediaan Bahan Baku
        $pola_biaya_prod_2 = '6.2.99.01'; // Beban Pengairan
        $pola_biaya_prod_3 = '6.1.02.99'; // Pembelian Obat/Pupuk yg jadi beban

        // Hitung pergerakan kas untuk setiap kategori
        $kas = [
            'operasi_masuk' => [
                'penjualan_jasa' => $calculateKasMovement('4.1.%'),
                'penjualan_barang_dagangan' => $calculateKasMovement('4.2.%'),
                'penjualan_barang_jadi' => $calculateKasMovement('4.3.%'),
                'bunga_bank' => $calculateKasMovement('7.1.01.%'),
            ],
            'operasi_keluar' => [
                'pembayaran_gaji' => $calculateKasMovement($pola_gaji),
                
                // Menggabungkan beberapa pola untuk biaya produksi
                'pembayaran_biaya_produksi' => 
                    $calculateKasMovement($pola_biaya_prod_1) + 
                    $calculateKasMovement($pola_biaya_prod_2) + 
                    $calculateKasMovement($pola_biaya_prod_3),
                
                // Semua akun beban (awalan 6) DIKURANGI yang sudah dihitung di atas
                'pembayaran_beban_lain' => 
                    $calculateKasMovement('6.%') - $calculateKasMovement($pola_gaji) - $calculateKasMovement($pola_biaya_prod_2) - $calculateKasMovement($pola_biaya_prod_3),
            ],
            'investasi_masuk' => [
                 // Tambahkan jika ada penjualan aset
            ],
            'investasi_keluar' => [
                // Kode ini sudah benar, akan menangkap pembelian mesin & kipas angin (Rp 4.770.000)
                'pembelian_aset_tetap' => $calculateKasMovement('1.3.%'),
                'pembelian_investasi' => $calculateKasMovement('1.2.%'),
            ],
            'pendanaan_masuk' => [
                'penyertaan_modal_desa' => $calculateKasMovement('3.1.01.%'),
                'penyertaan_modal_masyarakat' => $calculateKasMovement('3.1.02.%'),
                'donasi' => $calculateKasMovement('3.4.%'),
            ],
            'pendanaan_keluar' => [
                'bagi_hasil_desa' => $calculateKasMovement('3.2.01.%'),
                'bagi_hasil_masyarakat' => $calculateKasMovement('3.2.02.%'),
            ],
        ];
        
        // --- AKHIR PENYESUAIAN ---


        // Kalkulasi dipusatkan di Controller
        $totals = [];
        $totals['operasi_masuk'] = array_sum($kas['operasi_masuk']);
        $totals['operasi_keluar'] = array_sum($kas['operasi_keluar']);
        $totals['investasi_masuk'] = array_sum($kas['investasi_masuk'] ?? []);
        $totals['investasi_keluar'] = array_sum($kas['investasi_keluar']);
        $totals['pendanaan_masuk'] = array_sum($kas['pendanaan_masuk']);
        $totals['pendanaan_keluar'] = array_sum($kas['pendanaan_keluar']);

        $arus_kas_bersih = [];
        $arus_kas_bersih['operasi'] = $totals['operasi_masuk'] + $totals['operasi_keluar'];
        $arus_kas_bersih['investasi'] = $totals['investasi_masuk'] + $totals['investasi_keluar'];
        $arus_kas_bersih['pendanaan'] = $totals['pendanaan_masuk'] + $totals['pendanaan_keluar'];

        $kenaikan_penurunan_kas = array_sum($arus_kas_bersih);
        
        // Hitung Saldo Awal Kas
        $saldoKasAwalQuery = DB::table('detail_jurnals')
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
            ->where('jurnal_umums.status', 'disetujui')
            ->where('jurnal_umums.tanggal_transaksi', '<', $startDate->toDateString());
        
        $saldoKasAwalQuery->where(function($q) use ($polaAkunKasBank) {
            foreach ($polaAkunKasBank as $pola) {
                $q->orWhere('akuns.kode_akun', 'like', $pola);
            }
        });

        if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $saldoKasAwalQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
        } elseif ($unitUsahaId) {
            if ($unitUsahaId === 'pusat') {
                $saldoKasAwalQuery->whereNull('jurnal_umums.unit_usaha_id');
            } else {
                $saldoKasAwalQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
            }
        }
        $saldoKasAwal = $saldoKasAwalQuery->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));

        $saldo_kas_akhir = $saldoKasAwal + $kenaikan_penurunan_kas;

        // Penanda tangan
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];
        
        return view('laporan.arus_kas.show', compact(
            'bumdes', 'startDate', 'endDate', 'tanggalCetak',
            'kas', 'totals', 'arus_kas_bersih',
            'saldoKasAwal', 'kenaikan_penurunan_kas', 'saldo_kas_akhir',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}