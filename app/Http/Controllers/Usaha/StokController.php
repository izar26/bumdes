<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stok;
use App\Models\Produk;
use App\Models\UnitUsaha;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StokController extends Controller
{
    /**
     * Display a listing of the stock records (or current product stock list).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Produk::with('stok', 'unitUsaha');

        if ($user->isManajerUnitUsaha()) {
            $managedUnitUsahaIds = $user->unitUsahas->pluck('unit_usaha_id')->toArray();
            $query->whereIn('unit_usaha_id', $managedUnitUsahaIds); // <-- Pastikan ini 'unit_usaha_id'
        }

        if ($user->isAdminBumdes() && $request->filled('unit_usaha_id')) {
            $query->where('unit_usaha_id', $request->unit_usaha_id); // <-- Pastikan ini 'unit_usaha_id'
        }

        $produks = $query->orderBy('nama_produk')->get(); // <-- Perubahan: orderBy('nama_produk')
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();

        return view('usaha.stok.index', compact('produks', 'unitUsahas'));
    }

    public function create()
    {
        $user = Auth::user();
        $query = Produk::query();

        if ($user->isManajerUnitUsaha()) {
            $managedUnitUsahaIds = $user->unitUsahas->pluck('unit_usaha_id')->toArray();
            $query->whereIn('unit_usaha_id', $managedUnitUsahaIds); // <-- Pastikan ini 'unit_usaha_id'
        }

        $produks = $query->orderBy('nama_produk')->get(); // <-- Perubahan: orderBy('nama_produk')
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();

        return view('usaha.stok.create_adjustment', compact('produks', 'unitUsahas'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'produk_id' => 'required|exists:produks,produk_id',
            'unit_usaha_id' => 'required|exists:unit_usahas,unit_usaha_id',
            'jumlah_penyesuaian' => 'required|integer|min:1',
            'jenis_penyesuaian' => 'required|in:tambah,kurang',
            'alasan_penyesuaian' => 'nullable|string|max:255',
            'lokasi_penyimpanan' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $produk = Produk::findOrFail($request->produk_id);

            if ($produk->unit_usaha_id != $request->unit_usaha_id) {
                 DB::rollBack();
            }
            $user = Auth::user();
            if ($user->isManajerUnitUsaha() && !$user->unitUsahas->contains('unit_usaha_id', $request->unit_usaha_id)) {
                DB::rollBack();
            }

            $stok = Stok::firstOrNew([
                'produk_id' => $request->produk_id,
                'unit_usaha_id' => $request->unit_usaha_id,
            ]);

            if (!$stok->exists) {
                $usahajumlah_stok = 0;
            }

            $jumlahPenyesuaian = $usaha->jumlah_penyesuaian;

            if ($request->jenis_penyesuaian === 'tambah') {
                $stok->jumlah_stok += $jumlahPenyesuaian;
            } else {
                if ($stok->jumlah_stok < $jumlahPenyesuaian) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Jumlah penyesuaian pengurangan melebihi stok yang tersedia.');
                }
                $stok->jumlah_stok -= $jumlahPenyesuaian;
            }

            $stok->tanggal_perbarui = now();
            $stok->lokasi_penyimpanan = $request->lokasi_penyimpanan;

            $stok->save();

            DB::commit();

            return redirect()->route('usaha.stok.index')->with('success', 'Penyesuaian stok berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan penyesuaian stok: ' . $e->getMessage())->withInput();
        }
    }
    // ... (edit, update, destroy jika ada)
}
