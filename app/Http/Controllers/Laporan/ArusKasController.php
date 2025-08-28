<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use App\Models\Bungdes;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;

class ArusKasController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:bendahara_bumdes|sekretaris_bumdes|direktur_bumdes');
    }

    public function index()
    {
        $user = Auth::user();
        $unitUsahas = collect();

        // FIX: Perbaiki logika peran dan tambahkan nama tabel eksplisit di pluck
        if ($user->hasRole(['bendahara_bumdes', 'sekretaris_bumdes'])) {
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        } else {
            // FIX: Tambahkan nama tabel eksplisit di pluck
            $unitUsahas = $user->unitUsahas()->where('status_operasi', 'Aktif')->select('unit_usahas.*')->get();
        }

        return view('laporan.arus_kas.index', compact('unitUsahas'));
    }

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

        $akunKasBankIds = Akun::whereIn('nama_akun', ['Kas Tunai', 'Kas di Bank BSI', 'Kas di Bank Mandiri', 'Kas di Bank BRI', 'Kas di Bank BPD', 'Kas Kecil (Petty Cash)', 'Deposito <= 3 bulan'])
                               ->where('tipe_akun', 'Aset')
                               ->where('is_header', 0)
                               ->pluck('akun_id');

        if ($akunKasBankIds->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada akun Kas atau Bank yang ditemukan.');
        }

        $baseQuery = DetailJurnal::join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                                 ->where('jurnal_umums.status', 'disetujui');

        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            // FIX: Tambahkan nama tabel eksplisit di pluck
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $baseQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
        } elseif ($user->hasRole(['bendahara_bumdes', 'sekretaris_bumdes']) && !empty($unitUsahaId)) {
            $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }

        $saldoKasAwal = (clone $baseQuery)
            ->whereIn('detail_jurnals.akun_id', $akunKasBankIds)
            ->where('jurnal_umums.tanggal_transaksi', '<', $startDate)
            ->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));

        $transaksiKas = (clone $baseQuery)
            ->with(['jurnal.detailJurnals.akun'])
            ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])
            ->whereIn('detail_jurnals.akun_id', $akunKasBankIds)
            ->get();

        $arusOperasi = []; $arusInvestasi = []; $arusPendanaan = [];

        foreach ($transaksiKas as $transaksi) {
            $akunLawan = $transaksi->jurnal->detailJurnals->firstWhere(function($detail) use ($transaksi){
                return $detail->akun_id !== $transaksi->akun_id;
            })->akun;

            if ($akunLawan) {
                $jumlah = $transaksi->debit - $transaksi->kredit;
                $item = ['nama' => $akunLawan->nama_akun, 'jumlah' => $jumlah];

                if (in_array($akunLawan->tipe_akun, ['Pendapatan', 'HPP', 'Beban', 'Piutang', 'Kewajiban', 'Persediaan'])) {
                    $arusOperasi[] = $item;
                } elseif (in_array($akunLawan->tipe_akun, ['Aset', 'Aset Tetap', 'Investasi Jangka Panjang'])) {
                    $arusInvestasi[] = $item;
                } elseif (in_array($akunLawan->tipe_akun, ['Ekuitas', 'Modal'])) {
                    $arusPendanaan[] = $item;
                }
            }
        }

        $totalOperasi = collect($arusOperasi)->sum('jumlah');
        $totalInvestasi = collect($arusInvestasi)->sum('jumlah');
        $totalPendanaan = collect($arusPendanaan)->sum('jumlah');

        $kenaikanPenurunanKas = $totalOperasi + $totalInvestasi + $totalPendanaan;
        $saldoKasAkhir = $saldoKasAwal + $kenaikanPenurunanKas;

        return view('laporan.arus_kas.show', compact(
            'startDate', 'endDate', 'saldoKasAwal', 'arusOperasi',
            'totalOperasi', 'arusInvestasi', 'totalInvestasi',
            'arusPendanaan', 'totalPendanaan',
            'kenaikanPenurunanKas', 'saldoKasAkhir', 'bumdes'
        ));
    }
}
