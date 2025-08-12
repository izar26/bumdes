<?php

namespace App\Http\Controllers\Admin\Aset;

use App\Models\AsetBUMDes;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;

class AsetBUMDesController extends Controller
{
    /**
     * Menampilkan daftar aset.
     */
    public function index(): View
    {
        $aset = AsetBUMDes::with('unitUsaha')->orderBy('created_at', 'desc')->paginate(10);
        return view('aset.index', compact('aset'));
    }

    /**
     * Menampilkan form untuk membuat aset baru.
     */
    public function create(): View
    {
        $unitUsahas = UnitUsaha::all();
        $metodePenyusutan = ['Garis Lurus' => 'Garis Lurus', 'Saldo Menurun' => 'Saldo Menurun'];
        return view('aset.create', compact('unitUsahas', 'metodePenyusutan'));
    }

    /**
     * Menyimpan aset baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'nomor_inventaris' => 'required|string|max:255|unique:aset_bumdes,nomor_inventaris',
            'nama_aset' => 'required|string|max:255',
            'jenis_aset' => 'required|string|max:100',
            'nilai_perolehan' => 'required|numeric|min:0',
            'tanggal_perolehan' => 'required|date',
            'kondisi' => 'required|string|in:Baik,Rusak Ringan,Rusak Berat',
            'lokasi' => 'nullable|string|max:255',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id',
            // --- Kolom baru untuk penyusutan ---
            'metode_penyusutan' => 'required|string|in:Garis Lurus,Saldo Menurun',
            'masa_manfaat' => 'required|integer|min:1',
            'nilai_residu' => 'nullable|numeric|min:0',
        ]);

        try {
            // Hitung nilai awal saat ini (nilai buku awal)
            $nilaiSaatIni = $validatedData['nilai_perolehan'];
            $validatedData['nilai_saat_ini'] = $nilaiSaatIni;

            AsetBUMDes::create($validatedData);
            return redirect()->route('bumdes.aset.index')->with('success', 'Aset berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan aset: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail aset.
     */
    public function show(AsetBUMDes $aset): View
    {
        return view('aset.show', compact('aset'));
    }

    /**
     * Menampilkan form untuk mengedit aset.
     */
    public function edit(AsetBUMDes $aset): View
    {
        $unitUsahas = UnitUsaha::all();
        $metodePenyusutan = ['Garis Lurus' => 'Garis Lurus', 'Saldo Menurun' => 'Saldo Menurun'];
        return view('aset.edit', compact('aset', 'unitUsahas', 'metodePenyusutan'));
    }

    /**
     * Memperbarui aset di database.
     */
    public function update(Request $request, AsetBUMDes $aset): RedirectResponse
    {
        $validatedData = $request->validate([
            'nomor_inventaris' => 'required|string|max:255|unique:aset_bumdes,nomor_inventaris,' . $aset->aset_id . ',aset_id',
            'nama_aset' => 'required|string|max:255',
            'jenis_aset' => 'required|string|max:100',
            'nilai_perolehan' => 'required|numeric|min:0',
            'tanggal_perolehan' => 'required|date',
            'kondisi' => 'required|string|in:,Baru,Baik,Rusak Ringan,Rusak Berat',
            'lokasi' => 'nullable|string|max:255',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id',
            'metode_penyusutan' => 'required|string|in:Garis Lurus,Saldo Menurun',
            'masa_manfaat' => 'required|integer|min:1',
            'nilai_residu' => 'nullable|numeric|min:0',
        ]);

        try {
            $aset->update($validatedData);
            return redirect()->route('bumdes.aset.index')->with('success', 'Aset berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui aset: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus aset dari database.
     */
    public function destroy(AsetBUMDes $aset): RedirectResponse
    {
        try {
            $aset->delete();
            return redirect()->route('bumdes.aset.index')->with('success', 'Aset berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus aset: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan halaman penyusutan.
     * Mengimplementasikan logika perhitungan penyusutan.
     */
    public function penyusutan(): View
    {
        // Ambil semua aset yang memiliki masa manfaat
        $asets = AsetBUMDes::whereNotNull('masa_manfaat')->get();

        // Hitung nilai buku saat ini untuk setiap aset
        $asets->each(function ($aset) {
            $tahunSekarang = now()->year;
            $tahunPerolehan = $aset->tanggal_perolehan->year;
            $umurAset = $tahunSekarang - $tahunPerolehan;

            if ($aset->metode_penyusutan == 'Garis Lurus') {
                $penyusutanPerTahun = ($aset->nilai_perolehan - $aset->nilai_residu) / $aset->masa_manfaat;
                $akumulasiPenyusutan = $penyusutanPerTahun * $umurAset;
                $nilaiSaatIni = $aset->nilai_perolehan - $akumulasiPenyusutan;

                // Pastikan nilai buku tidak di bawah nilai residu
                $aset->nilai_saat_ini = max($nilaiSaatIni, $aset->nilai_residu);
                $aset->save(); // Simpan nilai saat ini ke database
            }
        });

        return view('aset.penyusutan', compact('asets'));
    }

    /**
     * Menampilkan halaman pemeliharaan.
     */
    public function pemeliharaan(): View
    {
        $asets = AsetBUMDes::with('unitUsaha')->get();

        return view('aset.pemeliharaan', compact('asets'));
    }
}
