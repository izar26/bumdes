<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\DetailJurnal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BukuBesarController extends Controller
{
    /**
     * Menampilkan halaman form filter laporan Buku Besar.
     */
    public function index()
    {
        // Ambil semua akun yang merupakan akun detail (bukan header)
        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun')->get();
        return view('laporan.buku_besar.index', compact('akuns'));
    }

    /**
     * Memproses filter dan menampilkan laporan Buku Besar.
     */
    // app/Http/Controllers/Laporan/BukuBesarController.php

public function generate(Request $request)
{
    // 1. Validasi input dari form
    $request->validate([
        'akun_id' => 'required|exists:akuns,akun_id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    $akunId = $request->akun_id;
    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);
    $akun = Akun::findOrFail($akunId);

    // 2. Hitung Saldo Awal (Opening Balance)
    // Kita beritahu database untuk mengambil 'debit' dari tabel 'detail_jurnals'
    $saldoAwalDebit = DetailJurnal::where('akun_id', $akunId)
        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
        ->where('jurnal_umums.tanggal_transaksi', '<', $startDate)
        ->sum('detail_jurnals.debit'); // <-- PERBAIKAN DI SINI

    // Kita beritahu database untuk mengambil 'kredit' dari tabel 'detail_jurnals'    
    $saldoAwalKredit = DetailJurnal::where('akun_id', $akunId)
        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
        ->where('jurnal_umums.tanggal_transaksi', '<', $startDate)
        ->sum('detail_jurnals.kredit'); // <-- PERBAIKAN DI SINI

    $saldoAwal = $saldoAwalDebit - $saldoAwalKredit;

    // 3. Ambil daftar transaksi sesuai rentang tanggal
    $transaksis = DetailJurnal::with('jurnal')
        ->where('akun_id', $akunId)
        ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
        ->whereBetween('jurnal_umums.tanggal_transaksi', [$startDate, $endDate])
        ->orderBy('jurnal_umums.tanggal_transaksi', 'asc')
        ->orderBy('detail_jurnals.detail_jurnal_id', 'asc')
        ->select('detail_jurnals.*') 
        ->get();
        
    // 4. Kirim semua data ke view untuk ditampilkan
    return view('laporan.buku_besar.show', compact(
        'akun', 
        'startDate', 
        'endDate', 
        'saldoAwal', 
        'transaksis'
    ));
}
}