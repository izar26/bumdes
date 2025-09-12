<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Tarif;
use Illuminate\Http\Request;

class TarifController extends Controller
{
    private $jenisTarifOptions = [
        'pemakaian' => 'Tarif Pemakaian Air',
        'biaya_tetap' => 'Biaya Tetap (Abonemen)',
        'denda' => 'Denda Keterlambatan',
    ];

    public function index()
    {
        $semua_tarif = Tarif::all()->groupBy('jenis_tarif');
        return view('usaha.tarif.index', [
            'semua_tarif' => $semua_tarif,
            'jenisTarifOptions' => $this->jenisTarifOptions
        ]);
    }

    public function create()
    {
        return view('usaha.tarif.create', ['jenisTarifOptions' => $this->jenisTarifOptions]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_tarif' => 'required|in:pemakaian,biaya_tetap,denda',
            'deskripsi' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'batas_bawah' => 'nullable|required_if:jenis_tarif,pemakaian|integer|min:0',
            'batas_atas' => 'nullable|integer|min:0|gte:batas_bawah',
        ]);

        Tarif::create($validated);

        return redirect()->route('usaha.tarif.index')->with('success', 'Aturan tarif baru berhasil ditambahkan.');
    }

    public function edit(Tarif $tarif)
    {
        return view('usaha.tarif.edit', [
            'tarif' => $tarif,
            'jenisTarifOptions' => $this->jenisTarifOptions
        ]);
    }

    public function update(Request $request, Tarif $tarif)
    {
        $validated = $request->validate([
            'jenis_tarif' => 'required|in:pemakaian,biaya_tetap,denda',
            'deskripsi' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'batas_bawah' => 'nullable|required_if:jenis_tarif,pemakaian|integer|min:0',
            'batas_atas' => 'nullable|integer|min:0|gte:batas_bawah',
        ]);

        $tarif->update($validated);

        return redirect()->route('usaha.tarif.index')->with('success', 'Aturan tarif berhasil diperbarui.');
    }

    public function destroy(Tarif $tarif)
    {
        try {
            $tarif->delete();
            return redirect()->route('usaha.tarif.index')->with('success', 'Aturan tarif berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('usaha.tarif.index')->with('error', 'Gagal menghapus tarif. Pastikan tidak ada data terkait.');
        }
    }
}
