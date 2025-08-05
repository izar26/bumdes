<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\UnitUsaha;
use App\Models\Stok;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    /**
     * Helper function untuk mengambil daftar Unit Usaha berdasarkan peran user.
     */
    private function getUnitUsahasForUser()
    {
        $user = auth()->user();
        // Bendahara atau Admin BUMDes bisa melihat semua unit usaha
        if ($user->hasRole(['bendahara_bumdes', 'admin_bumdes'])) {
            return UnitUsaha::where('status_operasi', 'Aktif')->get();
        }

        // Peran lain hanya melihat unit usaha yang menjadi tanggung jawabnya
        return $user->unitUsahas()->where('status_operasi', 'Aktif')->get();
    }

    public function index()
    {
        $produks = Produk::with('unitUsaha', 'stok')->latest()->get();
        return view('usaha.produk.index', compact('produks'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        // Menggunakan helper function agar lebih rapi
        $unitUsahas = $this->getUnitUsahasForUser();
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('usaha.produk.create', compact('unitUsahas', 'kategoris'));
    }

    /**
     * Store a newly created product in storage.
     */
   public function store(Request $request)
{
    $currentUser = Auth::user();

    // Definisikan aturan validasi dasar.
    // Pastikan 'unit_usaha_id' didefinisikan sebagai ARRAY dari awal.
    $validationRules = [
        'nama_produk' => 'required|string|max:255',
        'harga_beli' => 'required|numeric|min:0',
        'harga_jual' => 'required|numeric|min:0|gt:harga_beli',
        'satuan_unit' => 'required|string|max:50',
        'deskripsi_produk' => 'nullable|string|max:1000',
        'kategori_id' => 'nullable|exists:kategoris,id',
        'stok_minimum' => 'nullable|integer|min:0',
        'unit_usaha_id' => [
            'required',
            'exists:unit_usahas,unit_usaha_id',
        ],
        'stok_awal' => 'required|numeric|min:0',
    ];

    // Jika manajer unit usaha, tambahkan aturan kustom ke array 'unit_usaha_id'
    // Menggunakan array push seperti ini sekarang akan berhasil.
    if ($currentUser->role === 'manajer_unit_usaha') {
        $validationRules['unit_usaha_id'][] = function ($attribute, $value, $fail) use ($currentUser) {
            if (!$currentUser->unitUsahas()->where('unit_usaha_id', $value)->exists()) {
                $fail('Anda tidak memiliki izin untuk mengelola unit usaha ini.');
            }
        };
    }

    $request->validate($validationRules);

    try {
        DB::beginTransaction();

        $produk = Produk::create($request->all());

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
        return redirect()->back()
                         ->with('error', 'Gagal menyimpan produk: ' . $e->getMessage())
                         ->withInput();
    }
}

    public function show(Produk $produk)
    {
        return view('usaha.produk.show', compact('produk'));
    }

    public function edit(Produk $produk)
    {
        $unitUsahas = $this->getUnitUsahasForUser();
        // PERBAIKAN: Tambahkan kategori ke view edit
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('usaha.produk.edit', compact('produk', 'unitUsahas', 'kategoris'));
    }

    public function update(Request $request, Produk $produk)
    {
        // PERBAIKAN: Tambahkan validasi untuk kategori_id
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi_produk' => 'nullable|string|max:1000',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0|gt:harga_beli',
            'satuan_unit' => 'required|string|max:50',
            'stok_minimum' => 'nullable|integer|min:0',
            'unit_usaha_id' => 'required|exists:unit_usahas,unit_usaha_id',
            'kategori_id' => 'nullable|exists:kategoris,id',
        ]);

        $produk->update($request->all());

        // PERBAIKAN: Menggunakan nama rute yang benar 'usaha.produk.index'
        return redirect()->route('usaha.produk.index')
                         ->with('success', 'Data produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        DB::transaction(function () use ($produk) {
            $produk->delete();
        });

        // PERBAIKAN: Menggunakan nama rute yang benar 'usaha.produk.index'
        return redirect()->route('usaha.produk.index')
                         ->with('success', 'Data produk berhasil dihapus.');
    }
}
