<?php
namespace App\Http\Controllers;

use App\Models\Bungdes;
use Illuminate\Http\Request;

class BungdesController extends Controller
{
    public function index()
    {
        $bungdeses = Bungdes::latest()->get();
        return view('bungdes.index', compact('bungdeses'));
    }

    public function create()
    {
        return view('bungdes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bungdes' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'desa_id' => 'nullable|integer',
            'tanggal_berdiri' => 'nullable|date',
            'deskripsi' => 'nullable|string',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'user_id' => 'nullable|exists:users,user_id',
        ]);

        Bungdes::create($request->all());

        return redirect()->route('bungdes.index')->with('success', 'Data BUMDes berhasil ditambahkan!');
    }

    public function edit(Bungdes $bungdes)
    {
        return view('bungdes.edit', compact('bungdes'));
    }

    public function update(Request $request, Bungdes $bungdes)
    {
        $request->validate([
            'nama_bungdes' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'desa_id' => 'nullable|integer',

        ]);

        $bungdes->update($request->all());

        return redirect()->route('bungdes.index')->with('success', 'Data BUMDes berhasil diperbarui!');
    }

    public function destroy(Bungdes $bungdes)
    {
        $bungdes->delete();

        return redirect()->route('bungdes.index')->with('success', 'Data BUMDes berhasil dihapus!');
    }
}
