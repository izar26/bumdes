<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\UnitUsaha;
use App\Models\Stok;
use Illuminate\Http\Request;
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

    public function create()
    {
        $unitUsahas = $this->getUnitUsahasForUser();
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
            'stok_awal' => 'required|numeric|min:0',
        ]);

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

            return redirect()->route('produk.index')
                             ->with('success', 'Produk baru berhasil ditambahkan beserta stok awalnya.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan produk: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Produk $produk)
    {
        return view('usaha.produk.show', compact('produk'));
    }

    public function edit(Produk $produk)
    {
        $unitUsahas = $this->getUnitUsahasForUser();
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
        DB::transaction(function () use ($produk) {
            $produk->delete();
        });

        return redirect()->route('produk.index')
                         ->with('success', 'Data produk berhasil dihapus.');
    }
}