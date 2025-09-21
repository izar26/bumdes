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

class NeracaSaldoController extends Controller
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

        return view('laporan.neraca_saldo.index', compact('unitUsahas', 'user'));
    }

    /**
     * Memproses filter dan menampilkan laporan dengan format worksheet (mutasi & saldo).
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

        $query = DB::table('akuns')
            ->leftJoin('detail_jurnals', 'akuns.akun_id', '=', 'detail_jurnals.akun_id')
            ->leftJoin('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('akuns.is_header', 0)
            ->where('jurnal_umums.status', 'disetujui')
            ->select(
                'akuns.kode_akun', 'akuns.nama_akun', 'akuns.tipe_akun',
                // Mutasi (Pergerakan selama periode)
                DB::raw("SUM(CASE WHEN jurnal_umums.tanggal_transaksi BETWEEN '{$startDate->toDateString()}' AND '{$endDate->toDateString()}' THEN detail_jurnals.debit ELSE 0 END) as mutasi_debit"),
                DB::raw("SUM(CASE WHEN jurnal_umums.tanggal_transaksi BETWEEN '{$startDate->toDateString()}' AND '{$endDate->toDateString()}' THEN detail_jurnals.kredit ELSE 0 END) as mutasi_kredit"),
                // Saldo Akhir (Total hingga akhir periode)
                DB::raw("SUM(CASE WHEN jurnal_umums.tanggal_transaksi <= '{$endDate->toDateString()}' THEN detail_jurnals.debit ELSE 0 END) as total_debit_akhir"),
                DB::raw("SUM(CASE WHEN jurnal_umums.tanggal_transaksi <= '{$endDate->toDateString()}' THEN detail_jurnals.kredit ELSE 0 END) as total_kredit_akhir")
            );

        // Filter unit usaha
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
        
        $results = $query
            ->groupBy('akuns.akun_id', 'akuns.kode_akun', 'akuns.nama_akun', 'akuns.tipe_akun')
            ->orderBy('akuns.kode_akun')
            ->get();
        
        $laporanData = [];
        $akunNormalDebit = ['Aset', 'HPP', 'Beban'];
        
        foreach ($results as $akun) {
            // Hitung Saldo Akhir
            $saldoAkhir = 0;
            if (in_array($akun->tipe_akun, $akunNormalDebit)) {
                // Handle akun kontra-aset (saldo normal kredit)
                if (str_contains($akun->nama_akun, 'Akumulasi Penyusutan') || str_contains($akun->nama_akun, 'Penyisihan Piutang')) {
                    $saldoAkhir = $akun->total_kredit_akhir - $akun->total_debit_akhir;
                } else {
                    $saldoAkhir = $akun->total_debit_akhir - $akun->total_kredit_akhir;
                }
            } else { // Kewajiban, Ekuitas, Pendapatan
                $saldoAkhir = $akun->total_kredit_akhir - $akun->total_debit_akhir;
            }

            // Hanya tampilkan akun yang punya mutasi atau saldo
            if ($akun->mutasi_debit != 0 || $akun->mutasi_kredit != 0 || $saldoAkhir != 0) {
                $laporanData[] = (object)[
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'mutasi_debit' => $akun->mutasi_debit,
                    'mutasi_kredit' => $akun->mutasi_kredit,
                    'saldo_debit' => $saldoAkhir > 0 && in_array($akun->tipe_akun, $akunNormalDebit) && !str_contains($akun->nama_akun, 'Akumulasi') ? $saldoAkhir : 0,
                    'saldo_kredit' => $saldoAkhir > 0 && !in_array($akun->tipe_akun, $akunNormalDebit) || ($saldoAkhir > 0 && str_contains($akun->nama_akun, 'Akumulasi')) ? $saldoAkhir : 0,
                ];
            }
        }
        
        // Data Penanda Tangan
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];

        return view('laporan.neraca_saldo.show', compact(
            'bumdes', 'startDate', 'endDate', 'tanggalCetak', 'laporanData',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}
