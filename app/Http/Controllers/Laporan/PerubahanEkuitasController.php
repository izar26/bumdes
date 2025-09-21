<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
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

        // Helper function untuk mengambil saldo
        $getSaldo = function($pattern, $date, $operator = '<=') use ($user, $unitUsahaId) {
            $query = DB::table('detail_jurnals')
                ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->where('jurnal_umums.status', 'disetujui')
                ->where('akuns.kode_akun', 'like', $pattern)
                ->whereDate('jurnal_umums.tanggal_transaksi', $operator, $date);
            
            // Filter unit usaha
            if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
                $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
                $query->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
            } elseif ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
                if ($unitUsahaId === 'pusat') $query->whereNull('jurnal_umums.unit_usaha_id');
                elseif (!empty($unitUsahaId)) $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
            }
            
            // Ekuitas normal kredit
            return $query->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));
        };
        
        // Helper untuk Laba Rugi
        $getLabaRugi = function($start, $end) use ($getSaldo) {
             $pendapatan = $getSaldo('4.%', $end, '<=') - $getSaldo('4.%', $start, '<');
             $hpp = ($getSaldo('5.%', $end, '<=') - $getSaldo('5.%', $start, '<')) * -1; // HPP adalah pengurang
             $beban = ($getSaldo('6.%', $end, '<=') - $getSaldo('6.%', $start, '<')) * -1; // Beban adalah pengurang
             $lainnya = $getSaldo('7.%', $end, '<=') - $getSaldo('7.%', $start, '<');
             return $pendapatan - $hpp - $beban + $lainnya;
        };
        
        // 1. HITUNG SALDO AWAL (per H-1 dari start_date)
        $tanggalAwal = $startDate->copy()->subDay();
        $modal_desa_awal = $getSaldo('3.1.01.%', $tanggalAwal);
        $modal_masyarakat_awal = $getSaldo('3.1.02.%', $tanggalAwal);
        $modal_donasi_awal = $getSaldo('3.4.%', $tanggalAwal);
        // Saldo Laba Awal adalah akumulasi laba dari awal waktu s.d. tanggal awal
        $saldo_laba_awal = $getLabaRugi(Carbon::minValue(), $tanggalAwal);

        // 2. HITUNG PERGERAKAN SELAMA PERIODE
        $penambahan_modal_desa = $getSaldo('3.1.01.%', $endDate) - $modal_desa_awal;
        $penambahan_modal_masyarakat = $getSaldo('3.1.02.%', $endDate) - $modal_masyarakat_awal;
        $laba_rugi_periode_berjalan = $getLabaRugi($tanggalAwal, $endDate);
        // Bagi hasil adalah pergerakan debit, jadi dikali -1
        $bagi_hasil_desa = ($getSaldo('3.2.01.%', $endDate) - $getSaldo('3.2.01.%', $tanggalAwal)) * -1;
        $bagi_hasil_masyarakat = ($getSaldo('3.2.02.%', $endDate) - $getSaldo('3.2.02.%', $tanggalAwal)) * -1;
        $penambahan_donasi = $getSaldo('3.4.%', $endDate) - $modal_donasi_awal;
        
        // 3. HITUNG SALDO AKHIR
        $penyertaan_modal_akhir = $modal_desa_awal + $modal_masyarakat_awal + $penambahan_modal_desa + $penambahan_modal_masyarakat;
        $saldo_laba_akhir = $saldo_laba_awal + $laba_rugi_periode_berjalan - $bagi_hasil_desa - $bagi_hasil_masyarakat;
        $modal_donasi_akhir = $modal_donasi_awal + $penambahan_donasi;
        $ekuitas_akhir = $penyertaan_modal_akhir + $saldo_laba_akhir + $modal_donasi_akhir;

        // Penanda Tangan
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];
        
        return view('laporan.perubahan_ekuitas.show', compact(
            'startDate', 'endDate', 'bumdes', 'tanggalCetak',
            'modal_desa_awal', 'modal_masyarakat_awal', 'saldo_laba_awal', 'modal_donasi_awal',
            'penambahan_modal_desa', 'penambahan_modal_masyarakat', 'laba_rugi_periode_berjalan',
            'bagi_hasil_desa', 'bagi_hasil_masyarakat',
            'penyertaan_modal_akhir', 'saldo_laba_akhir', 'modal_donasi_akhir', 'ekuitas_akhir',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}
