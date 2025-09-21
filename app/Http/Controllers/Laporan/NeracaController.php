<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\UnitUsaha;
use App\Models\Bungdes;
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
     * Memproses filter dan menampilkan laporan dengan format detail.
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
        $startOfYear = $endDate->copy()->startOfYear();

        // Helper function untuk membangun query dasar yang sudah difilter
        $baseQueryBuilder = function($date) use ($user, $unitUsahaId) {
            $query = DB::table('detail_jurnals')
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->where('jurnal_umums.status', 'disetujui')
                ->where('jurnal_umums.tanggal_transaksi', '<=', $date->toDateString());

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
        
        // Helper function untuk menghitung saldo berdasarkan pola kode akun
        $getSaldoByPattern = function($pattern, $normalDebit = true) use ($baseQueryBuilder, $endDate) {
            $query = $baseQueryBuilder($endDate)->where('akuns.kode_akun', 'like', $pattern);
            $saldo = $normalDebit
                ? $query->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'))
                : $query->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));
            return $saldo;
        };

        // 1. Hitung Laba/Rugi Tahun Berjalan
        $pendapatanQuery = $baseQueryBuilder($endDate)->whereBetween('jurnal_umums.tanggal_transaksi', [$startOfYear, $endDate]);
        $bebanQuery = clone $pendapatanQuery;

        $totalPendapatan = $pendapatanQuery->whereIn('akuns.tipe_akun', ['Pendapatan', 'Pendapatan & Beban Lainnya'])
            ->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));

        $totalBeban = $bebanQuery->whereIn('akuns.tipe_akun', ['Beban', 'HPP'])
            ->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));

        $labaRugiBerjalan = $totalPendapatan - $totalBeban;

        // 2. Hitung Saldo Laba Ditahan (dari tahun-tahun sebelumnya)
        $labaDitahanAwal = $baseQueryBuilder($startOfYear->copy()->subDay())
            ->whereIn('akuns.tipe_akun', ['Pendapatan', 'Pendapatan & Beban Lainnya', 'Beban', 'HPP'])
            ->sum(DB::raw('CASE WHEN akuns.tipe_akun IN ("Pendapatan", "Pendapatan & Beban Lainnya") THEN detail_jurnals.kredit - detail_jurnals.debit ELSE detail_jurnals.debit - detail_jurnals.kredit END'));

        // 3. Kalkulasi setiap pos di Neraca
        $aset = [
            'kas' => $getSaldoByPattern('1.1.01.%'),
            'setara_kas' => $getSaldoByPattern('1.1.02.%'),
            'piutang' => $getSaldoByPattern('1.1.03.%'),
            'penyisihan_piutang' => $getSaldoByPattern('1.1.04.%', false), // Kontra-aset
            'persediaan' => $getSaldoByPattern('1.1.05.%'),
            'perlengkapan' => $getSaldoByPattern('1.1.06.%'),
            'pembayaran_dimuka' => $getSaldoByPattern('1.1.07.%'),
            'aset_lancar_lainnya' => $getSaldoByPattern('1.1.98.%'),
            'investasi' => $getSaldoByPattern('1.2.01.%'),
            'tanah' => $getSaldoByPattern('1.3.01.%'),
            'kendaraan' => $getSaldoByPattern('1.3.02.%'),
            'peralatan' => $getSaldoByPattern('1.3.03.%'),
            'meubelair' => $getSaldoByPattern('1.3.04.%'),
            'gedung' => $getSaldoByPattern('1.3.05.%'),
            'akumulasi_penyusutan' => $getSaldoByPattern('1.3.07.%', false), // Kontra-aset
        ];

        $kewajiban = [
            'utang_usaha' => $getSaldoByPattern('2.1.01.%', false),
            'utang_pajak' => $getSaldoByPattern('2.1.02.%', false),
            'utang_gaji' => $getSaldoByPattern('2.1.03.%', false),
            'utang_pendek_lainnya' => $getSaldoByPattern('2.1.99.%', false),
            'utang_panjang' => $getSaldoByPattern('2.2.%', false),
        ];

        $ekuitas = [
            'modal_disetor' => $getSaldoByPattern('3.1.%', false),
            'saldo_laba' => $labaDitahanAwal + $labaRugiBerjalan,
        ];
        
        $totals = [
            'total_aset_lancar' => $aset['kas'] + $aset['setara_kas'] + $aset['piutang'] + $aset['penyisihan_piutang'] + $aset['persediaan'] + $aset['perlengkapan'] + $aset['pembayaran_dimuka'] + $aset['aset_lancar_lainnya'],
            'total_aset_tetap' => $aset['tanah'] + $aset['kendaraan'] + $aset['peralatan'] + $aset['meubelair'] + $aset['gedung'] + $aset['akumulasi_penyusutan'],
            'total_kewajiban_pendek' => $kewajiban['utang_usaha'] + $kewajiban['utang_pajak'] + $kewajiban['utang_gaji'] + $kewajiban['utang_pendek_lainnya'],
        ];
        $totals['total_aset'] = $totals['total_aset_lancar'] + $aset['investasi'] + $totals['total_aset_tetap'];
        $totals['total_kewajiban'] = $totals['total_kewajiban_pendek'] + $kewajiban['utang_panjang'];
        $totals['ekuitas_akhir'] = $ekuitas['modal_disetor'] + $ekuitas['saldo_laba'];
        $totals['total_kewajiban_ekuitas'] = $totals['total_kewajiban'] + $totals['ekuitas_akhir'];

        $data = array_merge($aset, $kewajiban, $ekuitas, $totals);

        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];
        
        return view('laporan.neraca.show', compact('bumdes', 'endDate', 'tanggalCetak', 'data', 'penandaTangan1', 'penandaTangan2'));
    }
}

