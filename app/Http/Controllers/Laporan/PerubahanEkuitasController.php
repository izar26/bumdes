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
     * Memproses filter dan menampilkan laporan.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            // --- PERUBAHAN 1: Mengizinkan nilai 'pusat' ---
            'unit_usaha_id' => 'nullable|string',
            'tanggal_cetak' => 'nullable|date',
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $unitUsahaId = $request->unit_usaha_id;
        $bumdes = Bungdes::first();
        $tanggalCetak = $request->tanggal_cetak ? Carbon::parse($request->tanggal_cetak) : now();

        $baseQueryBuilder = function() use ($user, $unitUsahaId) {
            $query = DetailJurnal::join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                ->join('akuns', 'detail_jurnals.akun_id', '=', 'akuns.akun_id')
                ->where('jurnal_umums.status', 'disetujui');
            
            // --- PERUBAHAN 2: Menambahkan logika filter unit usaha ---
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
            // --- AKHIR PERUBAHAN ---
            return $query;
        };
        
        // 1. Hitung Saldo Awal Ekuitas
        $saldoAwalEkuitas = $baseQueryBuilder()
            ->where('akuns.tipe_akun', 'Ekuitas')
            ->where('jurnal_umums.tanggal_transaksi', '<', $startDate)
            ->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));

        // 2. Hitung Laba/Rugi Bersih selama periode berjalan
        $labaRugiQuery = $baseQueryBuilder()
            ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate]);
            
        $totalPendapatan = (clone $labaRugiQuery)
            ->whereIn('akuns.tipe_akun', ['Pendapatan', 'Pendapatan & Beban Lainnya'])
            ->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));
            
        $totalBeban = (clone $labaRugiQuery)
            ->whereIn('akuns.tipe_akun', ['Beban', 'HPP'])
            ->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit'));
            
        $labaRugiPeriodeIni = $totalPendapatan - $totalBeban;

        // 3. Hitung Penambahan Modal selama periode berjalan
        $penambahanModal = $baseQueryBuilder()
            ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])
            ->where('akuns.kode_akun', 'like', '3.1.%') // Kode untuk Modal Disetor
            ->sum(DB::raw('detail_jurnals.kredit - detail_jurnals.debit'));

        // 4. Hitung Pengurangan Modal (Bagi Hasil) selama periode berjalan
        $penguranganModal = $baseQueryBuilder()
            ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])
            ->where('akuns.kode_akun', 'like', '3.2.%') // Kode untuk Bagi Hasil
            ->sum(DB::raw('detail_jurnals.debit - detail_jurnals.kredit')); // Dibalik karena mengurangi modal

        // 5. Hitung Saldo Akhir Ekuitas
        $ekuitasAkhir = $saldoAwalEkuitas + $labaRugiPeriodeIni + $penambahanModal - $penguranganModal;
        
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];
        
        return view('laporan.perubahan_ekuitas.show', compact(
            'startDate', 'endDate', 'bumdes', 'tanggalCetak',
            'saldoAwalEkuitas',
            'labaRugiPeriodeIni',
            'penambahanModal',
            'penguranganModal',
            'ekuitasAkhir',
            'penandaTangan1',
            'penandaTangan2'
        ));
    }
}
    