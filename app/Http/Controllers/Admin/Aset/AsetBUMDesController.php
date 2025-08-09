<?php

namespace App\Http\Controllers\Admin\Aset;

use App\Models\AsetBUMDes;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;

class AsetBUMDesController extends Controller
{
    /**
     * Menampilkan daftar aset.
     */
    public function index(): View
    {
        $aset = AsetBUMDes::with('unitUsaha')->orderBy('created_at', 'desc')->paginate(10);
        return view('aset.index', compact('aset'));
    }

    /**
     * Menampilkan form untuk membuat aset baru.
     */
    public function create(): View
    {
        $unitUsahas = UnitUsaha::all();
        return view('aset.create', compact('unitUsahas'));
    }

    /**
     * Menyimpan aset baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'nomor_inventaris' => 'required|string|max:255|unique:aset_bumdes,nomor_inventaris',
            'nama_aset' => 'required|string|max:255',
            'jenis_aset' => 'required|string|max:100',
            'nilai_perolehan' => 'required|numeric|min:0',
            'tanggal_perolehan' => 'required|date',
            'kondisi' => 'required|string|in:Baik,Rusak Ringan,Rusak Berat',
            'lokasi' => 'nullable|string|max:255',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id',
        ]);

        try {
            AsetBUMDes::create($validatedData);
            return redirect()->route('bumdes.aset.aset.index')->with('success', 'Aset berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan aset: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail aset.
     */
    public function show(AsetBUMDes $aset): View
    {
        return view('aset.show', compact('aset'));
    }

    /**
     * Menampilkan form untuk mengedit aset.
     */
    public function edit(AsetBUMDes $aset): View
    {
        $unitUsahas = UnitUsaha::all();
        return view('aset.edit', compact('aset', 'unitUsahas'));
    }

    /**
     * Memperbarui aset di database.
     */
    public function update(Request $request, AsetBUMDes $aset): RedirectResponse
    {
        $validatedData = $request->validate([
            'nomor_inventaris' => 'required|string|max:255|unique:aset_bumdes,nomor_inventaris,' . $aset->aset_id . ',aset_id',
            'nama_aset' => 'required|string|max:255',
            'jenis_aset' => 'required|string|max:100',
            'nilai_perolehan' => 'required|numeric|min:0',
            'tanggal_perolehan' => 'required|date',
            'kondisi' => 'required|string|in:Baik,Rusak Ringan,Rusak Berat',
            'lokasi' => 'nullable|string|max:255',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id',
        ]);

        try {
            $aset->update($validatedData);
            return redirect()->route('bumdes.aset.aset.index')->with('success', 'Aset berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui aset: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus aset dari database.
     */
    public function destroy(AsetBUMDes $aset): RedirectResponse
    {
        try {
            $aset->delete();
            return redirect()->route('bumdes.aset.aset.index')->with('success', 'Aset berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus aset: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman penyusutan.
     */
    public function penyusutan(): View
    {
        return view('aset.penyusutan');
    }

    /**
     * Menampilkan halaman pemeliharaan.
     */
    public function pemeliharaan(): View
    {
        return view('aset.pemeliharaan');
    }
}
