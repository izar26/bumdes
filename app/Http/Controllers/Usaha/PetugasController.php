<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use Illuminate\Http\Request;

class PetugasController extends Controller
{
    public function index()
    {
        $semua_petugas = Petugas::latest()->paginate(10);
        return view('usaha.petugas.index', compact('semua_petugas'));
    }

    public function create()
    {
        // Method ini sekarang tidak dipakai karena kita menggunakan modal.
        // Anda bisa menghapusnya atau membiarkannya kosong.
        return redirect()->route('usaha.petugas.index');
    }

    public function store(Request $request)
    {
        $request->validate(['nama_petugas' => 'required|string|max:255|unique:petugas,nama_petugas']);
        Petugas::create($request->all());
        return redirect()->route('usaha.petugas.index')->with('success', 'Petugas baru berhasil ditambahkan.');
    }

    public function show(Petugas $petuga)
    {
        return redirect()->route('usaha.petugas.index');
    }

    // Method ini sekarang tidak dipakai karena form edit ada di modal.
    public function edit(Petugas $petuga)
    {
       return redirect()->route('usaha.petugas.index');
    }

    public function update(Request $request, Petugas $petuga)
    {
        $request->validate(['nama_petugas' => 'required|string|max:255|unique:petugas,nama_petugas,' . $petuga->id]);
        $petuga->update($request->all());
        return redirect()->route('usaha.petugas.index')->with('success', 'Data petugas berhasil diperbarui.');
    }

    public function destroy(Petugas $petuga)
    {
        if ($petuga->tagihan()->exists()) {
            return redirect()->route('usaha.petugas.index')->with('error', 'Petugas tidak bisa dihapus karena memiliki riwayat di data tagihan.');
        }
        $petuga->delete();
        return redirect()->route('usaha.petugas.index')->with('success', 'Data petugas berhasil dihapus.');
    }
}
