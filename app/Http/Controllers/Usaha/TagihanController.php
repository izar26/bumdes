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

        $periode_sekarang = Carbon::create($tahun_terpilih, $bulan_terpilih, 1);
        $periode_lalu = $periode_sekarang->copy()->subMonth();

        $semua_pelanggan = Pelanggan::where('status_pelanggan', 'Aktif')->orderBy('nama')->get();

        // Mendapatkan petugas yang AKTIF. Jika tidak ada atau lebih dari satu, kembalikan error.
        $petugas_aktif = Petugas::where('status', 'Aktif')->first();
        if (Petugas::where('status', 'Aktif')->count() > 1) {
             return redirect()->back()->with('error', 'Terdapat lebih dari satu petugas yang berstatus Aktif. Mohon tetapkan hanya satu petugas.');
        }

        if (!$petugas_aktif) {
            return redirect()->back()->with('error', 'Tidak ada petugas yang berstatus Aktif. Mohon tetapkan satu petugas.');
        }

        $tagihan_periode_lalu = Tagihan::where('periode_tagihan', $periode_lalu->toDateString())->get()->keyBy('pelanggan_id');
        $tagihan_periode_sekarang = Tagihan::where('periode_tagihan', $periode_sekarang->toDateString())->with('petugas')->get()->keyBy('pelanggan_id');

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
            'petugas_aktif' => $petugas_aktif,
            'bulan_terpilih' => $bulan_terpilih,
            'tahun_terpilih' => $tahun_terpilih,
        ]);
    }

    /**
     * Menyimpan SEMUA perubahan dari mode edit massal.
     */
    public function simpanSemuaMassal(Request $request)
    {
        // Validasi tanpa petugas_id di form
        $request->validate([
            'periode_tagihan' => 'required|date',
            'tagihan' => 'required|array',
            'tagihan.*.meter_awal' => 'required|numeric|min:0',
            'tagihan.*.meter_akhir' => 'nullable|numeric|min:0'
        ]);

        // Mendapatkan petugas aktif secara otomatis dari database
        $petugas_aktif = Petugas::where('status', 'Aktif')->first();
        if (!$petugas_aktif) {
            return redirect()->back()->with('error', 'Tidak ada petugas yang berstatus Aktif.');
        }
        $petugas_id = $petugas_aktif->id;

        $periode = $request->periode_tagihan;

        DB::beginTransaction();
        try {
            foreach ($request->tagihan as $pelanggan_id => $data) {
                if (isset($data['meter_akhir']) && $data['meter_akhir'] !== null && $data['meter_akhir'] !== '') {
                    if ($data['meter_akhir'] < $data['meter_awal']) {
                        $pelanggan = Pelanggan::find($pelanggan_id);
                        throw new \Exception('Meter akhir untuk pelanggan ' . ($pelanggan->nama ?? '#'.$pelanggan_id) . ' tidak boleh lebih kecil dari meter awal.');
                    }

                    $tagihan_sementara = new Tagihan(['pelanggan_id' => $pelanggan_id, 'periode_tagihan' => $periode, 'meter_awal' => $data['meter_awal'], 'meter_akhir' => $data['meter_akhir']]);
                    $hasil_kalkulasi = $this->kalkulasiTagihanData($tagihan_sementara);

                    $data_untuk_disimpan = array_merge($hasil_kalkulasi, [
                        'petugas_id' => $petugas_id,
                        'meter_awal' => $data['meter_awal'],
                        'meter_akhir' => $data['meter_akhir'],
                        'tanggal_cetak' => now(),
                        'status_pembayaran' => 'Belum Lunas',
                    ]);
                    unset($data_untuk_disimpan['rincian_dihitung']);

                    $tagihan = Tagihan::updateOrCreate(
                        ['pelanggan_id' => $pelanggan_id, 'periode_tagihan' => $periode],
                        $data_untuk_disimpan
                    );

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
     */
    /**
 * [PRIVATE METHOD] Otak dari semua kalkulasi tagihan.
 */
private function kalkulasiTagihanData(Tagihan $tagihan)
{
    $semua_tarif = Tarif::all();
    $total_pemakaian = $tagihan->meter_akhir - $tagihan->meter_awal;
    $rincian_dihitung = [];
    $subtotal_pemakaian = 0;

    $tarif_pemakaian = $semua_tarif->where('jenis_tarif', 'pemakaian')
                                   ->sortBy('batas_bawah')
                                   ->values();

    $sisa_pemakaian = $total_pemakaian;

    foreach ($tarif_pemakaian as $index => $tarif) {
        $batas_atas = $tarif->batas_atas ?? INF;

        // BLOK PERTAMA: WAJIB BAYAR 5 m³
        if ($index === 0) {
            $pemakaian_di_blok = 5;
            $subtotal = $pemakaian_di_blok * $tarif->harga;

            $rincian_dihitung[] = [
                'deskripsi'     => $tarif->deskripsi,
                'kuantitas'     => $pemakaian_di_blok,
                'harga_satuan'  => $tarif->harga,
                'subtotal'      => $subtotal,
            ];
            $subtotal_pemakaian += $subtotal;

            // sisanya dikurangi 5, walaupun total pemakaian < 5 tetap dihitung 0
            $sisa_pemakaian = max(0, $total_pemakaian - 5);
            continue;
        }

        // BLOK BERIKUTNYA
        if ($sisa_pemakaian <= 0) break;

        $rentang_blok = $batas_atas - $tarif->batas_bawah; // TANPA +1
        $pemakaian_di_blok = min($sisa_pemakaian, $rentang_blok);

        if ($pemakaian_di_blok > 0) {
            $subtotal = $pemakaian_di_blok * $tarif->harga;

            $rincian_dihitung[] = [
                'deskripsi'     => $tarif->deskripsi,
                'kuantitas'     => $pemakaian_di_blok,
                'harga_satuan'  => $tarif->harga,
                'subtotal'      => $subtotal,
            ];
            $subtotal_pemakaian += $subtotal;
            $sisa_pemakaian -= $pemakaian_di_blok;
        }
    }

    // Biaya tetap (abonemen, admin, dll)
    $biaya_lainnya = 0;
    $tarif_biaya_tetap = $semua_tarif->where('jenis_tarif', 'biaya_tetap');
    foreach ($tarif_biaya_tetap as $biaya) {
        $biaya_lainnya += $biaya->harga;
        $rincian_dihitung[] = [
            'deskripsi'     => $biaya->deskripsi,
            'kuantitas'     => 1,
            'harga_satuan'  => $biaya->harga,
            'subtotal'      => $biaya->harga,
        ];
    }

    // Cek tunggakan & denda
    $tunggakan = 0;
    $denda = 0;
    $tagihan_terakhir = Tagihan::where('pelanggan_id', $tagihan->pelanggan_id)
        ->where('periode_tagihan', '<', $tagihan->periode_tagihan)
        ->latest('periode_tagihan')
        ->first();

    if ($tagihan_terakhir && $tagihan_terakhir->status_pembayaran == 'Belum Lunas') {
        $tunggakan = $tagihan_terakhir->total_harus_dibayar;
        $tarif_denda = $semua_tarif->where('jenis_tarif', 'denda')->first();
        if ($tarif_denda) {
            $denda = $tarif_denda->harga;
            $rincian_dihitung[] = [
                'deskripsi'     => $tarif_denda->deskripsi,
                'kuantitas'     => 1,
                'harga_satuan'  => $denda,
                'subtotal'      => $denda,
            ];
        }
    }

    return [
        'total_pemakaian_m3'  => $total_pemakaian,
        'subtotal_pemakaian'  => $subtotal_pemakaian,
        'biaya_lainnya'       => $biaya_lainnya,
        'denda'               => $denda,
        'tunggakan'           => $tunggakan,
        'total_harus_dibayar' => $subtotal_pemakaian + $biaya_lainnya + $denda + $tunggakan,
        'rincian_dihitung'    => $rincian_dihitung,
    ];
}

}
