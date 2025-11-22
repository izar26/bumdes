<?php

namespace App\Http\Controllers\Simpanan;

use App\Models\JenisSimpanan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JenisSimpananController extends Controller
{
    /**
     * Menampilkan daftar semua jenis simpanan.
     */
    public function index()
    {
        $jenisSimpanan = JenisSimpanan::all();
        return view('simpanan.jenis.index', compact('jenisSimpanan'));
    }

    /**
     * Menampilkan form untuk membuat jenis simpanan baru.
     */
    public function create()
    {
        return view('simpanan.jenis.create');
    }

    /**
     * Menyimpan jenis simpanan baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_jenis' => 'required|unique:jenis_simpanans,kode_jenis|max:20',
            'nama_jenis' => 'required|max:100',
            'deskripsi' => 'nullable',
        ]);

        JenisSimpanan::create($request->all());

        return redirect()->route('jenis-simpanan.index')->with('success', 'Jenis Simpanan berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail satu jenis simpanan (optional).
     */
    public function show(JenisSimpanan $jenisSimpanan)
    {
        return view('simpanan.jenis.show', compact('jenisSimpanan'));
    }

    /**
     * Menampilkan form untuk mengedit jenis simpanan.
     */
    public function edit(JenisSimpanan $jenisSimpanan)
    {
        return view('simpanan.jenis.edit', compact('jenisSimpanan'));
    }

    /**
     * Memperbarui jenis simpanan di database.
     */
    public function update(Request $request, JenisSimpanan $jenisSimpanan)
    {
        $request->validate([
            'kode_jenis' => 'required|max:20|unique:jenis_simpanans,kode_jenis,' . $jenisSimpanan->jenis_simpanan_id . ',jenis_simpanan_id',
            'nama_jenis' => 'required|max:100',
            'deskripsi' => 'nullable',
        ]);

        $jenisSimpanan->update($request->all());

        return redirect()->route('simpanan.jenis-simpanan.index')->with('success', 'Jenis Simpanan berhasil diperbarui.');
    }

    /**
     * Menghapus jenis simpanan (pastikan tidak ada rekening yang menggunakannya).
     */
    public function destroy(JenisSimpanan $jenisSimpanan)
    {
        // Peringatan: Tambahkan pengecekan apakah ada rekening yang masih menggunakan jenis ini.
        if ($jenisSimpanan->rekeningSimpanan()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus. Jenis simpanan ini masih digunakan oleh beberapa rekening.');
        }

        $jenisSimpanan->delete();

        return redirect()->route('simpanan.jenis-simpanan.index')->with('success', 'Jenis Simpanan berhasil dihapus.');
    }
}
