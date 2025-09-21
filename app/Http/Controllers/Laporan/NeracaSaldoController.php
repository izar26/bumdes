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
     * Memproses filter dan menampilkan laporan Neraca Saldo standar.
     */
    public function generate(Request $request)
    {
        // Validasi diubah, hanya perlu tanggal akhir
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

        $query = DB::table('akuns')
            ->leftJoin('detail_jurnals', 'akuns.akun_id', '=', 'detail_jurnals.akun_id')
            ->leftJoin('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('akuns.is_header', 0)
            ->where('jurnal_umums.status', 'disetujui')
            ->where('jurnal_umums.tanggal_transaksi', '<=', $endDate->toDateString())
            ->select(
                'akuns.kode_akun', 'akuns.nama_akun', 'akuns.tipe_akun',
                DB::raw("COALESCE(SUM(detail_jurnals.debit), 0) as total_debit"),
                DB::raw("COALESCE(SUM(detail_jurnals.kredit), 0) as total_kredit")
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
            $saldo = 0;
            $isKontraAset = str_contains($akun->nama_akun, 'Akumulasi Penyusutan') || str_contains($akun->nama_akun, 'Penyisihan Piutang');

            if (in_array($akun->tipe_akun, $akunNormalDebit) && !$isKontraAset) {
                $saldo = $akun->total_debit - $akun->total_kredit;
            } else { // Kewajiban, Ekuitas, Pendapatan, dan Kontra Aset
                $saldo = $akun->total_kredit - $akun->total_debit;
            }

            if (round($saldo, 2) != 0) {
                $laporanData[] = (object)[
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'saldo_debit' => $saldo > 0 && (in_array($akun->tipe_akun, $akunNormalDebit) && !$isKontraAset) ? $saldo : 0,
                    'saldo_kredit' => $saldo > 0 && (!in_array($akun->tipe_akun, $akunNormalDebit) || $isKontraAset) ? $saldo : 0,
                ];
            }
        }
        
        // Data Penanda Tangan
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];

        return view('laporan.neraca_saldo.show', compact(
            'bumdes', 'endDate', 'tanggalCetak', 'laporanData',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}

