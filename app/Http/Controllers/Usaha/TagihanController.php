<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use App\Models\Petugas;
use App\Models\Tarif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $semua_petugas = Petugas::orderBy('nama_petugas')->get();

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
            'semua_petugas' => $semua_petugas,
            'bulan_terpilih' => $bulan_terpilih,
            'tahun_terpilih' => $tahun_terpilih,
        ]);
    }

    /**
     * Menyimpan SEMUA perubahan dari mode edit massal.
     */
    public function simpanSemuaMassal(Request $request)
    {
        $request->validate([
            'petugas_id' => 'required|exists:petugas,id',
            'periode_tagihan' => 'required|date',
            'tagihan' => 'required|array',
            'tagihan.*.meter_awal' => 'required|numeric|min:0',
            'tagihan.*.meter_akhir' => 'nullable|numeric|min:0'
        ], ['petugas_id.required' => 'Silakan pilih petugas yang bertugas.']);

        $periode = $request->periode_tagihan;
        $petugas_id = $request->petugas_id;

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
     * [PRIVATE METHOD] Otak dari semua kalkulasi tagihan.
     */
    private function kalkulasiTagihanData(Tagihan $tagihan)
    {
        $semua_tarif = Tarif::all();
        $total_pemakaian = $tagihan->meter_akhir - $tagihan->meter_awal;
        $rincian_dihitung = [];
        $subtotal_pemakaian = 0;

        $sisa_pemakaian = $total_pemakaian;
        $tarif_pemakaian = $semua_tarif->where('jenis_tarif', 'pemakaian')->sortBy('batas_bawah');
        foreach ($tarif_pemakaian as $tarif) { if ($sisa_pemakaian <= 0) break; $batas_atas = $tarif->batas_atas ?? 999999; $rentang_blok = $batas_atas - ($tarif->batas_bawah > 0 ? ($tarif->batas_bawah - 1) : 0); $pemakaian_di_blok = min($sisa_pemakaian, $rentang_blok); $biaya_blok = $pemakaian_di_blok * $tarif->harga; $subtotal_pemakaian += $biaya_blok; $sisa_pemakaian -= $pemakaian_di_blok; if ($pemakaian_di_blok > 0) { $rincian_dihitung[] = ['deskripsi' => $tarif->deskripsi, 'kuantitas' => $pemakaian_di_blok, 'harga_satuan' => $tarif->harga, 'subtotal' => $biaya_blok]; } }

        $biaya_lainnya = 0;
        $tarif_biaya_tetap = $semua_tarif->where('jenis_tarif', 'biaya_tetap');
        foreach ($tarif_biaya_tetap as $biaya) { $biaya_lainnya += $biaya->harga; $rincian_dihitung[] = ['deskripsi' => $biaya->deskripsi, 'kuantitas' => 1, 'harga_satuan' => $biaya->harga, 'subtotal' => $biaya->harga]; }

        $tunggakan = 0; $denda = 0;
        $tagihan_terakhir = Tagihan::where('pelanggan_id', $tagihan->pelanggan_id)->where('periode_tagihan', '<', $tagihan->periode_tagihan)->latest('periode_tagihan')->first();
        if ($tagihan_terakhir && $tagihan_terakhir->status_pembayaran == 'Belum Lunas') { $tunggakan = $tagihan_terakhir->total_harus_dibayar; $tarif_denda = $semua_tarif->where('jenis_tarif', 'denda')->first(); if ($tarif_denda) { $denda = $tarif_denda->harga; $rincian_dihitung[] = ['deskripsi' => $tarif_denda->deskripsi, 'kuantitas' => 1, 'harga_satuan' => $denda, 'subtotal' => $denda]; } }

        return [
            'total_pemakaian_m3'    => $total_pemakaian, 'subtotal_pemakaian'    => $subtotal_pemakaian,
            'biaya_lainnya'         => $biaya_lainnya, 'denda'                 => $denda, 'tunggakan'             => $tunggakan,
            'total_harus_dibayar'   => $subtotal_pemakaian + $biaya_lainnya + $denda + $tunggakan,
            'rincian_dihitung'      => $rincian_dihitung,
        ];
    }

    /**
     * Menampilkan detail satu tagihan untuk dicetak.
     */
    public function show(Tagihan $tagihan)
    {
        $tagihan->load(['pelanggan', 'petugas', 'rincian']);
        return view('usaha.tagihan.show', compact('tagihan'));
    }

    /**
     * Menghapus tagihan dari database.
     */
    public function destroy(Tagihan $tagihan)
    {
        try {
            $tagihan->delete();
            return redirect()->route('usaha.tagihan.index')->with('success', 'Tagihan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('usaha.tagihan.index')->with('error', 'Gagal menghapus tagihan.');
        }
    }

    /**
     * Mengubah status satu tagihan menjadi 'Lunas'.
     */
    public function tandaiLunas(Tagihan $tagihan)
    {
        try {
            $tagihan->status_pembayaran = 'Lunas'; $tagihan->save();
            if (request()->ajax()) { return response()->json(['success' => true, 'message' => 'Tagihan untuk ' . $tagihan->pelanggan->nama . ' berhasil dilunasi.']); }
            return redirect()->back()->with('success', 'Tagihan berhasil dilunasi.');
        } catch (\Exception $e) {
            if (request()->ajax()) { return response()->json(['success' => false, 'message' => 'Gagal mengubah status.'], 500); }
            return redirect()->back()->with('error', 'Gagal mengubah status.');
        }
    }

    /**
     * Mengubah status beberapa tagihan yang dipilih menjadi 'Lunas'.
     */
    public function tandaiLunasSelektif(Request $request)
    {
        $request->validate(['tagihan_ids' => 'required|array|min:1', 'tagihan_ids.*' => 'exists:tagihan,id']);
        try {
            Tagihan::whereIn('id', $request->tagihan_ids)->where('status_pembayaran', 'Belum Lunas')->update(['status_pembayaran' => 'Lunas']);
            return redirect()->back()->with('success', count($request->tagihan_ids) . ' tagihan berhasil ditandai lunas.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui status tagihan.');
        }
    }

    /**
     * Mencetak tagihan yang dipilih dari halaman index.
     */
    public function cetakSelektif(Request $request)
    {
        $request->validate(['tagihan_ids'   => 'required|array|min:1', 'tagihan_ids.*' => 'exists:tagihan,id'], ['tagihan_ids.required' => 'Tidak ada tagihan yang dipilih.']);
        $semua_tagihan = Tagihan::with(['pelanggan', 'petugas', 'rincian'])->whereIn('id', $request->input('tagihan_ids'))->get();
        return view('usaha.tagihan.cetak-massal', compact('semua_tagihan'));
    }

    /**
     * Mencetak semua tagihan dalam satu periode bulan tertentu.
     */
    public function cetakMassal(Request $request)
    {
        $request->validate(['periode_bulan' => 'required|integer|between:1,12', 'periode_tahun' => 'required|integer|digits:4']);
        $periode = Carbon::create($request->periode_tahun, $request->periode_bulan, 1)->toDateString();
        $semua_tagihan = Tagihan::with(['pelanggan', 'petugas', 'rincian'])->where('periode_tagihan', $periode)->get();
        if ($semua_tagihan->isEmpty()) { return redirect()->back()->with('error', 'Tidak ada tagihan yang ditemukan untuk periode tersebut.'); }
        return view('usaha.tagihan.cetak-massal', compact('semua_tagihan'));
    }
}
