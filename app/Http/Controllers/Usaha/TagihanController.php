<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use App\Models\Petugas;
use App\Models\Tarif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TagihanController extends Controller
{
    /**
     * Menampilkan halaman utama untuk daftar & input tagihan (mode massal).
     */
    public function index(Request $request)
    {
        $bulan_terpilih = $request->input('periode_bulan', date('n'));
        $tahun_terpilih = $request->input('periode_tahun', date('Y'));
        $petugas_terpilih = $request->input('petugas_id');

        $periode_sekarang = Carbon::create($tahun_terpilih, $bulan_terpilih, 1);
        $periode_lalu = $periode_sekarang->copy()->subMonth();

        $semua_pelanggan = Pelanggan::where('status_pelanggan', 'Aktif')->orderBy('nama')->get();

        $semua_petugas = Petugas::orderBy('nama_petugas')->get();

        $tagihan_periode_lalu = Tagihan::where('periode_tagihan', $periode_lalu->toDateString())->get()->keyBy('pelanggan_id');

        $query_tagihan_sekarang = Tagihan::where('periode_tagihan', $periode_sekarang->toDateString())->with('petugas');
        if ($petugas_terpilih) {
            $query_tagihan_sekarang->where('petugas_id', $petugas_terpilih);
        }
        $tagihan_periode_sekarang = $query_tagihan_sekarang->get()->keyBy('pelanggan_id');

        $data_tabel = $semua_pelanggan->map(function ($pelanggan) use ($tagihan_periode_lalu, $tagihan_periode_sekarang) {
            $tagihan_lalu = $tagihan_periode_lalu->get($pelanggan->id);
            $tagihan_sekarang = $tagihan_periode_sekarang->get($pelanggan->id);
            return (object) [
                'pelanggan' => $pelanggan,
                'tagihan' => $tagihan_sekarang,
                'meter_awal' => $tagihan_lalu->meter_akhir ?? 0,
            ];
        });

        return view('usaha.tagihan.index', [
            'data_tabel' => $data_tabel,
            'semua_petugas' => $semua_petugas,
            'petugas_terpilih' => $petugas_terpilih,
            'bulan_terpilih' => $bulan_terpilih,
            'tahun_terpilih' => $tahun_terpilih,
        ]);
    }

    /**
     * Menyimpan tagihan secara massal.
     */
    public function simpanSemuaMassal(Request $request)
{
    $request->validate([
        'periode_tagihan' => 'required|date',
        'tagihan' => 'required|array',
        'tagihan.*.meter_awal' => 'required|numeric|min:0',
        'tagihan.*.meter_akhir' => 'nullable|numeric|min:0',
        'petugas_massal_id' => 'required|numeric|exists:petugas,id', // <--- Tambah validasi untuk petugas massal
    ]);

    $periode = $request->periode_tagihan;
    $petugas_id_massal = $request->petugas_massal_id; // <--- Ambil nilai petugas massal

    DB::beginTransaction();
    try {
        foreach ($request->tagihan as $pelanggan_id => $data) {
            // Cek apakah data meter akhir terisi
            if (isset($data['meter_akhir']) && $data['meter_akhir'] !== null && $data['meter_akhir'] !== '') {
                // Tambahkan validasi perbandingan meter_akhir vs meter_awal
                if ($data['meter_akhir'] < $data['meter_awal']) {
                    $pelanggan = Pelanggan::find($pelanggan_id);
                    throw new \Exception('Meter akhir untuk pelanggan ' . ($pelanggan->nama ?? '#' . $pelanggan_id) . ' tidak boleh lebih kecil dari meter awal.');
                }

                // Kalkulasi tagihan bulan ini
                $tagihan_sementara = new Tagihan([
                    'pelanggan_id' => $pelanggan_id,
                    'periode_tagihan' => $periode,
                    'meter_awal' => $data['meter_awal'],
                    'meter_akhir' => $data['meter_akhir']
                ]);
$hasil_kalkulasi = $this->kalkulasiTagihanData($tagihan_sementara, 0, 0);

                // Siapkan data untuk disimpan atau diupdate
                $data_untuk_disimpan = array_merge($hasil_kalkulasi, [
                    'petugas_id' => $petugas_id_massal, // <--- Gunakan petugas massal di sini
                    'meter_awal' => $data['meter_awal'],
                    'meter_akhir' => $data['meter_akhir'],
                    'tanggal_cetak' => now(),
                    'status_pembayaran' => 'Belum Lunas',
                ]);
                unset($data_untuk_disimpan['rincian_dihitung']);

                // Simpan atau perbarui tagihan
                $tagihan = Tagihan::updateOrCreate(
                    ['pelanggan_id' => $pelanggan_id, 'periode_tagihan' => $periode],
                    $data_untuk_disimpan
                );

                // Hapus dan buat ulang rincian
                $tagihan->rincian()->delete();
                $tagihan->rincian()->createMany($hasil_kalkulasi['rincian_dihitung']);
            }
        }
        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
    return redirect()->back()->with('success', 'Semua perubahan tagihan berhasil disimpan!');
}

    /**
     * Memperbarui nilai meter_akhir dari tagihan bulan lalu.
     * Digunakan oleh permintaan AJAX.
     */
    public function updateMeterAkhirLalu(Request $request)
    {
        $request->validate([
            'pelanggan_id' => 'required|numeric|exists:pelanggan,id',
            'meter_akhir' => 'required|numeric|min:0',
        ]);

        $bulan_terpilih = $request->input('periode_bulan', date('n'));
        $tahun_terpilih = $request->input('periode_tahun', date('Y'));
        $periode_sekarang = Carbon::create($tahun_terpilih, $bulan_terpilih, 1);
        $periode_lalu = $periode_sekarang->copy()->subMonth();

        $tagihan_lalu = Tagihan::where('pelanggan_id', $request->pelanggan_id)
                               ->where('periode_tagihan', $periode_lalu->toDateString())
                               ->first();

        if (!$tagihan_lalu) {
            return response()->json(['success' => false, 'message' => 'Tagihan bulan lalu tidak ditemukan.'], 404);
        }

        if ($request->meter_akhir < $tagihan_lalu->meter_awal) {
            return response()->json(['success' => false, 'message' => 'Meter akhir tidak boleh lebih kecil dari meter awal.'], 422);
        }

        $tagihan_lalu->meter_akhir = $request->meter_akhir;
        $tagihan_lalu->save();

        return response()->json(['success' => true, 'message' => 'Meter akhir bulan lalu berhasil diperbarui.']);
    }

    /**
     * Menandai tagihan sebagai lunas.
     */
    public function tandaiLunas(Request $request, Tagihan $tagihan)
    {
        $tagihan->update(['status_pembayaran' => 'Lunas']);
        return back()->with('success', 'Tagihan berhasil ditandai lunas.');
    }

    /**
     * Menandai tagihan-tagihan terpilih sebagai lunas secara massal.
     */
    public function tandaiLunasSelektif(Request $request)
    {
        $request->validate(['tagihan_ids' => 'required|array']);
        Tagihan::whereIn('id', $request->tagihan_ids)->update(['status_pembayaran' => 'Lunas']);
        return back()->with('success', 'Tagihan terpilih berhasil ditandai lunas.');
    }

    /**
     * Menampilkan struk tagihan untuk dicetak.
     */
    public function show($id)
    {
        $tagihan = Tagihan::with('pelanggan', 'petugas')->findOrFail($id);
        $rincian = $tagihan->rincian;
        return view('usaha.tagihan.show', compact('tagihan', 'rincian'));
    }

    /**
     * Mencetak tagihan massal untuk satu bulan.
     */
    public function cetakMassal(Request $request)
    {
        $periode = Carbon::create($request->periode_tahun, $request->periode_bulan, 1);
        $semua_tagihan = Tagihan::where('periode_tagihan', $periode->toDateString())
                                ->with('pelanggan', 'petugas', 'rincian')
                                ->get();
        return view('usaha.tagihan.cetak-massal', compact('semua_tagihan'));
    }

    /**
     * Mencetak tagihan yang dipilih secara selektif.
     */
    public function cetakSelektif(Request $request)
    {
        $request->validate(['tagihan_ids' => 'required|array']);
        $semua_tagihan = Tagihan::whereIn('id', $request->tagihan_ids)
                                ->with('pelanggan', 'petugas', 'rincian')
                                ->get();
        return view('usaha.tagihan.cetak-massal', compact('semua_tagihan'));
    }

    /**
     * Menghapus tagihan.
     */
    public function destroy(Tagihan $tagihan)
    {
        $tagihan->delete();
        return back()->with('success', 'Tagihan berhasil dihapus.');
    }

    /**
     * [PRIVATE METHOD] Otak dari semua kalkulasi tagihan.
     * Sekarang menerima parameter tunggakan dan denda.
     */
 private function kalkulasiTagihanData(Tagihan $tagihan, $tunggakan_manual = 0)
{
    $semua_tarif = Tarif::all();
    $total_pemakaian_real = max(0, $tagihan->meter_akhir - $tagihan->meter_awal);
    $rincian_dihitung = [];
    $subtotal_pemakaian = 0;

    // ===== Hitung tarif pemakaian =====
    $tarif_pemakaian = $semua_tarif->where('jenis_tarif', 'pemakaian')
        ->sortBy('batas_bawah')
        ->values();

    $sisa = $total_pemakaian_real;
    $firstBlock = true;

    foreach ($tarif_pemakaian as $tarif) {
        $bawah = isset($tarif->batas_bawah) ? (int)$tarif->batas_bawah : 0;
        $atas = isset($tarif->batas_atas) ? (int)$tarif->batas_atas : null;

        $kapasitas = $atas !== null ? ($atas - ($bawah > 0 ? ($bawah - 1) : 0)) : INF;

        if ($firstBlock) {
            // Blok pertama: minimal charge penuh
            if ($total_pemakaian_real > 0) {
                if ($total_pemakaian_real <= $kapasitas) {
                    $kuantitas = $kapasitas; // tetap hitung penuh
                    $sisa = 0;
                } else {
                    $kuantitas = min($sisa, $kapasitas);
                    $sisa -= $kuantitas;
                }
            } else {
                $kuantitas = 0;
            }
            $firstBlock = false;
        } else {
            if ($sisa <= 0) break;
            $kuantitas = min($sisa, $kapasitas);
            $sisa -= $kuantitas;
        }

        if ($kuantitas > 0) {
            $subtotal = $kuantitas * $tarif->harga;
            $rincian_dihitung[] = [
                'deskripsi' => $tarif->deskripsi,
                'kuantitas' => (int)$kuantitas,
                'harga_satuan' => $tarif->harga,
                'subtotal' => $subtotal,
            ];
            $subtotal_pemakaian += $subtotal;
        }
    }

    // ===== Biaya tetap =====
    $biaya_lainnya = 0;
    $tarif_biaya_tetap = $semua_tarif->where('jenis_tarif', 'biaya_tetap');
    foreach ($tarif_biaya_tetap as $biaya) {
        $biaya_lainnya += $biaya->harga;
        $rincian_dihitung[] = [
            'deskripsi' => $biaya->deskripsi,
            'kuantitas' => 1,
            'harga_satuan' => $biaya->harga,
            'subtotal' => $biaya->harga,
        ];
    }

    // ===== Hitung denda dinamis =====
    // asumsinya ada field $tagihan->bulan_tagihan (format YYYY-MM misalnya)
    $denda_per_bulan = 5000;
    $denda = 0;

    if ($tagihan->bulan_tagihan) {
        $bulanTagihan = \Carbon\Carbon::parse($tagihan->bulan_tagihan)->startOfMonth();
        $bulanSekarang = \Carbon\Carbon::now()->startOfMonth();

        $selisihBulan = $bulanTagihan->diffInMonths($bulanSekarang, false);

        if ($selisihBulan > 0 && !$tagihan->sudah_dibayar) {
            $denda = $selisihBulan * $denda_per_bulan;
            $rincian_dihitung[] = [
                'deskripsi' => 'Denda Keterlambatan',
                'kuantitas' => $selisihBulan,
                'harga_satuan' => $denda_per_bulan,
                'subtotal' => $denda,
            ];
        }
    }

    // ===== Tambahkan tunggakan manual (kalau ada) =====
    if ($tunggakan_manual > 0) {
        $rincian_dihitung[] = [
            'deskripsi' => 'Tunggakan Bulan Sebelumnya',
            'kuantitas' => 1,
            'harga_satuan' => $tunggakan_manual,
            'subtotal' => $tunggakan_manual,
        ];
    }

    // ===== Total =====
    $total_harus_dibayar = $subtotal_pemakaian + $biaya_lainnya + $tunggakan_manual + $denda;

    return [
        'total_pemakaian_m3' => $total_pemakaian_real,
        'subtotal_pemakaian' => $subtotal_pemakaian,
        'biaya_lainnya' => $biaya_lainnya,
        'denda' => $denda,
        'tunggakan' => $tunggakan_manual,
        'total_harus_dibayar' => $total_harus_dibayar,
        'rincian_dihitung' => $rincian_dihitung,
    ];
}


public function batalkanTagihan(Tagihan $tagihan)
{
    $tagihan->update(['status_pembayaran' => 'Batal']);
    return back()->with('success', 'Tagihan berhasil dibatalkan dan tidak akan masuk dalam perhitungan rekap.');
}

public function rekap(Request $request)
{
    $bulan_terpilih = $request->input('periode_bulan', date('n'));
    $tahun_terpilih = $request->input('periode_tahun', date('Y'));

    $periode = \Carbon\Carbon::create($tahun_terpilih, $bulan_terpilih, 1)->toDateString();

    // Modifikasi di sini: hanya ambil tagihan yang bukan 'Batal'
    $tagihan_bulan_ini = Tagihan::where('periode_tagihan', $periode)
                                ->where('status_pembayaran', '!=', 'Batal')
                                ->get();

    $total_pemasukan = $tagihan_bulan_ini->where('status_pembayaran', 'Lunas')->sum('total_harus_dibayar');
    $total_belum_lunas = $tagihan_bulan_ini->where('status_pembayaran', 'Belum Lunas')->sum('total_harus_dibayar');

    return view('usaha.tagihan.rekap', [
        'bulan_terpilih' => $bulan_terpilih,
        'tahun_terpilih' => $tahun_terpilih,
        'total_pemasukan' => $total_pemasukan,
        'total_belum_lunas' => $total_belum_lunas,
    ]);
}
}
