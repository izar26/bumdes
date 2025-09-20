<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PetugasController extends Controller
{
    /**
     * Menampilkan daftar semua petugas dengan paginasi.
     */
    public function index()
    {
        $semua_petugas = Petugas::latest()->paginate(10);
        return view('usaha.petugas.index', compact('semua_petugas'));
    }

    /**
     * Menyimpan data petugas baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_petugas' => 'required|string|max:255|unique:petugas,nama_petugas',
        ]);

        Petugas::create([
            'nama_petugas' => $request->nama_petugas,
            'status' => 'Aktif', // Secara default, petugas baru akan berstatus 'Aktif'
        ]);

        return redirect()->route('usaha.petugas.index')->with('success', 'Petugas baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data petugas yang sudah ada, termasuk statusnya.
     */
    public function update(Request $request, Petugas $petuga)
    {
        $request->validate([
            'nama_petugas' => [
                'required',
                'string',
                'max:255',
                Rule::unique('petugas')->ignore($petuga->id),
            ],
            'status' => [
                'required',
                Rule::in(['Aktif', 'Tidak Aktif']),
            ],
        ]);

        $petuga->update($request->all());

        return redirect()->route('usaha.petugas.index')->with('success', 'Data petugas berhasil diperbarui.');
    }

    /**
     * Menghapus data petugas dari database.
     */
    public function destroy(Petugas $petuga)
    {
        if ($petuga->tagihan()->exists()) {
            return redirect()->route('usaha.petugas.index')->with('error', 'Petugas tidak bisa dihapus karena memiliki riwayat di data tagihan.');
        }

        $petuga->delete();

        return redirect()->route('usaha.petugas.index')->with('success', 'Data petugas berhasil dihapus.');
    }

    // Metode 'create', 'show', dan 'edit' tidak diubah karena Anda sudah menyatakan tidak menggunakannya.
    public function create()
    {
        return redirect()->route('usaha.petugas.index');
    }

    public function show(Petugas $petuga)
    {
        return redirect()->route('usaha.petugas.index');
    }

    public function edit(Petugas $petuga)
    {
       return redirect()->route('usaha.petugas.index');
    }
}
