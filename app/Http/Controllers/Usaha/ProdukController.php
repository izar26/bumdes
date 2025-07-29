<?php

namespace App\Http\Controllers\Usaha; // Assuming ProdukController is in Usaha namespace

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk; // Make sure to import your Produk model
use App\Models\UnitUsaha; // Assuming you need this for the form
use App\Models\Kategori; // Assuming you need this for the form
use Illuminate\Support\Facades\Validator; // For validation

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produks = Produk::with('unitUsaha', 'kategori')->orderBy('nama_produk')->get();
        return view('usaha.produk.index', compact('produks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('usaha.produk.create', compact('unitUsahas', 'kategoris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'deskripsi_produk' => 'nullable|string',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0|gt:harga_beli', // jual must be greater than beli
            'satuan_unit' => 'required|string|max:50',
            'unit_usaha_id' => 'required|exists:unit_usahas,unit_usaha_id', // Assuming 'id' for UnitUsaha primary key
            'stok_minimum' => 'required|integer|min:0',
            'kategori_id' => 'nullable|exists:kategoris,id', // Assuming 'id' for Kategori primary key
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            Produk::create($request->all());
            return redirect()->route('usaha.produk.index')->with('success', 'Produk berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Produk $produk)
    {
        return view('usaha.produk.show', compact('produk'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produk $produk)
    {
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('usaha.produk.edit', compact('produk', 'unitUsahas', 'kategoris'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produk $produk)
    {
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'deskripsi_produk' => 'nullable|string',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0|gt:harga_beli',
            'satuan_unit' => 'required|string|max:50',
            'unit_usaha_id' => 'required|exists:unit_usahas,unit_usaha_id',
            'stok_minimum' => 'required|integer|min:0',
            'kategori_id' => 'nullable|exists:kategoris,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $produk->update($request->all());
            return redirect()->route('usaha.produk.index')->with('success', 'Produk berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui produk: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Produk $produk)
    {
        try {
            $produk->delete();
            return redirect()->route('usaha.produk.index')->with('success', 'Produk berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }
}
