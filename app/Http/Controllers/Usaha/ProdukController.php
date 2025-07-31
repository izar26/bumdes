<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\UnitUsaha;
use App\Models\Stok; // <-- Pastikan ini ada
use App\Models\Kategori; // <-- Pastikan ini ada
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- Pastikan ini ada
use App\Http\Controllers\Usaha\KategoriController; // <-- Pastikan ini ada

class ProdukController extends Controller
{
    public function index()
    {
        // Tambahkan relasi 'stok' untuk ditampilkan di tabel
        $produks = Produk::with('unitUsaha', 'stok')->latest()->get();
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
        // Tambahkan validasi untuk stok_awal
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|gte:harga_beli',
            'satuan_unit' => 'required|string|max:50',
            'deskripsi_produk' => 'nullable|string|max:1000',
            'kategori' => 'nullable|string|max:100',
            'stok_minimum' => 'nullable|integer|min:0',
            'unit_usaha_id' => 'required|exists:unit_usahas,unit_usaha_id',
            'stok_awal' => 'required|numeric|min:0', // <-- Validasi baru
        ]);

        try {
            DB::beginTransaction();

            // 1. Simpan Produk
            $produk = Produk::create($request->all());

            // 2. Buat Stok Awal untuk produk baru
            Stok::create([
                'produk_id' => $produk->produk_id,
                'unit_usaha_id' => $request->unit_usaha_id,
                'jumlah_stok' => $request->stok_awal,
                'tanggal_perbarui' => now(),
            ]);

            DB::commit();

            return redirect()->route('usaha.produk.index')
                             ->with('success', 'Produk baru berhasil ditambahkan beserta stok awalnya.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan produk: ' . $e->getMessage())->withInput();
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produk $produk)
    {
        // Menggunakan transaction untuk keamanan jika ada proses lain nantinya
        DB::transaction(function () use ($produk) {
            // Hapus stok terkait terlebih dahulu jika tidak ada onDelete Cascade di level DB
            // Namun, karena skema DB kita sudah punya onDelete cascade,
            // Stok akan terhapus otomatis saat produk dihapus.
            $produk->delete();
        });

        return redirect()->route('usaha.produk.index')
                         ->with('success', 'Data produk berhasil dihapus.');
    }
}
