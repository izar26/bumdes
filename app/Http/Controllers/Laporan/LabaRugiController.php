<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bungdes;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LabaRugiController extends Controller
{
    /**
     * Menampilkan halaman form filter.
     */
    public function index()
    {
        $user = Auth::user();
        $unitUsahas = null;

        if ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')
                ->orderBy('nama_unit')
                ->get();
        } elseif ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $unitUsahas = UnitUsaha::whereIn('unit_usaha_id', $unitUsahaIds)
                ->where('status_operasi', 'Aktif')
                ->orderBy('nama_unit')
                ->get();
        } else {
            $unitUsahas = collect();
        }

        return view('laporan.laba_rugi.index', compact('unitUsahas', 'user'));
    }

    /**
     * Memproses filter dan menampilkan laporan dengan format detail.
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

        // Helper function untuk mengambil saldo berdasarkan pola kode akun
        $calculateSaldoes = function (array $patterns, $saldoNormal = 'kredit') use ($user, $startDate, $endDate, $unitUsahaId) {
            $query = DetailJurnal::query()
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->where('jurnal_umums.status', 'disetujui')
                ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate->toDateString(), $endDate->toDateString()]);
            
            $query->where(function($q) use ($patterns) {
                foreach ($patterns as $pattern) {
                    $q->orWhere('akuns.kode_akun', 'like', $pattern);
                }
            });

            // Filter hak akses & unit usaha
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

            $result = $query->select(
                DB::raw('SUM(detail_jurnals.debit) as total_debit'),
                DB::raw('SUM(detail_jurnals.kredit) as total_kredit')
            )->first();
            
            if ($saldoNormal === 'kredit') {
                return ($result->total_kredit ?? 0) - ($result->total_debit ?? 0);
            } else { // debit
                return ($result->total_debit ?? 0) - ($result->total_kredit ?? 0);
            }
        };

        // 1. PENDAPATAN USAHA
        $pendapatan_jasa = $calculateSaldoes(['4.1.%'], 'kredit');
        $pendapatan_brg_dagang = $calculateSaldoes(['4.2.%'], 'kredit');
        $retur_diskon_dagang = $calculateSaldoes(['4.2.98.%', '4.2.99.%'], 'debit'); // Asumsi retur & diskon
        $penjualan_dagang_bersih = $pendapatan_brg_dagang - $retur_diskon_dagang;
        
        $pendapatan_brg_jadi = $calculateSaldoes(['4.3.%'], 'kredit');
        $retur_diskon_jadi = $calculateSaldoes(['4.3.98.%', '4.3.99.%'], 'debit'); // Asumsi retur & diskon
        $penjualan_jadi_bersih = $pendapatan_brg_jadi - $retur_diskon_jadi;
        
        $total_pendapatan = $pendapatan_jasa + $penjualan_dagang_bersih + $penjualan_jadi_bersih;

        // 2. HARGA POKOK PENJUALAN
        $hpp_dagang = $calculateSaldoes(['5.1.%'], 'debit');
        $hpp_jadi = $calculateSaldoes(['5.2.%'], 'debit');
        $total_hpp = $hpp_dagang + $hpp_jadi;

        // 3. LABA KOTOR
        $laba_kotor = $total_pendapatan - $total_hpp;

        // 4. BEBAN USAHA
        // Beban Administrasi & Umum (Asumsi 6.1.%)
        $beban_adm_pegawai = $calculateSaldoes(['6.1.01.%'], 'debit');
        $beban_adm_perlengkapan = $calculateSaldoes(['6.1.02.%'], 'debit');
        $beban_adm_pemeliharaan = $calculateSaldoes(['6.1.03.%'], 'debit');
        $beban_adm_utilitas = $calculateSaldoes(['6.1.04.%'], 'debit');
        $beban_adm_sewa = $calculateSaldoes(['6.1.05.%'], 'debit');
        $beban_adm_kebersihan = $calculateSaldoes(['6.1.06.%'], 'debit');
        $beban_adm_penyusutan = $calculateSaldoes(['6.1.07.%'], 'debit');
        $beban_adm_lain = $calculateSaldoes(['6.1.99.%'], 'debit');
        $total_beban_adm = $beban_adm_pegawai + $beban_adm_perlengkapan + $beban_adm_pemeliharaan + $beban_adm_utilitas + $beban_adm_sewa + $beban_adm_kebersihan + $beban_adm_penyusutan + $beban_adm_lain;
        
        // Beban Operasional (Asumsi 6.2.%)
        $beban_ops_pegawai = $calculateSaldoes(['6.2.01.%'], 'debit');
        $beban_ops_pemeliharaan = $calculateSaldoes(['6.2.02.%'], 'debit');
        $beban_ops_lain = $calculateSaldoes(['6.2.99.%'], 'debit');
        $total_beban_ops = $beban_ops_pegawai + $beban_ops_pemeliharaan + $beban_ops_lain;

        // Beban Pemasaran (Asumsi 6.3.%)
        $beban_pemasaran_pegawai = $calculateSaldoes(['6.3.01.%'], 'debit');
        $beban_pemasaran_iklan = $calculateSaldoes(['6.3.02.%'], 'debit');
        $beban_pemasaran_lain = $calculateSaldoes(['6.3.99.%'], 'debit');
        $total_beban_pemasaran = $beban_pemasaran_pegawai + $beban_pemasaran_iklan + $beban_pemasaran_lain;
        
        $total_beban_usaha = $total_beban_adm + $total_beban_ops + $total_beban_pemasaran;
        
        // 5. LABA OPERASI
        $laba_operasi = $laba_kotor - $total_beban_usaha;
        
        // 6. PENDAPATAN & BEBAN LAIN-LAIN
        $pendapatan_lain = $calculateSaldoes(['7.%'], 'kredit');
        $beban_lain = $calculateSaldoes(['8.%'], 'debit');
        
        // 7. BEBAN PAJAK (Asumsi 9.%)
        $beban_pajak = $calculateSaldoes(['9.%'], 'debit');

        $laba_sebelum_bagi_hasil = $laba_operasi + $pendapatan_lain - $beban_lain - $beban_pajak;

        // 8. BAGI HASIL (Asumsi dari akun ekuitas 3.2.%)
        $bagi_hasil_desa = $calculateSaldoes(['3.2.01.%'], 'debit');
        $bagi_hasil_masyarakat = $calculateSaldoes(['3.2.02.%'], 'debit');
        
        $laba_bersih_final = $laba_sebelum_bagi_hasil - $bagi_hasil_desa - $bagi_hasil_masyarakat;
        
        // Data Penanda Tangan
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];

        // Mengirim semua data yang sudah dihitung ke view
        return view('laporan.laba_rugi.show', compact(
            'bumdes', 'startDate', 'endDate', 'tanggalCetak', 'penandaTangan1', 'penandaTangan2',
            'pendapatan_jasa', 'penjualan_dagang_bersih', 'penjualan_jadi_bersih', 'total_pendapatan',
            'total_hpp', 'laba_kotor',
            'beban_adm_pegawai', 'beban_adm_perlengkapan', 'beban_adm_pemeliharaan', 'beban_adm_utilitas', 'beban_adm_sewa', 'beban_adm_kebersihan', 'beban_adm_penyusutan', 'beban_adm_lain', 'total_beban_adm',
            'beban_ops_pegawai', 'beban_ops_pemeliharaan', 'beban_ops_lain', 'total_beban_ops',
            'beban_pemasaran_pegawai', 'beban_pemasaran_iklan', 'beban_pemasaran_lain', 'total_beban_pemasaran',
            'total_beban_usaha', 'laba_operasi',
            'pendapatan_lain', 'beban_lain', 'beban_pajak',
            'laba_sebelum_bagi_hasil', 'bagi_hasil_desa', 'bagi_hasil_masyarakat', 'laba_bersih_final'
        ));
    }
}
