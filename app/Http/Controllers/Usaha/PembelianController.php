<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\Pemasok;
use App\Models\Produk;
use App\Models\Akun;
use App\Models\JurnalUmum;
use App\Models\DetailJurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelians = Pembelian::with('pemasok')->latest('tanggal_pembelian')->get();
        return view('usaha.pembelian.index', compact('pembelians'));
    }

    public function create()
    {
        $pemasoks = Pemasok::orderBy('nama_pemasok')->get();
        $produks = Produk::orderBy('nama_produk')->get();
        return view('usaha.pembelian.create', compact('pemasoks', 'produks'));
    }

    // app/Http/Controllers/Usaha/PembelianController.php

// ... (bagian atas controller tidak berubah)

public function store(Request $request)
{
    $request->validate([
        'tanggal_pembelian' => 'required|date',
        'pemasok_id' => 'required|exists:pemasoks,pemasok_id',
        'status_pembelian' => 'required|in:Lunas,Belum Lunas',
        'produk_id' => 'required|array|min:1',
        'produk_id.*' => 'required|exists:produks,produk_id',
        'jumlah' => 'required|array|min:1',
        'jumlah.*' => 'required|numeric|min:1',
        'harga_unit' => 'required|array|min:1',
        'harga_unit.*' => 'required|numeric|min:0',
    ]);

    try {
        DB::beginTransaction();

        $totalPembelian = 0;
        $detailData = [];

        foreach ($request->produk_id as $key => $id_produk) {
            $jumlah = $request->jumlah[$key];
            $harga = $request->harga_unit[$key];
            $subtotal = $jumlah * $harga;
            $totalPembelian += $subtotal;

            $detailData[] = [
                'produk_id' => $id_produk,
                'jumlah' => $jumlah,
                'harga_unit' => $harga,
                'subtotal' => $subtotal,
            ];

            // ================== LOGIKA UPDATE STOK ==================
            // Cari stok berdasarkan produk_id, lalu tambah jumlahnya
            $stok = Stok::where('produk_id', $id_produk)->first();
            if ($stok) {
                $stok->increment('jumlah_stok', $jumlah);
                $stok->update(['tanggal_perbarui' => now()]);
            }
            // ========================================================
        }

        // ... (sisa logika untuk membuat jurnal dan menyimpan pembelian tetap sama)

        $akunDebit = Akun::where('kode_akun', '1-10400')->firstOrFail(); // Persediaan Barang Dagang
        $deskripsiJurnal = 'Pembelian barang dagang dari ' . Pemasok::find($request->pemasok_id)->nama_pemasok;

        if ($request->status_pembelian == 'Lunas') {
            $akunKredit = Akun::where('kode_akun', '1-10101')->firstOrFail(); // Kas di Tangan
        } else {
            $akunKredit = Akun::where('kode_akun', '2-10100')->firstOrFail(); // Utang Usaha
        }

        $jurnal = JurnalUmum::create([
            'user_id' => Auth::id(),
            'tanggal_transaksi' => $request->tanggal_pembelian,
            'deskripsi' => $deskripsiJurnal,
            'total_debit' => $totalPembelian,
            'total_kredit' => $totalPembelian,
        ]);

        DetailJurnal::create([
            'jurnal_id' => $jurnal->jurnal_id,
            'akun_id' => $akunDebit->akun_id,
            'debit' => $totalPembelian,
            'kredit' => 0,
        ]);

        DetailJurnal::create([
            'jurnal_id' => $jurnal->jurnal_id,
            'akun_id' => $akunKredit->akun_id,
            'debit' => 0,
            'kredit' => $totalPembelian,
        ]);

        $pembelian = Pembelian::create([
            'pemasok_id' => $request->pemasok_id,
            'no_faktur' => $request->no_faktur,
            'tanggal_pembelian' => $request->tanggal_pembelian,
            'nominal_pembelian' => $totalPembelian,
            'jurnal_id' => $jurnal->jurnal_id,
            'unit_usaha_id' => Pemasok::find($request->pemasok_id)->unit_usaha_id,
            'status_pembelian' => $request->status_pembelian,
        ]);

        $pembelian->detailPembelians()->createMany($detailData);

        DB::commit();

        return redirect()->route('usaha.pembelian.index')->with('success', 'Transaksi pembelian berhasil disimpan dan stok telah diperbarui.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage())->withInput();
    }
}

    public function show(Pembelian $pembelian)
    {
        $pembelian->load('detailPembelians.produk', 'pemasok');
        return view('usaha.pembelian.show', compact('pembelian'));
    }

    public function destroy(Pembelian $pembelian)
    {
        try {
            DB::beginTransaction();
            $jurnal = JurnalUmum::find($pembelian->jurnal_id);
            $pembelian->delete();
            if ($jurnal) {
                $jurnal->delete();
            }
            DB::commit();
            return redirect()->route('usaha.pembelian.index')
                             ->with('success', 'Transaksi pembelian berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('usaha.pembelian.index')
                             ->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
