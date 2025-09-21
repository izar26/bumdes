<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bungdes;
use App\Models\UnitUsaha;
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
     * Memproses filter dan menampilkan laporan dengan format detail seperti Excel.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'end_date' => 'required|date',
            'unit_usaha_id' => 'nullable|string',
            'tanggal_cetak' => 'nullable|date',
        ]);

        $user = Auth::user();
        $endDate = Carbon::parse($request->end_date);
        $unitUsahaId = $request->unit_usaha_id;
        $bumdes = Bungdes::first();
        $tanggalCetak = $request->tanggal_cetak ? Carbon::parse($request->tanggal_cetak) : now();

        // Helper function untuk mengambil saldo berdasarkan pola kode akun
        $getSaldoByPattern = function(array $patterns, $saldoNormal = 'debit') use ($user, $endDate, $unitUsahaId) {
            $query = DB::table('detail_jurnals')
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->where('jurnal_umums.status', 'disetujui')
                ->where('jurnal_umums.tanggal_transaksi', '<=', $endDate->toDateString());

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
                DB::raw('COALESCE(SUM(detail_jurnals.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(detail_jurnals.kredit), 0) as total_kredit')
            )->first();
            
            if ($saldoNormal === 'kredit') {
                return $result->total_kredit - $result->total_debit;
            }
            return $result->total_debit - $result->total_kredit;
        };
        
        // --- ASET ---
        // Aset Lancar (1.1.%)
        $kas = $getSaldoByPattern(['1.1.01.%']);
        $setara_kas = $getSaldoByPattern(['1.1.02.%']); // Bank
        $piutang = $getSaldoByPattern(['1.1.03.%']);
        $penyisihan_piutang = $getSaldoByPattern(['1.1.04.%'], 'kredit'); // Saldo normal kredit
        $persediaan = $getSaldoByPattern(['1.1.05.%']);
        $perlengkapan = $getSaldoByPattern(['1.1.06.%']);
        $pembayaran_dimuka = $getSaldoByPattern(['1.1.07.%']); // Sewa, asuransi dibayar dimuka
        $aset_lancar_lainnya = $getSaldoByPattern(['1.1.99.%']);
        $total_aset_lancar = $kas + $setara_kas + $piutang + $penyisihan_piutang + $persediaan + $perlengkapan + $pembayaran_dimuka + $aset_lancar_lainnya;
        
        // Investasi (1.2.%)
        $investasi = $getSaldoByPattern(['1.2.%']);
        
        // Aset Tetap (1.3.%)
        $tanah = $getSaldoByPattern(['1.3.01.%']);
        $kendaraan = $getSaldoByPattern(['1.3.02.%']);
        $peralatan = $getSaldoByPattern(['1.3.03.%']);
        $meubelair = $getSaldoByPattern(['1.3.04.%']);
        $gedung = $getSaldoByPattern(['1.3.05.%']);
        $akumulasi_penyusutan = $getSaldoByPattern(['1.3.99.%'], 'kredit'); // Saldo normal kredit
        $total_aset_tetap = $tanah + $kendaraan + $peralatan + $meubelair + $gedung + $akumulasi_penyusutan;

        $total_aset = $total_aset_lancar + $investasi + $total_aset_tetap;

        // --- KEWAJIBAN ---
        // Kewajiban Jangka Pendek (2.1.%)
        $utang_usaha = $getSaldoByPattern(['2.1.01.%'], 'kredit');
        $utang_pajak = $getSaldoByPattern(['2.1.02.%'], 'kredit');
        $utang_gaji = $getSaldoByPattern(['2.1.03.%'], 'kredit');
        $utang_pendek_lainnya = $getSaldoByPattern(['2.1.99.%'], 'kredit');
        $total_kewajiban_pendek = $utang_usaha + $utang_pajak + $utang_gaji + $utang_pendek_lainnya;
        
        // Kewajiban Jangka Panjang (2.2.%)
        $utang_panjang = $getSaldoByPattern(['2.2.%'], 'kredit');
        $total_kewajiban = $total_kewajiban_pendek + $utang_panjang;

        // --- EKUITAS ---
        // Modal Awal (3.%)
        $modal = $getSaldoByPattern(['3.%'], 'kredit');

        // Laba (Rugi) Tahun Berjalan
        $startOfYear = $endDate->copy()->startOfYear();
        $total_pendapatan = $getSaldoByPattern(['4.%', '7.%'], 'kredit');
        $total_beban_hpp = $getSaldoByPattern(['5.%', '6.%', '8.%', '9.%'], 'debit');
        $laba_tahun_berjalan = $total_pendapatan - $total_beban_hpp;
        
        $ekuitas_akhir = $modal + $laba_tahun_berjalan;
        $total_kewajiban_ekuitas = $total_kewajiban + $ekuitas_akhir;

        // Data Penanda Tangan
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];
        
        return view('laporan.neraca.show', compact(
            'bumdes', 'endDate', 'tanggalCetak', 'penandaTangan1', 'penandaTangan2',
            'kas', 'setara_kas', 'piutang', 'penyisihan_piutang', 'persediaan', 'perlengkapan', 'pembayaran_dimuka', 'aset_lancar_lainnya', 'total_aset_lancar',
            'investasi',
            'tanah', 'kendaraan', 'peralatan', 'meubelair', 'gedung', 'akumulasi_penyusutan', 'total_aset_tetap',
            'total_aset',
            'utang_usaha', 'utang_pajak', 'utang_gaji', 'utang_pendek_lainnya', 'total_kewajiban_pendek',
            'utang_panjang', 'total_kewajiban',
            'ekuitas_akhir', 'total_kewajiban_ekuitas'
        ));
    }
}
