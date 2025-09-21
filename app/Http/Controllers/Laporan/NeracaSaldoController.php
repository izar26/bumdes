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

        $baseQuery = DB::table('akuns')
            ->leftJoin('detail_jurnals', 'akuns.akun_id', '=', 'detail_jurnals.akun_id')
            ->leftJoin('jurnal_umums', function($join) use ($endDate) {
                $join->on('detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                     ->where('jurnal_umums.status', 'disetujui')
                     ->where('jurnal_umums.tanggal_transaksi', '<=', $endDate);
            })
            ->where('akuns.is_header', 0)
            ->select(
                'akuns.kode_akun',
                'akuns.nama_akun',
                'akuns.tipe_akun',
                DB::raw('COALESCE(SUM(detail_jurnals.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(detail_jurnals.kredit), 0) as total_kredit')
            );

        // Terapkan filter unit usaha jika ada
        if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $baseQuery->where(function($q) use ($managedUnitIds) {
                $q->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds)
                  ->orWhereNull('jurnal_umums.jurnal_id'); // Tetap tampilkan akun meski belum ada transaksi
            });
        } elseif ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
            if ($unitUsahaId === 'pusat') {
                $baseQuery->where(function($q) {
                    $q->whereNull('jurnal_umums.unit_usaha_id')
                      ->orWhereNull('jurnal_umums.jurnal_id');
                });
            } elseif (!empty($unitUsahaId)) {
                $baseQuery->where(function($q) use ($unitUsahaId) {
                    $q->where('jurnal_umums.unit_usaha_id', $unitUsahaId)
                      ->orWhereNull('jurnal_umums.jurnal_id');
                });
            }
        }

        $results = $baseQuery
            ->groupBy('akuns.akun_id', 'akuns.kode_akun', 'akuns.nama_akun', 'akuns.tipe_akun')
            ->orderBy('akuns.kode_akun')
            ->get();
        
        $laporanData = [];
        $akunNormalDebit = ['Aset', 'HPP', 'Beban'];

        foreach ($results as $akun) {
            $saldoDebit = 0;
            $saldoKredit = 0;

            if (in_array($akun->tipe_akun, $akunNormalDebit)) {
                $saldo = $akun->total_debit - $akun->total_kredit;
                if (str_contains($akun->nama_akun, 'Akumulasi Penyusutan') || str_contains($akun->nama_akun, 'Penyisihan Piutang')) {
                    $saldo = $akun->total_kredit - $akun->total_debit; // Saldo normal kredit untuk kontra aset
                    if ($saldo > 0) $saldoKredit = $saldo;
                    else $saldoDebit = abs($saldo);
                } else {
                    if ($saldo > 0) $saldoDebit = $saldo;
                    else $saldoKredit = abs($saldo);
                }
            } else { // Kewajiban, Ekuitas, Pendapatan
                $saldo = $akun->total_kredit - $akun->total_debit;
                if ($saldo > 0) $saldoKredit = $saldo;
                else $saldoDebit = abs($saldo);
            }
            
            // Hanya tampilkan akun yang memiliki saldo
            if ($saldoDebit > 0 || $saldoKredit > 0) {
                $laporanData[] = (object)[
                    'kode_akun' => $akun->kode_akun,
                    'nama_akun' => $akun->nama_akun,
                    'saldo_debit' => $saldoDebit,
                    'saldo_kredit' => $saldoKredit,
                ];
            }
        }
        
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

