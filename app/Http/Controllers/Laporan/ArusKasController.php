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
     * Memproses filter dan menampilkan laporan dengan format baru.
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

        $baseQuery = JurnalUmum::with('detailJurnals.akun')
            ->where('status', 'disetujui')
            ->whereHas('detailJurnals.akun', function ($q) {
                $q->where('kode_akun', 'like', '1.1.01.%'); // Hanya jurnal yg melibatkan KAS
            })
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate]);

        // Terapkan filter hak akses dan unit usaha
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
        
        // Inisialisasi semua kategori arus kas
        $kas = [
            'operasi_masuk' => [
                'penjualan_jasa' => 0,
                'penjualan_barang_dagangan' => 0,
                'penjualan_barang_jadi' => 0,
                'bunga_dividen' => 0,
                'bunga_bank' => 0,
                'angsuran_pinjaman' => 0,
            ],
            'operasi_keluar' => [
                'pembayaran_pemasok' => 0,
                'pembayaran_gaji' => 0,
                'pembayaran_pajak' => 0,
                'pembayaran_bunga' => 0,
                'pemberian_pinjaman' => 0,
                'pembayaran_biaya_produksi' => 0,
                'pembayaran_beban_lain' => 0,
            ],
            'investasi_masuk' => [
                'penjualan_aset_tetap' => 0,
                'penjualan_investasi' => 0,
            ],
            'investasi_keluar' => [
                'pembelian_aset_tetap' => 0,
                'pembelian_investasi' => 0,
            ],
            'pendanaan_masuk' => [
                'penyertaan_modal_desa' => 0,
                'penyertaan_modal_masyarakat' => 0,
                'donasi' => 0,
                'utang_jangka_panjang' => 0,
            ],
            'pendanaan_keluar' => [
                'bagi_hasil_desa' => 0,
                'bagi_hasil_masyarakat' => 0,
                'pembayaran_utang_jangka_panjang' => 0,
            ],
        ];

        foreach ($jurnals as $jurnal) {
            $pergerakanKas = $jurnal->detailJurnals->where('akun.tipe_akun', 'Aset')->sum(fn($i) => $i->debit - $i->kredit);
            $akunLawanList = $jurnal->detailJurnals->where('akun.tipe_akun', '!=', 'Aset');

            foreach ($akunLawanList as $detailLawan) {
                $kode = $detailLawan->akun->kode_akun;
                $tipe = $detailLawan->akun->tipe_akun;
                
                // --- KLASIFIKASI ARUS KAS OPERASI ---
                if (str_starts_with($kode, '4.1.')) $kas['operasi_masuk']['penjualan_jasa'] += $pergerakanKas;
                if (str_starts_with($kode, '4.2.')) $kas['operasi_masuk']['penjualan_barang_dagangan'] += $pergerakanKas;
                if (str_starts_with($kode, '4.3.')) $kas['operasi_masuk']['penjualan_barang_jadi'] += $pergerakanKas;
                if (str_starts_with($kode, '7.1.01.')) $kas['operasi_masuk']['bunga_bank'] += $pergerakanKas;
                
                if (str_starts_with($kode, '2.1.01.')) $kas['operasi_keluar']['pembayaran_pemasok'] += $pergerakanKas;
                if (str_starts_with($kode, '6.1.01.') || str_starts_with($kode, '6.2.01.') || str_starts_with($kode, '6.3.01.')) $kas['operasi_keluar']['pembayaran_gaji'] += $pergerakanKas;
                if (str_starts_with($kode, '5.')) $kas['operasi_keluar']['pembayaran_biaya_produksi'] += $pergerakanKas;
                if ($tipe === 'Beban') $kas['operasi_keluar']['pembayaran_beban_lain'] += $pergerakanKas;


                // --- KLASIFIKASI ARUS KAS INVESTASI ---
                if (str_starts_with($kode, '1.3.')) $kas['investasi_keluar']['pembelian_aset_tetap'] += $pergerakanKas;
                if (str_starts_with($kode, '1.2.')) $kas['investasi_keluar']['pembelian_investasi'] += $pergerakanKas;

                // --- KLASIFIKASI ARUS KAS PENDANAAN ---
                if (str_starts_with($kode, '3.1.01.')) $kas['pendanaan_masuk']['penyertaan_modal_desa'] += $pergerakanKas;
                if (str_starts_with($kode, '3.1.02.')) $kas['pendanaan_masuk']['penyertaan_modal_masyarakat'] += $pergerakanKas;
                if (str_starts_with($kode, '3.4.')) $kas['pendanaan_masuk']['donasi'] += $pergerakanKas;
                if (str_starts_with($kode, '3.2.01.')) $kas['pendanaan_keluar']['bagi_hasil_desa'] += $pergerakanKas;
                if (str_starts_with($kode, '3.2.02.')) $kas['pendanaan_keluar']['bagi_hasil_masyarakat'] += $pergerakanKas;
            }
        }

        // Hitung Saldo Awal Kas
        $saldoKasAwalQuery = DB::table('detail_jurnals')
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
            ->where('jurnal_umums.status', 'disetujui')
            ->where('akuns.kode_akun', 'like', '1.1.01.%')
            ->where('jurnal_umums.tanggal_transaksi', '<', $startDate->toDateString());

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

        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];

        return view('laporan.arus_kas.show', compact(
            'bumdes', 'startDate', 'endDate', 'tanggalCetak',
            'kas', 'saldoKasAwal',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}

