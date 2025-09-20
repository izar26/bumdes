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
     * Memproses filter dan menampilkan laporan komparatif.
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

        $getNeracaData = function(Carbon $reportDate) use ($user, $unitUsahaId) {
            $startOfYear = $reportDate->copy()->startOfYear();

            // --- BASE QUERY BUILDER ---
            $baseQueryBuilder = function() use ($reportDate, $user, $unitUsahaId) {
                $query = DB::table('detail_jurnals')
                    ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                    ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                    ->where('jurnal_umums.status', 'disetujui')
                    ->where('jurnal_umums.tanggal_transaksi', '<=', $reportDate->toDateString());

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

            // --- 1. HITUNG SALDO AKUN NERACA ---
            $neracaAccounts = $baseQueryBuilder()
                ->whereIn('akuns.tipe_akun', ['Aset', 'Kewajiban', 'Ekuitas'])
                ->where('akuns.is_header', 0)
                ->select(
                    'akuns.nama_akun', 
                    'akuns.tipe_akun',
                    DB::raw('COALESCE(SUM(detail_jurnals.debit), 0) as total_debit'),
                    DB::raw('COALESCE(SUM(detail_jurnals.kredit), 0) as total_kredit')
                )
                ->groupBy('akuns.akun_id', 'akuns.nama_akun', 'akuns.tipe_akun')
                ->get();
            
            // --- 2. HITUNG LABA/RUGI TAHUN BERJALAN ---
            $labaRugiQuery = $baseQueryBuilder()
                ->whereIn('akuns.tipe_akun', ['Pendapatan', 'Pendapatan & Beban Lainnya', 'Beban', 'HPP'])
                ->whereBetween('jurnal_umums.tanggal_transaksi', [$startOfYear->toDateString(), $reportDate->toDateString()]);
            
            // --- PERBAIKAN DIMULAI DI SINI ---
            // Secara eksplisit menyebutkan nama tabel 'detail_jurnals' untuk menghindari ambiguitas
            $totalPendapatan = (clone $labaRugiQuery)
                ->whereIn('akuns.tipe_akun', ['Pendapatan', 'Pendapatan & Beban Lainnya'])
                ->select(DB::raw('COALESCE(SUM(detail_jurnals.kredit), 0) - COALESCE(SUM(detail_jurnals.debit), 0) as total'))
                ->first()->total ?? 0;

            $totalBeban = (clone $labaRugiQuery)
                ->whereIn('akuns.tipe_akun', ['Beban', 'HPP'])
                ->select(DB::raw('COALESCE(SUM(detail_jurnals.debit), 0) - COALESCE(SUM(detail_jurnals.kredit), 0) as total'))
                ->first()->total ?? 0;
            // --- AKHIR PERBAIKAN ---

            $labaTahunBerjalan = $totalPendapatan - $totalBeban;

            // --- 3. PROSES DAN KELOMPOKKAN DATA ---
            $data = ['Aset' => [], 'Kewajiban' => [], 'Ekuitas' => []];
            $totalModal = 0;
            $kontraAsetKeywords = ['Akumulasi Penyusutan', 'Penyisihan Piutang'];

            foreach ($neracaAccounts as $akun) {
                $isKontraAset = false;
                foreach($kontraAsetKeywords as $keyword) {
                    if (str_contains($akun->nama_akun, $keyword)) {
                        $isKontraAset = true;
                        break;
                    }
                }

                if ($akun->tipe_akun === 'Aset') {
                    $saldo = $isKontraAset ? 
                             ($akun->total_kredit - $akun->total_debit) : 
                             ($akun->total_debit - $akun->total_kredit);
                    if($saldo != 0) $data['Aset'][$akun->nama_akun] = $saldo;
                } elseif ($akun->tipe_akun === 'Kewajiban') {
                    $saldo = $akun->total_kredit - $akun->total_debit;
                    if($saldo != 0) $data['Kewajiban'][$akun->nama_akun] = $saldo;
                } elseif ($akun->tipe_akun === 'Ekuitas') {
                    $saldo = $akun->total_kredit - $akun->total_debit;
                    if($saldo != 0) {
                        $data['Ekuitas'][$akun->nama_akun] = $saldo;
                        $totalModal += $saldo;
                    }
                }
            }
            
            $data['labaDitahan'] = $labaTahunBerjalan;
            $data['totalEkuitas'] = $totalModal + $labaTahunBerjalan;

            return $data;
        };

        $dataAwal = $getNeracaData($startDate->copy()->subDay());
        $dataAkhir = $getNeracaData($endDate);

        $allAsetNames = array_unique(array_merge(array_keys($dataAwal['Aset']), array_keys($dataAkhir['Aset'])));
        $allKewajibanNames = array_unique(array_merge(array_keys($dataAwal['Kewajiban']), array_keys($dataAkhir['Kewajiban'])));
        $allEkuitasNames = array_unique(array_merge(array_keys($dataAwal['Ekuitas']), array_keys($dataAkhir['Ekuitas'])));
        
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];
        
        return view('laporan.neraca.show', compact(
            'bumdes', 'startDate', 'endDate', 'tanggalCetak',
            'dataAwal', 'dataAkhir',
            'allAsetNames', 'allKewajibanNames', 'allEkuitasNames',
            'penandaTangan1', 'penandaTangan2'
        ));
    }
}

