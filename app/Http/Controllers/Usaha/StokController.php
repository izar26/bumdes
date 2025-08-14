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
     * Tampilkan daftar stok.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Produk::with('stok', 'unitUsaha');

        // Filter untuk manajer unit usaha
        if ($user->isManajerUnitUsaha()) {
            $managedUnitUsahaIds = $user->unitUsahas->pluck('unit_usaha_id')->toArray();
            $query->whereIn('unit_usaha_id', $managedUnitUsahaIds);
        }
        if ($user->isAdminUnitUsaha()) {
            $managedUnitUsahaIds = $user->unitUsahas->pluck('unit_usaha_id')->toArray();
            $query->whereIn('unit_usaha_id', $managedUnitUsahaIds);
        }

        // Filter untuk admin bumdes
        if ($user->isAdminBumdes()) {
            if ($request->filled('unit_usaha_id')) {
                $query->where('unit_usaha_id', $request->unit_usaha_id);
            } else {
                $managedUnitUsahaIds = $user->unitUsahas->pluck('unit_usaha_id')->toArray();
                $query->whereIn('unit_usaha_id', $managedUnitUsahaIds);
            }
        }

        $produks = $query->orderBy('nama_produk')->get();

        // Unit usaha hanya yang dikelola user
        $unitUsahas = $user->unitUsahas()->orderBy('nama_unit')->get();

        return view('usaha.stok.index', compact('produks', 'unitUsahas'));
    }

    /**
     * Form tambah penyesuaian stok.
     */
    public function create()
    {
        $user = Auth::user();

        // Produk hanya dari unit usaha yang dikelola user
        $managedUnitUsahaIds = $user->unitUsahas->pluck('unit_usaha_id')->toArray();
        $produks = Produk::whereIn('unit_usaha_id', $managedUnitUsahaIds)
            ->orderBy('nama_produk')
            ->get();

        // Unit usaha hanya yang dikelola user
        $unitUsahas = $user->unitUsahas()->orderBy('nama_unit')->get();

        return view('usaha.stok.create_adjustment', compact('produks', 'unitUsahas'));
    }

    /**
     * Simpan penyesuaian stok.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'produk_id'           => 'required|exists:produks,produk_id',
            'unit_usaha_id'       => 'required|exists:unit_usahas,unit_usaha_id',
            'jumlah_penyesuaian'  => 'required|integer|min:1',
            'jenis_penyesuaian'   => 'required|in:tambah,kurang',
            'alasan_penyesuaian'  => 'nullable|string|max:255',
            'lokasi_penyimpanan'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $produk = Produk::findOrFail($request->produk_id);

            // Cek apakah produk sesuai dengan unit usaha
            if ($produk->unit_usaha_id != $request->unit_usaha_id) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Produk tidak sesuai dengan unit usaha.')->withInput();
            }

            $user = Auth::user();

            // Cek akses untuk manajer unit usaha
            if ($user->isManajerUnitUsaha() && !$user->unitUsahas->contains('unit_usaha_id', $request->unit_usaha_id)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke unit usaha ini.')->withInput();
            }

            $stok = Stok::firstOrNew([
                'produk_id' => $request->produk_id,
                'unit_usaha_id' => $request->unit_usaha_id,
            ]);

            if (!$stok->exists) {
                $stok->jumlah_stok = 0;
            }

            $jumlahPenyesuaian = $request->jumlah_penyesuaian;

            if ($request->jenis_penyesuaian === 'tambah') {
                $stok->jumlah_stok += $jumlahPenyesuaian;
            } else {
                if ($stok->jumlah_stok < $jumlahPenyesuaian) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Jumlah penyesuaian pengurangan melebihi stok yang tersedia.')->withInput();
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
}
