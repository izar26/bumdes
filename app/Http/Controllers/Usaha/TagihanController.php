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
public function index(Request $request)
{
    // --- Bagian ini tetap sama ---
    $bulan_terpilih = $request->input('periode_bulan', date('n'));
    $tahun_terpilih = $request->input('periode_tahun', date('Y'));
    $petugas_terpilih = $request->input('petugas_id');

    $periode_sekarang = Carbon::create($tahun_terpilih, $bulan_terpilih, 1);
    $periode_lalu = $periode_sekarang->copy()->subMonth();

    $semua_petugas = Petugas::orderBy('nama_petugas')->get();

    // ====================================================================
    // PERUBAHAN DIMULAI DARI SINI
    // ====================================================================

    // 1. Ambil SEMUA pelanggan aktif terlebih dahulu, jangan difilter dulu.
    $semua_pelanggan = Pelanggan::where('status_pelanggan', 'Aktif')->orderBy('nama')->get();
    $semua_pelanggan_ids = $semua_pelanggan->pluck('id');

    // 2. Ambil tagihan periode lalu berdasarkan SEMUA pelanggan aktif.
    $tagihan_periode_lalu = Tagihan::whereIn('pelanggan_id', $semua_pelanggan_ids)
                                    ->where('periode_tagihan', $periode_lalu->toDateString())
                                    ->get()->keyBy('pelanggan_id');

    // 3. Ambil tagihan periode sekarang berdasarkan SEMUA pelanggan aktif.
    $tagihan_periode_sekarang = Tagihan::whereIn('pelanggan_id', $semua_pelanggan_ids)
                                       ->where('periode_tagihan', $periode_sekarang->toDateString())
                                       ->with('petugas') // Eager load relasi petugas
                                       ->get()->keyBy('pelanggan_id');

    // 4. Buat data tabel mentah dari semua pelanggan.
    $data_tabel_mentah = $semua_pelanggan->map(function ($pelanggan) use ($tagihan_periode_lalu, $tagihan_periode_sekarang) {
        $tagihan_lalu = $tagihan_periode_lalu->get($pelanggan->id);
        $tagihan_sekarang = $tagihan_periode_sekarang->get($pelanggan->id);

        // Logika ini sekarang akan berjalan dengan benar karena $tagihan_lalu akan ditemukan
        $meter_awal = $tagihan_lalu->meter_akhir ?? 0;
        $meter_akhir = $tagihan_sekarang->meter_akhir ?? null; // Ganti ke null agar lebih jelas jika belum ada
        
        // Jika meter akhir belum diisi, pemakaian adalah 0
        $pemakaian = ($meter_akhir !== null) ? max(0, $meter_akhir - $meter_awal) : 0;

        return (object) [
            'pelanggan'   => $pelanggan,
            'tagihan'     => $tagihan_sekarang,
            'meter_awal'  => $meter_awal,
            'meter_akhir' => $meter_akhir ?? '-', // Tampilkan strip jika null
            'pemakaian'   => $pemakaian,
        ];
    });

    // 5. BARU LAKUKAN FILTER jika petugas dipilih.
    // Filter ini dilakukan pada data yang sudah jadi, bukan pada query awal.
    if ($petugas_terpilih) {
        // Jika petugas dipilih, tampilkan pelanggan yang SUDAH punya tagihan dengan petugas tsb,
        // ATAU yang BELUM punya tagihan sama sekali (agar bisa diinput baru).
        $data_tabel = $data_tabel_mentah->filter(function ($baris) use ($petugas_terpilih) {
            return !$baris->tagihan || ($baris->tagihan && $baris->tagihan->petugas_id == $petugas_terpilih);
        });
    } else {
        $data_tabel = $data_tabel_mentah;
    }
    
    // ====================================================================
    // AKHIR DARI PERUBAHAN
    // ====================================================================

    return view('usaha.tagihan.index', [
        'data_tabel' => $data_tabel,
        'semua_petugas' => $semua_petugas,
        'petugas_terpilih' => $petugas_terpilih,
        'bulan_terpilih' => $bulan_terpilih,
        'tahun_terpilih' => $tahun_terpilih,
    ]);
}

    public function simpanSemuaMassal(Request $request)
{
    $request->validate([
        'periode_tagihan' => 'required|date',
        'tagihan' => 'required|array',
        'tagihan.*.meter_awal' => 'required|numeric|min:0',
        'tagihan.*.meter_akhir' => 'nullable|numeric|min:0',
        'petugas_massal_id' => 'required|numeric|exists:petugas,id',
    ]);

    $periode = $request->periode_tagihan;
    $petugas_id_massal = $request->petugas_massal_id;

    DB::beginTransaction();
    try {
        foreach ($request->tagihan as $pelanggan_id => $data) {
            if (isset($data['meter_akhir']) && $data['meter_akhir'] !== null && $data['meter_akhir'] !== '') {
                if ($data['meter_akhir'] < $data['meter_awal']) {
                    $pelanggan = Pelanggan::find($pelanggan_id);
                    throw new \Exception('Meter akhir untuk pelanggan ' . ($pelanggan->nama ?? '#' . $pelanggan_id) . ' tidak boleh lebih kecil dari meter awal.');
                }

                // Ambil tunggakan dari tagihan bulan lalu yang belum lunas
                $periode_sekarang = Carbon::parse($periode)->startOfMonth();
                $periode_lalu = $periode_sekarang->copy()->subMonth();
                $tagihan_lalu = Tagihan::where('pelanggan_id', $pelanggan_id)
                                      ->where('periode_tagihan', $periode_lalu)
                                      ->where('status_pembayaran', 'Belum Lunas')
                                      ->first();

                // Dapatkan nilai tunggakan, jika tagihan bulan lalu ada
                $tunggakan_bulan_lalu = $tagihan_lalu->total_harus_dibayar ?? 0;
                $total_pemakaian_real = max(0, $data['meter_akhir'] - $data['meter_awal']);

                $hasil_kalkulasi = $this->kalkulasiTagihanData(
                    $total_pemakaian_real,
                    $tunggakan_bulan_lalu, // Kirimkan nilai tunggakan yang sudah diambil
                    Carbon::parse($periode_sekarang)
                );

                $data_untuk_disimpan = array_merge($hasil_kalkulasi, [
                    'pelanggan_id' => $pelanggan_id,
                    'periode_tagihan' => $periode,
                    'petugas_id' => $petugas_id_massal,
                    'meter_awal' => $data['meter_awal'],
                    'meter_akhir' => $data['meter_akhir'],
                    'tanggal_cetak' => now(),
                    'tunggakan' => $tunggakan_bulan_lalu, // Simpan nilai tunggakan ke kolom baru
                    'status_pembayaran' => ($hasil_kalkulasi['total_harus_dibayar'] == 0) ? 'Lunas' : 'Belum Lunas',
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

    public function tandaiLunas(Request $request, Tagihan $tagihan)
    {
        $tagihan->update(['status_pembayaran' => 'Lunas']);
        return back()->with('success', 'Tagihan berhasil ditandai lunas.');
    }
    public function tandaiLunasSelektif(Request $request)
    {
        $request->validate(['tagihan_ids' => 'required|array']);
        Tagihan::whereIn('id', $request->tagihan_ids)->update(['status_pembayaran' => 'Lunas']);
        return back()->with('success', 'Tagihan terpilih berhasil ditandai lunas.');
    }

    public function show($id)
    {
        $tagihan = Tagihan::with('pelanggan', 'petugas', 'rincian')->findOrFail($id);
        return view('usaha.tagihan.show', compact('tagihan'));
    }

    public function cetakMassal(Request $request)
    {
        $periode = Carbon::create($request->periode_tahun, $request->periode_bulan, 1);
        $semua_tagihan = Tagihan::where('periode_tagihan', $periode->toDateString())
                                ->with('pelanggan', 'petugas', 'rincian')
                                ->get();
        return view('usaha.tagihan.cetak-massal', compact('semua_tagihan'));
    }

    public function cetakSelektif(Request $request)
    {
        $request->validate(['tagihan_ids' => 'required|array']);
        $semua_tagihan = Tagihan::whereIn('id', $request->tagihan_ids)
                                ->with('pelanggan', 'petugas', 'rincian')
                                ->get();
        return view('usaha.tagihan.cetak-massal', compact('semua_tagihan'));
    }

    public function destroy(Tagihan $tagihan)
    {
        $tagihan->delete();
        return back()->with('success', 'Tagihan berhasil dihapus.');
    }

private function kalkulasiTagihanData($total_pemakaian_real, $tunggakan_manual, $periode_tagihan)
{
    $semua_tarif = Tarif::all();
    $rincian_dihitung = [];
    $subtotal_pemakaian = 0;

    // Ambil data tarif pemakaian
    $tarif_pemakaian = $semua_tarif->where('jenis_tarif', 'pemakaian')->sortBy('batas_bawah');

    // ====================================================================
    // --- PENAMBAHAN LOGIKA BIAYA MINIMUM ---
    // ====================================================================
    $pemakaian_untuk_dihitung = $total_pemakaian_real;
    $tarif_blok_pertama = $tarif_pemakaian->first();

    // Pastikan ada data tarif blok pertama
    if ($tarif_blok_pertama) {
        $batas_minimum = $tarif_blok_pertama->batas_atas; // Mengambil batas atas dari blok pertama, yaitu 5

        // Jika pemakaian lebih dari 0 dan kurang dari batas minimum, maka bulatkan ke atas
        if ($pemakaian_untuk_dihitung > 0 && $pemakaian_untuk_dihitung < $batas_minimum) {
            $pemakaian_untuk_dihitung = $batas_minimum;
        }
    }
    // ====================================================================
    // --- AKHIR DARI LOGIKA BIAYA MINIMUM ---
    // ====================================================================

    // Gunakan $pemakaian_untuk_dihitung untuk sisa proses kalkulasi
    $sisa_pemakaian = $pemakaian_untuk_dihitung;

    foreach ($tarif_pemakaian as $tarif) {
        if ($sisa_pemakaian <= 0) {
            break;
        }

        $batas_bawah = (int)$tarif->batas_bawah;
        $batas_atas = $tarif->batas_atas;

        if (is_null($batas_atas)) {
            $kapasitas_blok = $sisa_pemakaian;
        } else {
            if ($batas_bawah == 0) {
                $kapasitas_blok = $batas_atas;
            } else {
                $kapasitas_blok = $batas_atas - $batas_bawah + 1;
            }
        }
        
        $kuantitas_blok_ini = min($sisa_pemakaian, $kapasitas_blok);
        
        if ($kuantitas_blok_ini > 0) {
            $subtotal = $kuantitas_blok_ini * $tarif->harga;
            $rincian_dihitung[] = [
                'deskripsi'    => $tarif->deskripsi,
                'kuantitas'    => $kuantitas_blok_ini,
                'harga_satuan' => $tarif->harga,
                'subtotal'     => $subtotal,
            ];
            $subtotal_pemakaian += $subtotal;
        }

        $sisa_pemakaian -= $kuantitas_blok_ini;
    }

    // ===== Biaya tetap =====
    $biaya_lainnya = 0;
    $tarif_biaya_tetap = $semua_tarif->where('jenis_tarif', 'biaya_tetap');
    foreach ($tarif_biaya_tetap as $biaya) {
        $biaya_lainnya += $biaya->harga;
        $rincian_dihitung[] = [
            'deskripsi'    => $biaya->deskripsi,
            'kuantitas'    => 1,
            'harga_satuan' => $biaya->harga,
            'subtotal'     => $biaya->harga,
        ];
    }

    // ===== Hitung denda dinamis =====
    $tarif_denda = $semua_tarif->where('jenis_tarif', 'denda')->first();
    $denda_per_bulan = $tarif_denda->harga ?? 5000;
    $denda = 0;
    $bulanTagihan = $periode_tagihan->startOfMonth();
    $bulanSekarang = Carbon::now()->startOfMonth();
    $selisihBulan = $bulanTagihan->diffInMonths($bulanSekarang);
    if ($selisihBulan > 0) {
        $denda = $selisihBulan * $denda_per_bulan;
        $rincian_dihitung[] = [
            'deskripsi'    => $tarif_denda->deskripsi ?? 'Denda Keterlambatan',
            'kuantitas'    => $selisihBulan,
            'harga_satuan' => $denda_per_bulan,
            'subtotal'     => $denda,
        ];
    }

    // ===== Tambahkan tunggakan manual =====
    if ($tunggakan_manual > 0) {
        $rincian_dihitung[] = [
            'deskripsi'    => 'Tunggakan Bulan Sebelumnya',
            'kuantitas'    => 1,
            'harga_satuan' => $tunggakan_manual,
            'subtotal'     => $tunggakan_manual,
        ];
    }

    // ===== Total =====
    $total_harus_dibayar = $subtotal_pemakaian + $biaya_lainnya + $tunggakan_manual + $denda;

    return [
        'total_pemakaian_m3' => $total_pemakaian_real, // Tetap laporkan pemakaian asli
        'subtotal_pemakaian' => $subtotal_pemakaian,
        'biaya_lainnya'      => $biaya_lainnya,
        'denda'              => $denda,
        'tunggakan'          => $tunggakan_manual,
        'total_harus_dibayar'=> $total_harus_dibayar,
        'rincian_dihitung'   => $rincian_dihitung,
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
    public function quickSave(Request $request)
{
    $validated = $request->validate([
        'pelanggan_id'    => 'required|exists:pelanggan,id',
        'meter_awal'      => 'required|integer|min:0',
        'meter_akhir'     => 'required|integer|min:' . $request->meter_awal,
        'petugas_id'      => 'required|exists:petugas,id',
        'periode_tagihan' => 'required|date',
    ]);

    $pemakaian = $validated['meter_akhir'] - $validated['meter_awal'];

    // Hitung tarif sesuai blok (misalnya ada tabel tarif)
    $tarif = Tarif::orderBy('batas_atas')->get();
    $total_bayar = 0;
    $sisa = $pemakaian;

    foreach ($tarif as $t) {
        $limit = $t->batas_atas - $t->batas_bawah + 1;
        $pakai = min($sisa, $limit);
        $total_bayar += $pakai * $t->harga_per_m3;
        $sisa -= $pakai;

        if ($sisa <= 0) break;
    }

    // Simpan ke tabel tagihan
    $tagihan = Tagihan::updateOrCreate(
        [
            'pelanggan_id'    => $validated['pelanggan_id'],
            'periode_tagihan' => $validated['periode_tagihan'],
        ],
        [
            'meter_awal'        => $validated['meter_awal'],
            'meter_akhir'       => $validated['meter_akhir'],
            'total_pemakaian_m3'=> $pemakaian,
            'total_harus_dibayar' => $total_bayar,
            'status_pembayaran' => 'Belum Lunas',
            'petugas_id'        => $validated['petugas_id'],
        ]
    );

    return response()->json([
        'success'     => true,
        'message'     => 'Tagihan berhasil disimpan',
        'tagihan_id'  => $tagihan->id,
        'total_bayar' => number_format($total_bayar, 0, ',', '.'),
    ]);
}


public function batalkanMassal(Request $request)
{
    $request->validate([
        'tagihan_ids' => 'required|array',
        'tagihan_ids.*' => 'exists:tagihan,id', // Memastikan setiap ID ada di tabel
    ]);

    try {
        Tagihan::whereIn('id', $request->tagihan_ids)->update(['status_pembayaran' => 'Batal']);
        return back()->with('success', 'Tagihan yang dipilih berhasil dibatalkan.');
    } catch (\Exception $e) {
        return back()->with('error', 'Gagal membatalkan tagihan: ' . $e->getMessage());
    }
}
}
