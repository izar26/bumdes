<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Pemasok;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;

class PemasokController extends Controller
{
    /**
     * Helper function untuk mengambil daftar Unit Usaha berdasarkan peran user.
     */
    private function getManagedUnitUsahas()
    {
        $user = Auth::user();
        if ($user->hasRole(['admin_bumdes'])) {
            // Admin Bumdes dapat melihat semua unit usaha
            return UnitUsaha::where('status_operasi', 'Aktif')->get();
        }
        // User lain hanya bisa melihat unit usaha yang terhubung dengan mereka
        return $user->unitUsahas()->where('status_operasi', 'Aktif')->get();
    }

    /**
     * Tampilkan daftar pemasok yang dikelola user.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Pemasok::with('unitUsaha');

        // Filter berdasarkan unit usaha yang dikelola, kecuali untuk Admin Bumdes
        if (!$user->hasRole('admin_bumdes')) {
            $managedUnitUsahaIds = $user->unitUsahas->pluck('unit_usaha_id');
            $query->whereIn('unit_usaha_id', $managedUnitUsahaIds);
        }

        $pemasoks = $query->latest()->get();

        return view('usaha.pemasok.index', compact('pemasoks'));
    }

    /**
     * Tampilkan form untuk membuat pemasok baru.
     */
    public function create()
    {
        // Ambil unit usaha yang dikelola user
        $unitUsahas = $this->getManagedUnitUsahas();

        return view('usaha.pemasok.create', compact('unitUsahas'));
    }

    /**
     * Simpan pemasok baru di storage.
     */
    public function store(Request $request)
    {
        $managedUnitUsahaIds = $this->getManagedUnitUsahas()->pluck('unit_usaha_id');

        $request->validate([
            'nama_pemasok' => 'required|string|max:255',
            'no_telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            // Pastikan unit_usaha_id yang dipilih adalah yang dikelola user
            'unit_usaha_id' => ['required', Rule::in($managedUnitUsahaIds)],
        ]);

        Pemasok::create($request->all());

        return redirect()->route('usaha.pemasok.index')
            ->with('success', 'Pemasok baru berhasil ditambahkan.');
    }

    /**
     * Tampilkan form untuk mengedit pemasok.
     */
    public function edit(Pemasok $pemasok)
    {
        // Cek otorisasi: pastikan pemasok dikelola oleh unit usaha user
        $managedUnitUsahaIds = $this->getManagedUnitUsahas()->pluck('unit_usaha_id');
        if (!$managedUnitUsahaIds->contains($pemasok->unit_usaha_id)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk mengedit pemasok ini.');
        }

        // Ambil unit usaha yang dikelola user untuk dropdown
        $unitUsahas = $this->getManagedUnitUsahas();

        return view('usaha.pemasok.edit', compact('pemasok', 'unitUsahas'));
    }

    /**
     * Perbarui pemasok di storage.
     */
    public function update(Request $request, Pemasok $pemasok)
    {
        // Cek otorisasi: pastikan pemasok dikelola oleh unit usaha user
        $managedUnitUsahaIds = $this->getManagedUnitUsahas()->pluck('unit_usaha_id');
        if (!$managedUnitUsahaIds->contains($pemasok->unit_usaha_id)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk memperbarui pemasok ini.');
        }

        $request->validate([
            'nama_pemasok' => 'required|string|max:255',
            'no_telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            // Pastikan unit_usaha_id yang dipilih adalah yang dikelola user
            'unit_usaha_id' => ['required', Rule::in($managedUnitUsahaIds)],
        ]);

        $pemasok->update($request->all());

        return redirect()->route('usaha.pemasok.index')
            ->with('success', 'Data pemasok berhasil diperbarui.');
    }

    /**
     * Hapus pemasok dari storage.
     */
    public function destroy(Pemasok $pemasok)
    {
        // Cek otorisasi: pastikan pemasok dikelola oleh unit usaha user
        $managedUnitUsahaIds = $this->getManagedUnitUsahas()->pluck('unit_usaha_id');
        if (!$managedUnitUsahaIds->contains($pemasok->unit_usaha_id)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk menghapus pemasok ini.');
        }

        $pemasok->delete();

        return redirect()->route('usaha.pemasok.index')
            ->with('success', 'Data pemasok berhasil dihapus.');
    }
}
