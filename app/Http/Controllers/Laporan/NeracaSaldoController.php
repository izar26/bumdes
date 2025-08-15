<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use App\Models\Bungdes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;

class NeracaSaldoController extends Controller
{
    /**
     * Menampilkan halaman form filter.
     */
    public function index()
    {
        $user = Auth::user();
        $unitUsahas = collect();

        // FIX: Perbaiki logika peran untuk konsistensi
        if ($user->hasRole(['bendahara_bumdes', 'sekretaris_bumdes'])) {
            $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        } else {
            // FIX: Tambahkan nama tabel eksplisit untuk menghindari ambiguitas
            $unitUsahas = $user->unitUsahas()->where('status_operasi', 'Aktif')->select('unit_usahas.*')->get();
        }
        return view('laporan.neraca_saldo.index', compact('unitUsahas'));
    }

    /**
     * Memproses filter dan menampilkan laporan.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id'
        ]);

        $user = Auth::user();
        $reportDate = Carbon::parse($request->report_date);
        $unitUsahaId = $request->unit_usaha_id;
        $bumdes = Bungdes::first();

        // FIX: Gunakan satu query efisien untuk mengambil semua data
        $query = Akun::select('akuns.kode_akun', 'akuns.nama_akun')
            ->selectRaw('SUM(detail_jurnals.debit) as total_debit')
            ->selectRaw('SUM(detail_jurnals.kredit) as total_kredit')
            ->join('detail_jurnals', 'akuns.akun_id', '=', 'detail_jurnals.akun_id')
            ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('jurnal_umums.status', 'disetujui')
            ->where('jurnal_umums.tanggal_transaksi', '<=', $reportDate);

        // FIX: Logika filter unit usaha yang disatukan
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $query->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);
        }
        elseif ($user->hasRole(['bendahara_bumdes', 'sekretaris_bumdes']) && !empty($unitUsahaId)) {
            $query->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
        }

        $laporanData = $query->groupBy('akuns.akun_id', 'akuns.kode_akun', 'akuns.nama_akun')
            ->having(DB::raw('SUM(detail_jurnals.debit)'), '!=', 0)
            ->orHaving(DB::raw('SUM(detail_jurnals.kredit)'), '!=', 0)
            ->orderBy('akuns.kode_akun')
            ->get();

        $totalDebit = $laporanData->sum('total_debit');
        $totalKredit = $laporanData->sum('total_kredit');

        // Menyiapkan data tanda tangan untuk view
        $penandaTangan1 = [ 'jabatan' => 'Direktur', 'nama' => 'Nama Direktur Anda' ];
        $penandaTangan2 = [ 'jabatan' => 'Bendahara', 'nama' => 'Nama Bendahara Anda' ];

        return view('laporan.neraca_saldo.show', compact(
            'reportDate',
            'laporanData',
            'totalDebit',
            'totalKredit',
            'bumdes',
            'penandaTangan1',
            'penandaTangan2'
        ));
    }
}
