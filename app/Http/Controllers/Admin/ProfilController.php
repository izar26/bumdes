<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profil;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    /**
     * Menampilkan form edit dengan data yang sudah ada.
     */
    public function edit()
    {
        // Ambil data pertama, atau gagal jika tidak ada.
        // Ini memastikan seeder sudah dijalankan.
        $profil = Profil::firstOrFail();
        return view('admin.profil.edit', compact('profil'));
    }

    /**
     * Menyimpan perubahan data profil.
     */
    public function update(Request $request)
    {
        $profil = Profil::firstOrFail();

        // Validasi input dari form
        $request->validate([
            'nama_desa' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:1024', // Logo maks 1MB
            'deskripsi' => 'required|string',
            'jumlah_penduduk' => 'required|integer',
            'jumlah_kk' => 'required|integer',
            'luas_wilayah' => 'required|string',
            'alamat' => 'required|string',
            'email' => 'required|email',
            'telepon' => 'required|string',
        ]);

        // Ambil semua data dari request, kecuali 'logo'
        $data = $request->except('logo');

        // Logika untuk upload logo baru
        if ($request->hasFile('logo')) {
            // Hapus logo lama dari storage jika ada
            if ($profil->logo) {
                Storage::disk('public')->delete($profil->logo);
            }

            // Simpan logo baru ke folder 'logo-desa' di dalam 'storage/app/public'
            $path = $request->file('logo')->store('logo-desa', 'public');
            $data['logo'] = $path;
        }

        // Update record di database dengan data baru
        $profil->update($data);

        // Kembali ke halaman edit dengan pesan sukses
        return redirect()->route('admin.profil.edit')->with('success', 'Profil Desa berhasil diperbarui.');
    }
}
