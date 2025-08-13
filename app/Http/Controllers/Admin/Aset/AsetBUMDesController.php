<?php

namespace App\Http\Controllers\Admin\Aset;

use App\Models\AsetBUMDes;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Carbon\Carbon; // PERBAIKAN: Menambahkan import Carbon

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
            'kondisi' => 'required|string|in:Baru,Baik,Rusak Ringan,Rusak Berat',
            'lokasi' => 'nullable|string|max:255',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id',
            'metode_penyusutan' => 'required|string|in:Garis Lurus,Saldo Menurun',
            'masa_manfaat' => 'required|integer|min:1',
            'nilai_residu' => 'nullable|numeric|min:0',
        ]);

        try {
            // PERBAIKAN: Set nilai saat ini sama dengan nilai perolehan terlebih dahulu
            $validatedData['nilai_saat_ini'] = $validatedData['nilai_perolehan'];

            $aset = AsetBUMDes::create($validatedData);

            // PENAMBAHAN: Setelah aset dibuat, langsung hitung nilai buku saat ini
            // Ini untuk mengatasi jika tanggal perolehan adalah di masa lalu.
            $this->calculateAndUpdateCurrentValue($aset);

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
        // PENAMBAHAN: Panggil fungsi kalkulasi untuk memastikan data yang ditampilkan adalah yang terbaru
        $this->calculateAndUpdateCurrentValue($aset);
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
            'kondisi' => 'required|string|in:Baru,Baik,Rusak Ringan,Rusak Berat',
            'lokasi' => 'nullable|string|max:255',
            'unit_usaha_id' => 'nullable|exists:unit_usahas,unit_usaha_id',
            'metode_penyusutan' => 'required|string|in:Garis Lurus,Saldo Menurun',
            'masa_manfaat' => 'required|integer|min:1',
            'nilai_residu' => 'nullable|numeric|min:0',
        ]);

        try {
            $aset->update($validatedData);

            // PERBAIKAN: Panggil metode kalkulasi terpusat setelah update
            $this->calculateAndUpdateCurrentValue($aset);

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
     * Menampilkan halaman penyusutan dan menjalankan kalkulasi.
     */
    public function penyusutan(): View
    {
        $asets = AsetBUMDes::whereNotNull('masa_manfaat')->get();

        // PERBAIKAN: Gunakan metode terpusat untuk menghitung dan menyimpan
        $asets->each(function ($aset) {
            $this->calculateAndUpdateCurrentValue($aset);
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


    /**
     * PENAMBAHAN: Metode terpusat untuk menghitung dan menyimpan nilai buku aset.
     * Metode ini dipanggil dari store(), update(), show(), dan penyusutan().
     *
     * @param AsetBUMDes $aset
     * @return void
     */
    private function calculateAndUpdateCurrentValue(AsetBUMDes $aset): void
    {
        $tahunSekarang = now()->year;
        $tahunPerolehan = Carbon::parse($aset->tanggal_perolehan)->year;
        $umurAset = max(0, $tahunSekarang - $tahunPerolehan);
        $nilaiBuku = $aset->nilai_perolehan;

        // Jangan lakukan apa-apa jika umur aset masih 0 atau kurang
        if ($umurAset <= 0) {
            $aset->nilai_saat_ini = $aset->nilai_perolehan;
            $aset->save();
            return;
        }

        if ($aset->metode_penyusutan == 'Garis Lurus') {
            $penyusutanPerTahun = ($aset->nilai_perolehan - $aset->nilai_residu) / $aset->masa_manfaat;
            $akumulasiPenyusutan = $penyusutanPerTahun * $umurAset;
            $nilaiBuku = $aset->nilai_perolehan - $akumulasiPenyusutan;

        } elseif ($aset->metode_penyusutan == 'Saldo Menurun') {
            // Menggunakan metode Double Declining Balance
            $tarifPenyusutan = (1 / $aset->masa_manfaat) * 2;
            $nilaiBukuSementara = $aset->nilai_perolehan;

            for ($i = 0; $i < $umurAset; $i++) {
                $penyusutanTahunan = $nilaiBukuSementara * $tarifPenyusutan;
                $nilaiBukuSementara -= $penyusutanTahunan;

                // Hentikan penyusutan jika sudah mencapai nilai residu
                if ($nilaiBukuSementara < $aset->nilai_residu) {
                    break;
                }
            }
            $nilaiBuku = $nilaiBukuSementara;
        }

        // Pastikan nilai buku tidak pernah di bawah nilai residu
        $aset->nilai_saat_ini = max($nilaiBuku, $aset->nilai_residu ?? 0);
        $aset->save(); // Simpan nilai yang sudah dihitung
    }
}
