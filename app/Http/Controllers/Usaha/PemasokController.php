<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Pemasok;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;

class PemasokController extends Controller
{
    public function index()
    {
        $pemasoks = Pemasok::with('unitUsaha')->latest()->get();
        return view('usaha.pemasok.index', compact('pemasoks'));
    }

    public function create()
    {
        $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        return view('usaha.pemasok.create', compact('unitUsahas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pemasok' => 'required|string|max:255',
            'no_telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'unit_usaha_id' => 'required|exists:unit_usahas,unit_usaha_id',
        ]);

        Pemasok::create($request->all());

        return redirect()->route('usaha.pemasok.index')
                        ->with('success', 'Pemasok baru berhasil ditambahkan.');
    }

    public function edit(Pemasok $pemasok)
    {
        $unitUsahas = UnitUsaha::where('status_operasi', 'Aktif')->get();
        return view('usaha.pemasok.edit', compact('pemasok', 'unitUsahas'));
    }

    public function update(Request $request, Pemasok $pemasok)
    {
        $request->validate([
            'nama_pemasok' => 'required|string|max:255',
            'no_telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'unit_usaha_id' => 'required|exists:unit_usahas,unit_usaha_id',
        ]);

        $pemasok->update($request->all());

        return redirect()->route('usaha.pemasok.index')
                        ->with('success', 'Data pemasok berhasil diperbarui.');
    }

    public function destroy(Pemasok $pemasok)
    {
        $pemasok->delete();

        return redirect()->route('usaha.pemasok.index')
                        ->with('success', 'Data pemasok berhasil dihapus.');
    }
}