<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produk::with('unitUsaha')->latest()->get();
        return view('usaha.produk.index', compact('produks'));
    }

    public function create()
    {
        $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        return view('usaha.produk.create', compact('unitUsahas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|gte:harga_beli',
            'satuan_unit' => 'required|string|max:50',
            'unit_usaha_id' => 'required|exists:unit_usahas,unit_usaha_id',
        ]);

        Produk::create($request->all());

        return redirect()->route('produk.index')
                         ->with('success', 'Produk baru berhasil ditambahkan.');
    }

    public function show(Produk $produk)
    {
        return view('usaha.produk.show', compact('produk'));
    }

    public function edit(Produk $produk)
    {
        $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        return view('usaha.produk.edit', compact('produk', 'unitUsahas'));
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|gte:harga_beli',
            'satuan_unit' => 'required|string|max:50',
            'unit_usaha_id' => 'required|exists:unit_usahas,unit_usaha_id',
        ]);

        $produk->update($request->all());

        return redirect()->route('produk.index')
                         ->with('success', 'Data produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        $produk->delete();

        return redirect()->route('produk.index')
                         ->with('success', 'Data produk berhasil dihapus.');
    }
}