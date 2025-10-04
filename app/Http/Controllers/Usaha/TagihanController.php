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
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TagihanController extends Controller
{
public function index(Request $request)
{
    $bulan_terpilih = $request->input('periode_bulan', date('n'));
    $tahun_terpilih = $request->input('periode_tahun', date('Y'));
    $petugas_terpilih = $request->input('petugas_id');

    $periode_sekarang = Carbon::create($tahun_terpilih, $bulan_terpilih, 1);
    $periode_lalu = $periode_sekarang->copy()->subMonth();

    $semua_petugas = Petugas::orderBy('nama_petugas')->get();

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
    $data_tabel_mentah = $semua_pelanggan->map(function ($pelanggan) use ($tagihan_periode_lalu, $tagihan_periode_sekarang, $periode_sekarang) {
    $tagihan_lalu = $tagihan_periode_lalu->get($pelanggan->id);
    $tagihan_sekarang = $tagihan_periode_sekarang->get($pelanggan->id);

    $meter_awal = $tagihan_lalu?->meter_akhir ?? 0;
    $meter_akhir = $tagihan_sekarang?->meter_akhir ?? null;
    $pemakaian = ($meter_akhir !== null) ? max(0, $meter_akhir - $meter_awal) : 0;

    $denda_otomatis = 0;
    if (!$tagihan_sekarang || $tagihan_sekarang->status_pembayaran == 'Belum Lunas') {
        $tarif_denda = \App\Models\Tarif::where('jenis_tarif', 'denda')->first();
        $denda_per_bulan = $tarif_denda->harga ?? 5000;

        $bulanTagihan = $periode_sekarang->copy()->startOfMonth();
        $bulanSekarang = \Carbon\Carbon::now()->startOfMonth();
        $selisihBulan = $bulanTagihan->diffInMonths($bulanSekarang);

        if ($selisihBulan > 0) {
            $denda_otomatis = $selisihBulan * $denda_per_bulan;
        }
    }


    return (object) [
        'pelanggan'   => $pelanggan,
        'tagihan'     => $tagihan_sekarang,
        'meter_awal'  => $meter_awal,
        'meter_akhir' => $meter_akhir ?? '-',
        'pemakaian'   => $pemakaian,
        // Kirim denda otomatis ke view
        'denda_otomatis' => $tagihan_sekarang?->denda ?? $denda_otomatis,
    ];
});

  if ($petugas_terpilih) {
    $data_tabel = $data_tabel_mentah->filter(function ($baris) use ($petugas_terpilih) {
        // Hanya proses baris yang punya tagihan DAN petugas_id nya cocok
        return $baris->tagihan && $baris->tagihan->petugas_id == $petugas_terpilih;
    });
} else {
    $data_tabel = $data_tabel_mentah;
}
    return view('usaha.tagihan.index', [
        'data_tabel' => $data_tabel,
        'semua_petugas' => $semua_petugas,
        'petugas_terpilih' => $petugas_terpilih,
        'bulan_terpilih' => $bulan_terpilih,
        'tahun_terpilih' => $tahun_terpilih,
    ]);
}
// GANTI SELURUH METHOD INI DI TagihanController.php

// DI FILE: TagihanController.php

public function simpanSemuaMassal(Request $request)
{
    $request->validate([
  'periode_tagihan' => 'required|date',
        'tagihan' => 'required|array',
        'tagihan.*.meter_awal' => 'required|numeric|min:0',     // <-- Ganti ke numeric
        'tagihan.*.meter_akhir' => 'nullable|numeric|min:0',    // <-- Ganti ke numeric
        'tagihan.*.denda' => 'nullable|numeric|min:0',          // <-- Ganti ke numeric
        'petugas_massal_id' => 'required|numeric|exists:petugas,id',
    ]);

    $periode = $request->periode_tagihan;
    $petugas_id_massal = $request->petugas_massal_id;

    DB::beginTransaction();
    try {
        foreach ($request->tagihan as $pelanggan_id => $data) {
            $tagihan_sebelumnya = \App\Models\Tagihan::where('pelanggan_id', $pelanggan_id)
                                                    ->where('periode_tagihan', $periode)
                                                    ->first();

            $meter_akhir_diisi = isset($data['meter_akhir']) && $data['meter_akhir'] !== null && $data['meter_akhir'] !== '';

            if ($meter_akhir_diisi || $tagihan_sebelumnya) {
                $meter_awal_int = (int) $data['meter_awal'];
                $meter_akhir_int = $meter_akhir_diisi ? (int) $data['meter_akhir'] : ($tagihan_sebelumnya->meter_akhir ?? 0);

                if ($meter_akhir_int < $meter_awal_int) {
                    $pelanggan = \App\Models\Pelanggan::find($pelanggan_id);
                    throw new \Exception('Meter akhir untuk ' . ($pelanggan->nama ?? '#' . $pelanggan_id) . ' tidak boleh lebih kecil dari meter awal.');
                }

                $periode_sekarang = Carbon::parse($periode);
                $periode_lalu = $periode_sekarang->copy()->subMonth();
                $tagihan_lalu = \App\Models\Tagihan::where('pelanggan_id', $pelanggan_id)
                                                  ->where('periode_tagihan', $periode_lalu)
                                                  ->whereIn('status_pembayaran', ['Belum Lunas', 'Cicil'])
                                                  ->first();

                $tunggakan_bulan_lalu = $tagihan_lalu?->total_harus_dibayar - ($tagihan_lalu?->jumlah_dibayar ?? 0);
                $total_pemakaian_real = max(0, $meter_akhir_int - $meter_awal_int);
                $denda_dari_input = isset($data['denda']) && $data['denda'] !== '' ? (int)$data['denda'] : null;

                $hasil_kalkulasi = $this->kalkulasiTagihanData(
                    $total_pemakaian_real,
                    $tunggakan_bulan_lalu,
                    $periode_sekarang,
                    $denda_dari_input
                );

                //--- LOGIKA PERBAIKAN UTAMA ADA DI SINI ---

                $data_untuk_disimpan = array_merge($hasil_kalkulasi, [
                    'pelanggan_id' => $pelanggan_id,
                    'periode_tagihan' => $periode,
                    'petugas_id' => $petugas_id_massal,
                    'meter_awal' => $meter_awal_int,
                    'meter_akhir' => $meter_akhir_int,
                    'tunggakan' => $tunggakan_bulan_lalu,
                ]);
                unset($data_untuk_disimpan['rincian_dihitung']);

                if ($tagihan_sebelumnya) {
                    // JIKA TAGIHAN SUDAH ADA (UPDATE), PERTAHANKAN STATUS & JUMLAH BAYAR
                    $data_untuk_disimpan['status_pembayaran'] = $tagihan_sebelumnya->status_pembayaran;
                    $data_untuk_disimpan['jumlah_dibayar'] = $tagihan_sebelumnya->jumlah_dibayar;
                } else {
                    // JIKA TAGIHAN BARU, SET DEFAULT
                    $data_untuk_disimpan['status_pembayaran'] = 'Belum Lunas';
                    $data_untuk_disimpan['jumlah_dibayar'] = 0;
                }

                if ($hasil_kalkulasi['total_harus_dibayar'] <= ($data_untuk_disimpan['jumlah_dibayar'] ?? 0) && $total_pemakaian_real > 0) {
                    $data_untuk_disimpan['status_pembayaran'] = 'Lunas';
                }


                    Log::info('--- DEBUG SIMPAN MASSAL ---', [
                'pelanggan_id' => $pelanggan_id,
                '1_data_dari_form' => $data,
                '2_tagihan_sebelumnya_dari_db' => $tagihan_sebelumnya ? $tagihan_sebelumnya->toArray() : 'TIDAK ADA (BARU)',
                '3_data_final_akan_disimpan' => $data_untuk_disimpan,
            ]);

                $tagihan = \App\Models\Tagihan::updateOrCreate(
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
        Tagihan::whereIn('id', $request->tagihan_ids)->update(['status_pembayaran' => 'Lunas',
    'tanggal_pembayaran' => now()
    ]);
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
    $validated = $request->validate(['tagihan_ids' => 'required|array']);
    $tagihan_ids = $validated['tagihan_ids'];

    // LANGKAH 1: Update kolom tanggal_cetak di database dengan tanggal sekarang
    Tagihan::whereIn('id', $tagihan_ids)->update(['tanggal_cetak' => now()]);

    // LANGKAH 2: Ambil data yang sudah ter-update untuk ditampilkan
    $semua_tagihan = Tagihan::whereIn('id', $tagihan_ids)
                            ->with('pelanggan', 'petugas', 'rincian')
                            ->get();

    return view('usaha.tagihan.cetak-massal', compact('semua_tagihan'));
}

    public function destroy(Tagihan $tagihan)
    {
        $tagihan->delete();
        return back()->with('success', 'Tagihan berhasil dihapus.');
    }

private function kalkulasiTagihanData($total_pemakaian_real, $tunggakan_manual, $periode_tagihan, $denda_override = null)
{
    $semua_tarif = \App\Models\Tarif::all();
    $rincian_dihitung = [];
    $subtotal_pemakaian = 0;

    // --- Tarif pemakaian berdasarkan blok ---
    $tarif_pemakaian = $semua_tarif->where('jenis_tarif', 'pemakaian')->sortBy('batas_bawah');
    $pemakaian_untuk_dihitung = $total_pemakaian_real;
    $tarif_blok_pertama = $tarif_pemakaian->first();

    if ($tarif_blok_pertama) {

        $batas_minimum = 5;
        if ($pemakaian_untuk_dihitung > 0 && $pemakaian_untuk_dihitung < $batas_minimum) {
            $pemakaian_untuk_dihitung = $batas_minimum;
        }

        $sisa_pemakaian = $pemakaian_untuk_dihitung;

        foreach ($tarif_pemakaian as $index => $tarif) {
            if ($sisa_pemakaian <= 0) break;

            $batas_bawah = (int) $tarif->batas_bawah;
            $batas_atas = $tarif->batas_atas;

            // Hitung kapasitas blok (inklusif: 0–5 = 5, 6–15 = 10, dst)
            if (is_null($batas_atas)) {
                $kapasitas_blok = $sisa_pemakaian; // blok terakhir tak terbatas
            } else {
                $kapasitas_blok = $batas_atas - $batas_bawah + 1;
            }

            // --- Blok pertama wajib minimal 5 ---
           if ($index === 0) {
    // Blok pertama selalu maksimal 5 m³
    $kuantitas_blok_ini = min($sisa_pemakaian, $batas_minimum);
} else {
    $kuantitas_blok_ini = min($sisa_pemakaian, $kapasitas_blok);
}

            // Tambah ke rincian kalau ada pemakaian di blok ini
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
    }

    // --- Biaya tetap ---
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

    // --- Denda otomatis / manual ---
    $denda_otomatis = 0;
    $tarif_denda = $semua_tarif->where('jenis_tarif', 'denda')->first();
    $denda_per_bulan = $tarif_denda->harga ?? 5000;

    $bulanTagihan = $periode_tagihan->startOfMonth();
    $bulanSekarang = \Carbon\Carbon::now()->startOfMonth();
    $selisihBulan = $bulanTagihan->diffInMonths($bulanSekarang);

    if ($selisihBulan > 0) {
        $denda_otomatis = $selisihBulan * $denda_per_bulan;
    }

    $denda_final = $denda_otomatis; // 1. Asumsikan kita pakai denda otomatis.

if ($denda_override !== null) {
    $denda_final = $denda_override;
}

    // --- Tunggakan ---
    if ($tunggakan_manual > 0) {
        $rincian_dihitung[] = [
            'deskripsi'    => 'Tunggakan Bulan Sebelumnya',
            'kuantitas'    => 1,
            'harga_satuan' => $tunggakan_manual,
            'subtotal'     => $tunggakan_manual,
        ];
    }

    // --- Denda ---
    if ($denda_final > 0) {
        $deskripsi_denda = is_null($denda_override)
            ? ($tarif_denda->deskripsi ?? 'Denda Keterlambatan')
            : 'Denda (Manual)';
        $rincian_dihitung[] = [
            'deskripsi'    => $deskripsi_denda,
            'kuantitas'    => 1,
            'harga_satuan' => $denda_final,
            'subtotal'     => $denda_final,
        ];
    }

    // --- Total akhir ---
    $total_harus_dibayar = $subtotal_pemakaian + $biaya_lainnya + $tunggakan_manual + $denda_final;

    return [
        'total_pemakaian_m3' => $total_pemakaian_real,
        'subtotal_pemakaian' => $subtotal_pemakaian,
        'biaya_lainnya'      => $biaya_lainnya,
        'denda'              => $denda_final,
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

    // DI FILE: app/Http/Controllers/Usaha/TagihanController.php

public function rekap(Request $request)
{
    $bulan_terpilih = $request->input('periode_bulan', date('n'));
    $tahun_terpilih = $request->input('periode_tahun', date('Y'));
    $nama_bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

    $periode = \Carbon\Carbon::create($tahun_terpilih, $bulan_terpilih, 1);

    // Ambil SEMUA tagihan pada periode yang dipilih (termasuk yang dibatalkan untuk data lengkap)
    // Eager load relasi untuk optimasi query
    $semua_tagihan = Tagihan::with(['pelanggan', 'rincian'])
                            ->whereYear('periode_tagihan', $tahun_terpilih)
                            ->whereMonth('periode_tagihan', $bulan_terpilih)
                            ->get()
                            ->sortBy('pelanggan.nama'); // Urutkan berdasarkan nama pelanggan

    // Hitung total hanya dari tagihan yang tidak dibatalkan
    $tagihan_aktif = $semua_tagihan->where('status_pembayaran', '!=', 'Batal');

    $total_pemasukan = $tagihan_aktif->whereIn('status_pembayaran', ['Lunas', 'Cicil'])->sum('jumlah_dibayar');
    $total_belum_lunas = $tagihan_aktif->whereIn('status_pembayaran', ['Belum Lunas', 'Cicil'])
                                    ->sum(function ($tagihan) {
                                        return $tagihan->total_harus_dibayar - $tagihan->jumlah_dibayar;
                                    });

    return view('usaha.tagihan.rekap', [
        'semua_tagihan' => $semua_tagihan, // Kirim semua data tagihan ke view
        'bulan_terpilih' => $bulan_terpilih,
        'tahun_terpilih' => $tahun_terpilih,
        'nama_bulan' => $nama_bulan,
        'total_pemasukan' => $total_pemasukan,
        'total_belum_lunas' => $total_belum_lunas,
        'periode' => $periode,
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
        'tagihan_ids.*' => 'exists:tagihan,id',
    ]);

    try {
        Tagihan::whereIn('id', $request->tagihan_ids)->update(['status_pembayaran' => 'Batal']);
        return back()->with('success', 'Tagihan yang dipilih berhasil dibatalkan.');
    } catch (\Exception $e) {
        return back()->with('error', 'Gagal membatalkan tagihan: ' . $e->getMessage());
    }
}

public function batalkanLunas(Tagihan $tagihan)
{
    if ($tagihan->status_pembayaran !== 'Lunas') {
        return back()->with('error', 'Tagihan ini tidak berstatus Lunas.');
    }
    $newStatus = ($tagihan->jumlah_dibayar > 0) ? 'Cicil' : 'Belum Lunas';

    $tagihan->update([
        'status_pembayaran' => $newStatus,
        'tanggal_pembayaran' => null
    ]);

    return back()->with('success', 'Status Lunas berhasil dibatalkan.');
}

public function prosesPembayaran(Request $request, Tagihan $tagihan)
{
    $sisa_tagihan = $tagihan->total_harus_dibayar - $tagihan->jumlah_dibayar;

     $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'jumlah_bayar' => 'required|numeric|min:1|max:' . $sisa_tagihan,
    ], [
        'jumlah_bayar.max' => 'Jumlah bayar tidak boleh melebihi sisa tagihan.',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $tagihan->jumlah_dibayar += $request->jumlah_bayar;
    if (bccomp((string)$tagihan->jumlah_dibayar, (string)$tagihan->total_harus_dibayar, 2) >= 0) {
        $tagihan->status_pembayaran = 'Lunas';
        $tagihan->tanggal_pembayaran = now();
    } else {
        $tagihan->status_pembayaran = 'Cicil';
    }
    $tagihan->save();

    return response()->json([
        'success' => true,
        'message' => 'Pembayaran berhasil dicatat!',
    ]);
}

public function cetakRekap(Request $request)
{
    // Logika pengambilan data sama persis dengan method rekap
    $bulan_terpilih = $request->input('periode_bulan', date('n'));
    $tahun_terpilih = $request->input('periode_tahun', date('Y'));
    $nama_bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

    $semua_tagihan = Tagihan::with(['pelanggan', 'rincian'])
                            ->whereYear('periode_tagihan', $tahun_terpilih)
                            ->whereMonth('periode_tagihan', $bulan_terpilih)
                            ->get()
                            ->sortBy('pelanggan.nama');

    // Kirim data ke view cetak yang baru
    return view('usaha.tagihan.rekap-cetak', [
        'semua_tagihan' => $semua_tagihan,
        'bulan_terpilih' => $bulan_terpilih,
        'tahun_terpilih' => $tahun_terpilih,
        'nama_bulan' => $nama_bulan,
    ]);
}

}
