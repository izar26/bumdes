<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // <-- TAMBAHKAN INI

class BeritaController extends Controller
{
    // Method index() tidak berubah...
    public function index(Request $request)
    {
        $beritas = Berita::latest()->get();
        $berita_edit = new Berita();
        if ($request->filled('edit')) {
            $berita_edit = Berita::find($request->edit);
        }
        return view('admin.berita.index', compact('beritas', 'berita_edit'));
    }

    // Method store() diubah untuk menangani gambar
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi gambar
        ]);

        $data = $request->except('gambar');

        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('berita', 'public');
            $data['gambar'] = $path;
        }

        Berita::create($data);

        return redirect()->route('admin.berita.index')
                         ->with('success', 'Berita berhasil ditambahkan.');
    }

    // Method update() diubah untuk menangani gambar
    public function update(Request $request, Berita $berita)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        $data = $request->except('gambar');

        if ($request->hasFile('gambar')) {
            if ($berita->gambar) {
                Storage::disk('public')->delete($berita->gambar);
            }
            $path = $request->file('gambar')->store('berita', 'public');
            $data['gambar'] = $path;
        }

        $berita->update($data);

        return redirect()->route('admin.berita.index')
                         ->with('success', 'Berita berhasil diperbarui.');
    }

    // Method destroy() diubah untuk menghapus gambar
    public function destroy(Berita $berita)
    {
        if ($berita->gambar) {
            Storage::disk('public')->delete($berita->gambar);
        }
        $berita->delete();
        return redirect()->route('admin.berita.index')
                         ->with('success', 'Berita berhasil dihapus.');
    }
}