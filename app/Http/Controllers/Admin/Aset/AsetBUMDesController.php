<?php

namespace App\Http\Controllers\Admin\Aset;

use App\Models\AsetBUMDes;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\User;

class AsetBUMDesController extends Controller
{
    public function index(): View
    {
        $aset = AsetBUMDes::orderBy('created_at', 'desc')->paginate(10);
        return view('aset.index', compact('aset'));
    }

    public function create(): View
    {
        $users = User::all();
        return view('aset.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_aset' => 'required|string|max:255',
            'jenis_aset' => 'required|string|max:100',
            'nilai_perolehan' => 'required|numeric|min:0',
            'tanggal_perolehan' => 'required|date',
            'kondisi' => 'required|string|max:100',
            'lokasi' => 'nullable|string|max:255',
            'bungdes_id' => 'required|exists:bungdeses,bungdes_id',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id',
            'penanggung_jawab' => 'nullable|exists:users,id', // Diubah: merujuk ke users.id
        ]);

        AsetBUMDes::create($request->all());

        return redirect()->route('bumdes.aset.index')->with('success', 'Aset berhasil ditambahkan!');
    }

    public function show(AsetBUMDes $aset): View
    {
        return view('aset.show', compact('aset'));
    }

    public function edit(AsetBUMDes $aset): View
    {
        $users = User::all();
        return view('aset.edit', compact('aset', 'users'));
    }

    public function update(Request $request, AsetBUMDes $aset): RedirectResponse
    {
        $request->validate([
            'nama_aset' => 'required|string|max:255',
            'jenis_aset' => 'required|string|max:100',
            'nilai_perolehan' => 'required|numeric|min:0',
            'tanggal_perolehan' => 'required|date',
            'kondisi' => 'required|string|max:100',
            'lokasi' => 'nullable|string|max:255',
            'bungdes_id' => 'required|exists:bungdeses,bungdes_id',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id',
            'penanggung_jawab' => 'nullable|exists:users,id', // Diubah: merujuk ke users.id
        ]);

        $aset->update($request->all());

        return redirect()->route('bumdes.aset.index')->with('success', 'Aset berhasil diperbarui!');
    }

    public function destroy(AsetBUMDes $aset): RedirectResponse
    {
        $aset->delete();
        return redirect()->route('bumdes.aset.index')->with('success', 'Aset berhasil dihapus!');
    }

    public function penyusutan(): View
    {
        return view('aset.penyusutan');
    }

    public function pemeliharaan(): View
    {
        return view('aset.pemeliharaan');
    }
}
