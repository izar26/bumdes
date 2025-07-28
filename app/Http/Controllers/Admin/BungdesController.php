<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bungdes; // Pastikan model Bungdes di-import
use Illuminate\Support\Facades\Validator; // Untuk validasi
use Carbon\Carbon; // Untuk tanggal, jika diperlukan

class BungdesController extends Controller
{
    public function index()
    {
        $bungdeses = Bungdes::first();
        return view('admin.manajemen_data.bungdes.index', compact('bungdeses'));
    }

    public function update(Request $request)
    {
        $bungdeses = Bungdes::first();

        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_bumdes' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'tanggal_berdiri' => 'nullable|date',
            'deskripsi' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'struktur_organisasi' => 'nullable|mimes:jpeg,png,jpg,|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aset_usaha' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Jika ada file logo baru di-upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $bungdeses->logo = $logoPath;
        }

        if ($request->hasFIle('struktur_organisasi')){
            $struktur_organisasi_path = $request->file('struktur_organisasi')->store('struktur_organisasi', 'public');
            $bungdeses->struktur_organisasi = $struktur_organisasi_path;
        }

        // Update data
        $bungdeses->update([
            'nama_bumdes' => $request->nama_bumdes,
            'alamat' => $request->alamat,
            'tanggal_berdiri' => $request->tanggal_berdiri,
            'deskripsi' => $request->deskripsi,
            'telepon' => $request->telepon,
            'email' => $request->email,
            'website' => $request->website,
            'aset_usaha' => $request->aset_usaha,
        ]);

        if ($request->hasFile('logo')) {
            $bungdeses->save();
        }
        if ($request->hasFile('struktur_organisasi')){
            $bungdeses->save();
        }

        return redirect()->route('admin.bungdes.index')->with('success', 'Profil BUMDes berhasil diperbarui.');
    }
}
