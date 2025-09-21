<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitUsaha;
use App\Models\Bungdes;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerubahanEkuitasController extends Controller
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
        
        return view('laporan.perubahan_ekuitas.index', compact('unitUsahas', 'user'));
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

        $baseQuery = function() use ($user, $unitUsahaId) {
            $query = DB::table('detail_jurnals')
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->where('jurnal_umums.status', 'disetujui');
            
            if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
                $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
                $query->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
            } elseif ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
                if ($unitUsahaId === 'pusat') $query->whereNull('jurnal_umums.unit_usaha_id');
                elseif (!empty($unitUsahaId)) $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
            }
            return $query;
        };
        
        // --- PERBAIKAN: Menambahkan 'detail_jurnals.' pada setiap kolom kredit dan debit ---
        
        $tanggalAwal = $startDate->copy()->subDay();

        // 1. HITUNG SALDO AWAL
        $modal_desa_awal = (clone $baseQuery())->where('akuns.kode_akun', 'like', '3.1.01.%')->whereDate('jurnal_umums.tanggal_transaksi', '<=', $tanggalAwal)->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));
        $modal_masyarakat_awal = (clone $baseQuery())->where('akuns.kode_akun', 'like', '3.1.02.%')->whereDate('jurnal_umums.tanggal_transaksi', '<=', $tanggalAwal)->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));
        $modal_donasi_awal = (clone $baseQuery())->where('akuns.kode_akun', 'like', '3.4.%')->whereDate('jurnal_umums.tanggal_transaksi', '<=', $tanggalAwal)->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));

        // Saldo Laba Awal
        $pendapatanAwal = (clone $baseQuery())->whereIn('akuns.tipe_akun', ['Pendapatan', 'Pendapatan & Beban Lainnya'])->whereDate('jurnal_umums.tanggal_transaksi', '<=', $tanggalAwal)->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));
        $bebanAwal = (clone $baseQuery())->whereIn('akuns.tipe_akun', ['Beban', 'HPP'])->whereDate('jurnal_umums.tanggal_transaksi', '<=', $tanggalAwal)->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));
        $saldo_laba_awal = $pendapatanAwal - $bebanAwal;

        // 2. HITUNG PERGERAKAN SELAMA PERIODE
        $queryPeriode = (clone $baseQuery())->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate]);

        $penambahan_modal_desa = (clone $queryPeriode)->where('akuns.kode_akun', 'like', '3.1.01.%')->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));
        $penambahan_modal_masyarakat = (clone $queryPeriode)->where('akuns.kode_akun', 'like', '3.1.02.%')->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));
        $penambahan_donasi = (clone $queryPeriode)->where('akuns.kode_akun', 'like', '3.4.%')->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));
        $bagi_hasil_desa = (clone $queryPeriode)->where('akuns.kode_akun', 'like', '3.2.01.%')->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));
        $bagi_hasil_masyarakat = (clone $queryPeriode)->where('akuns.kode_akun', 'like', '3.2.02.%')->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));

        // Laba Rugi Periode Berjalan
        $pendapatanPeriode = (clone $queryPeriode)->whereIn('akuns.tipe_akun', ['Pendapatan', 'Pendapatan & Beban Lainnya'])->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));
        $bebanPeriode = (clone $queryPeriode)->whereIn('akuns.tipe_akun', ['Beban', 'HPP'])->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));
        $laba_rugi_periode_berjalan = $pendapatanPeriode - $bebanPeriode;

        // 3. HITUNG SALDO AKHIR
        $penyertaan_modal_akhir = $modal_desa_awal + $modal_masyarakat_awal + $penambahan_modal_desa + $penambahan_modal_masyarakat;
        $saldo_laba_akhir = $saldo_laba_awal + $laba_rugi_periode_berjalan - $bagi_hasil_desa - $bagi_hasil_masyarakat;
        $modal_donasi_akhir = $modal_donasi_awal + $penambahan_donasi;
        $ekuitas_akhir = $penyertaan_modal_akhir + $saldo_laba_akhir + $modal_donasi_akhir;

        // Data Penanda Tangan
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];
        
        return view('laporan.perubahan_ekuitas.show', compact(
            'startDate', 'endDate', 'bumdes', 'tanggalCetak',
            'modal_desa_awal', 'modal_masyarakat_awal', 'saldo_laba_awal',
            'penambahan_modal_desa', 'penambahan_modal_masyarakat', 'laba_rugi_periode_berjalan',
            'bagi_hasil_desa', 'bagi_hasil_masyarakat', 'penambahan_donasi',
            'penyertaan_modal_akhir', 'saldo_laba_akhir', 'modal_donasi_akhir', 'ekuitas_akhir',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}

