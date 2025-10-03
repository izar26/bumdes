<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{
    /**
     * Menampilkan daftar semua pelanggan.
     */
    public function index()
    {
        $semua_pelanggan = Pelanggan::all();
        return view('usaha.pelanggan.index', compact('semua_pelanggan'));
    }

    /**
     * Menampilkan form untuk membuat beberapa pelanggan baru (massal).
     */
    public function create()
    {
        return view('usaha.pelanggan.create');
    }

    /**
     * Menyimpan beberapa pelanggan baru dari form massal.
     */
    public function store(Request $request)
    {
        // Validasi input array
        $request->validate([
            'pelanggan' => 'required|array|min:1',
            'pelanggan.*.nama' => 'required|string|max:255',
            'pelanggan.*.alamat' => 'required|string|max:255',
            'pelanggan.*.kontak' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->pelanggan as $data_pelanggan) {
                // Hanya proses jika nama diisi untuk menghindari baris kosong
                if (!empty($data_pelanggan['nama'])) {
                    Pelanggan::create([
                        'nama' => $data_pelanggan['nama'],
                        'alamat' => $data_pelanggan['alamat'],
                        'kontak' => $data_pelanggan['kontak'] ?? null,
                        'status_pelanggan' => 'Aktif'
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan data pelanggan: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('usaha.pelanggan.index')->with('success', 'Berhasil menambahkan ' . count($request->pelanggan) . ' pelanggan baru!');
    }

    /**
     * Menampilkan detail satu pelanggan (tidak digunakan di alur utama, tapi standar resource).
     */
    public function show(Pelanggan $pelanggan)
    {
        return view('usaha.pelanggan.show', compact('pelanggan'));
    }

    /**
     * Menampilkan form untuk mengedit satu pelanggan.
     */
    public function edit(Pelanggan $pelanggan)
    {
        return view('usaha.pelanggan.edit', compact('pelanggan'));
    }

    /**
     * Memperbarui data satu pelanggan.
     */
    public function update(Request $request, Pelanggan $pelanggan)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
             'pelanggan.*.kontak' => 'nullable|string|max:20',
            'status_pelanggan' => 'required|in:Aktif,Nonaktif',
        ]);

        $pelanggan->update($request->all());

        return redirect()->route('usaha.pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Menghapus satu pelanggan.
     */
    public function destroy(Pelanggan $pelanggan)
    {
        try {
            // Cek relasi ke tagihan sebelum menghapus
            if ($pelanggan->tagihan()->exists()) {
                return redirect()->route('usaha.pelanggan.index')->with('error', 'Pelanggan tidak bisa dihapus karena memiliki riwayat tagihan.');
            }
            $pelanggan->delete();
            return redirect()->route('usaha.pelanggan.index')->with('success', 'Data pelanggan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('usaha.pelanggan.index')->with('error', 'Gagal menghapus data pelanggan.');
        }
    }
}
