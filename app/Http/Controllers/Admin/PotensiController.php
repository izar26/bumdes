<?php
// app/Http/Controllers/PotensiController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Potensi; // <-- Ganti
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PotensiController extends Controller
{
    public function index(Request $request)
    {
        $potensis = Potensi::latest()->get(); // <-- Ganti
        $potensi_edit = new Potensi(); // <-- Ganti
        if ($request->filled('edit')) {
            $potensi_edit = Potensi::find($request->edit); // <-- Ganti
        }
        return view('admin.potensi.index', compact('potensis', 'potensi_edit')); // <-- Ganti
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi gambar
        ]);

        $data = $request->except('gambar'); // Ambil semua data kecuali gambar

        // Jika ada file gambar yang di-upload
        if ($request->hasFile('gambar')) {
            // Simpan gambar & dapatkan path-nya
            $path = $request->file('gambar')->store('potensi', 'public');
            $data['gambar'] = $path;
        }

        Potensi::create($data); // Buat record dengan data baru

        return redirect()->route('admin.potensi.index')->with('success', 'Potensi berhasil ditambahkan.');
    }

    public function update(Request $request, Potensi $potensi)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        $data = $request->except('gambar');

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($potensi->gambar) {
                Storage::disk('public')->delete($potensi->gambar);
            }
            // Simpan gambar baru & dapatkan path-nya
            $path = $request->file('gambar')->store('potensi', 'public');
            $data['gambar'] = $path;
        }

        $potensi->update($data);

        return redirect()->route('admin.potensi.index')->with('success', 'Potensi berhasil diperbarui.');
    }

    public function destroy(Potensi $potensi)
    {
        // Hapus gambar dari storage sebelum menghapus record
        if ($potensi->gambar) {
            Storage::disk('public')->delete($potensi->gambar);
        }
        $potensi->delete();
        return redirect()->route('admin.potensi.index')->with('success', 'Potensi berhasil dihapus.');
    }
}