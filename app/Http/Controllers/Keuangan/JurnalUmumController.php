<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmum;
use Illuminate\Http\Request;

class JurnalUmumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua data jurnal, urutkan dari yang terbaru.
        // Gunakan 'with' untuk mengambil relasi detailJurnals dan akunnya sekaligus (Eager Loading).
        $jurnals = JurnalUmum::with('detailJurnals.akun')
                             ->latest('tanggal_transaksi')
                             ->get();

        return view('keuangan.jurnal.index', compact('jurnals'));
    }
}