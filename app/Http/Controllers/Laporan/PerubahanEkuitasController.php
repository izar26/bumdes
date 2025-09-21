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

        // Base query builder yang akan kita gunakan berulang kali
        $baseQuery = function() use ($user, $unitUsahaId) {
            $query = DB::table('detail_jurnals')
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->where('jurnal_umums.status', 'disetujui');
            
            // Filter unit usaha
            if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
                $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
                $query->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
            } elseif ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
                if ($unitUsahaId === 'pusat') $query->whereNull('jurnal_umums.unit_usaha_id');
                elseif (!empty($unitUsahaId)) $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
            }
            return $query;
        };
        
        // --- PERHITUNGAN DIMULAI ---
        
        $tanggalAwal = $startDate->copy()->subDay();

        // 1. HITUNG SALDO AWAL (akumulasi s.d. H-1 dari start_date)
        $modal_desa_awal = (clone $baseQuery())->where('akuns.kode_akun', 'like', '3.1.01.%')->whereDate('jurnal_umums.tanggal_transaksi', '<=', $tanggalAwal)->sum(DB::raw('kredit - debit'));
        $modal_masyarakat_awal = (clone $baseQuery())->where('akuns.kode_akun', 'like', '3.1.02.%')->whereDate('jurnal_umums.tanggal_transaksi', '<=', $tanggalAwal)->sum(DB::raw('kredit - debit'));
        $modal_donasi_awal = (clone $baseQuery())->where('akuns.kode_akun', 'like', '3.4.%')->whereDate('jurnal_umums.tanggal_transaksi', '<=', $tanggalAwal)->sum(DB::raw('kredit - debit'));

        // Saldo Laba Awal
        $pendapatanAwal = (clone $baseQuery())->whereIn('akuns.tipe_akun', ['Pendapatan', 'Pendapatan & Beban Lainnya'])->whereDate('jurnal_umums.tanggal_transaksi', '<=', $tanggalAwal)->sum(DB::raw('kredit - debit'));
        $bebanAwal = (clone $baseQuery())->whereIn('akuns.tipe_akun', ['Beban', 'HPP'])->whereDate('jurnal_umums.tanggal_transaksi', '<=', $tanggalAwal)->sum(DB::raw('debit - kredit'));
        $saldo_laba_awal = $pendapatanAwal - $bebanAwal;

        // 2. HITUNG PERGERAKAN SELAMA PERIODE (start_date s.d. end_date)
        $queryPeriode = (clone $baseQuery())->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate]);

        $penambahan_modal_desa = (clone $queryPeriode)->where('akuns.kode_akun', 'like', '3.1.01.%')->sum(DB::raw('kredit - debit'));
        $penambahan_modal_masyarakat = (clone $queryPeriode)->where('akuns.kode_akun', 'like', '3.1.02.%')->sum(DB::raw('kredit - debit'));
        $penambahan_donasi = (clone $queryPeriode)->where('akuns.kode_akun', 'like', '3.4.%')->sum(DB::raw('kredit - debit'));
        $bagi_hasil_desa = (clone $queryPeriode)->where('akuns.kode_akun', 'like', '3.2.01.%')->sum(DB::raw('debit - kredit'));
        $bagi_hasil_masyarakat = (clone $queryPeriode)->where('akuns.kode_akun', 'like', '3.2.02.%')->sum(DB::raw('debit - kredit'));

        // Laba Rugi Periode Berjalan
        $pendapatanPeriode = (clone $queryPeriode)->whereIn('akuns.tipe_akun', ['Pendapatan', 'Pendapatan & Beban Lainnya'])->sum(DB::raw('kredit - debit'));
        $bebanPeriode = (clone $queryPeriode)->whereIn('akuns.tipe_akun', ['Beban', 'HPP'])->sum(DB::raw('debit - kredit'));
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
            'bagi_hasil_desa', 'bagi_hasil_masyarakat',
            'penyertaan_modal_akhir', 'saldo_laba_akhir', 'modal_donasi_akhir', 'ekuitas_akhir',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}

