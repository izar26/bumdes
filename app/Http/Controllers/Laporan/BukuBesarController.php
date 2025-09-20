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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BukuBesarController extends Controller
{
    /**
     * Menampilkan halaman form filter.
     */
    public function index()
    {
        $user = Auth::user();
        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun')->get();
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

        return view('laporan.buku_besar.index', compact('akuns', 'unitUsahas', 'user'));
    }

    /**
     * Memproses filter dan menampilkan laporan.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'akun_id' => 'required|exists:akuns,akun_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'unit_usaha_id' => 'nullable|string', // Diubah ke string untuk mengakomodasi 'pusat'
            'tanggal_cetak' => 'nullable|date',
        ]);

        $user = Auth::user();
        $akunId = $request->akun_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $akun = Akun::findOrFail($akunId);
        $unitUsahaId = $request->unit_usaha_id;
        $bumdes = Bungdes::first();
        
        $tanggalCetak = $request->tanggal_cetak ? Carbon::parse($request->tanggal_cetak) : now();

        $baseQuery = DetailJurnal::join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
            ->where('detail_jurnals.akun_id', $akunId)
            ->where('jurnal_umums.status', 'disetujui');

        // --- PERUBAHAN DIMULAI DI SINI ---
        if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            // Logika untuk manajer unit usaha tidak berubah
            $managedUnitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $baseQuery->whereIn('jurnal_umums.unit_usaha_id', $managedUnitIds);

        } elseif ($user->hasAnyRole(['bendahara_bumdes', 'sekretaris_bumdes', 'direktur_bumdes', 'admin_bumdes'])) {
            // Logika untuk admin BUMDes diperbarui untuk menangani filter "Pusat"
            if (!empty($unitUsahaId)) {
                if ($unitUsahaId === 'pusat') {
                    // Jika user memilih "Hanya BUMDes Pusat", filter jurnal yang `unit_usaha_id`-nya NULL
                    $baseQuery->whereNull('jurnal_umums.unit_usaha_id');
                } else {
                    // Jika user memilih unit usaha spesifik
                    $baseQuery->where('jurnal_umums.unit_usaha_id', $unitUsahaId);
                }
            }
            // Jika $unitUsahaId kosong, tidak ada filter yang diterapkan (Laporan Gabungan). Ini sudah benar.
        }
        // --- AKHIR PERUBAHAN ---

        // Menghitung Saldo Awal
        $saldoAwalQuery = (clone $baseQuery)->where('jurnal_umums.tanggal_transaksi', '<', $startDate);
        
        $saldoAwalResult = $saldoAwalQuery->select(
            DB::raw('SUM(detail_jurnals.debit) as total_debit'),
            DB::raw('SUM(detail_jurnals.kredit) as total_kredit')
        )->first();

        $saldoAwalDebit = $saldoAwalResult->total_debit ?? 0;
        $saldoAwalKredit = $saldoAwalResult->total_kredit ?? 0;
        
        $akunNormalDebit = ['Aset', 'HPP', 'Beban', 'assets']; // Menambahkan 'assets' untuk konsistensi
        if (in_array($akun->tipe_akun, $akunNormalDebit)) {
            $saldoAwal = $saldoAwalDebit - $saldoAwalKredit;
        } else {
            $saldoAwal = $saldoAwalKredit - $saldoAwalDebit;
        }

        // Mengambil Transaksi pada Periode yang Dipilih
        $transaksis = (clone $baseQuery)
            ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])
            ->with('jurnal') // Eager loading untuk menghindari N+1 problem
            ->orderBy('jurnal_umums.tanggal_transaksi', 'asc')
            ->orderBy('detail_jurnals.detail_jurnal_id', 'asc')
            ->select('detail_jurnals.*')
            ->get();
            
        $direktur = User::role('direktur_bumdes')->with('anggota')->first();
        $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
        $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
        $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];

        return view('laporan.buku_besar.show', compact(
            'akun', 'startDate', 'endDate', 'saldoAwal', 'transaksis', 'bumdes',
            'penandaTangan1', 'penandaTangan2',
            'tanggalCetak'
        ));
    }
}

