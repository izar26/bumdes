<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\Produk;
use Illuminate\Http\Request;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualans = Penjualan::latest()->get();
        return view('usaha.penjualan.index', compact('penjualans'));
    }

    public function create()
    {
        $produks = Produk::orderBy('nama_produk')->get();
        return view('usaha.penjualan.create', compact('produks'));
    }

    public function store(Request $request)
    {
        // Logika penyimpanan akan kita buat di langkah berikutnya
    }

    // ... fungsi lainnya akan kita isi nanti ...
}