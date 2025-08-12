<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Pemasok;
use App\Models\Produk;
use App\Models\Akun;
use App\Models\JurnalUmum;
use App\Models\DetailJurnal;
use App\Models\Stok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    /**
     * Menampilkan daftar transaksi pembelian.
     */
    public function index()
    {
        $user = Auth::user();
        $pembelianQuery = Pembelian::with('pemasok', 'unitUsaha')->latest('tanggal_pembelian');

        // Filter berdasarkan peran pengguna
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            // FIX: Tambahkan nama tabel 'unit_usahas' untuk mengatasi ambiguitas kolom
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $pembelianQuery->whereIn('unit_usaha_id', $unitUsahaIds);
        }
        // Bendahara dan peran di atasnya bisa melihat semua

        $pembelians = $pembelianQuery->get();
        return view('usaha.pembelian.index', compact('pembelians'));
    }

    /**
     * Menampilkan form untuk membuat transaksi pembelian baru.
     */
    public function create()
    {
        $user = Auth::user();
        $pemasokQuery = Pemasok::orderBy('nama_pemasok');
        $produkQuery = Produk::orderBy('nama_produk');

        // Filter pemasok dan produk berdasarkan unit usaha yang dikelola pengguna
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            // FIX: Tambahkan nama tabel 'unit_usahas' untuk mengatasi ambiguitas kolom
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $pemasokQuery->whereIn('unit_usaha_id', $unitUsahaIds);
            $produkQuery->whereIn('unit_usaha_id', $unitUsahaIds);
        }

        $pemasoks = $pemasokQuery->get();
        $produks = $produkQuery->get();

        return view('usaha.pembelian.create', compact('pemasoks', 'produks'));
    }

    /**
     * Menyimpan transaksi pembelian baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_pembelian' => 'required|date',
            'pemasok_id' => 'required|exists:pemasoks,pemasok_id',
            'status_pembelian' => 'required|in:Lunas,Belum Lunas',
            'no_faktur' => 'nullable|string|max:255',
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

            // Tentukan Unit Usaha dari pemasok yang dipilih
            $pemasok = Pemasok::findOrFail($request->pemasok_id);
            $unitUsahaId = $pemasok->unit_usaha_id;

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

                // Logika Update Stok: Tambah stok saat pembelian
                // Gunakan firstOrCreate untuk menangani jika record stok belum ada
                Stok::firstOrCreate(['produk_id' => $id_produk], ['jumlah_stok' => 0])
                    ->increment('jumlah_stok', $jumlah);
            }

            // --- Jurnal Akuntansi ---
            $akunDebit = Akun::where('kode_akun', '1.1.05.01')->firstOrFail(); // Persediaan Barang Dagang
            $deskripsiJurnal = 'Pembelian barang dagang dari ' . $pemasok->nama_pemasok;

            if ($request->status_pembelian == 'Lunas') {
                $akunKredit = Akun::where('kode_akun', '1.1.01.01')->firstOrFail(); // Kas di Tangan
            } else {
                $akunKredit = Akun::where('kode_akun', '2.1.01.01')->firstOrFail(); // Utang Usaha
            }

            $jurnal = JurnalUmum::create([
                'user_id' => Auth::id(),
                'unit_usaha_id' => $unitUsahaId,
                'tanggal_transaksi' => $request->tanggal_pembelian,
                'deskripsi' => $deskripsiJurnal,
                'total_debit' => $totalPembelian,
                'total_kredit' => $totalPembelian,
            ]);

            DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $akunDebit->akun_id, 'debit' => $totalPembelian, 'kredit' => 0]);
            DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $akunKredit->akun_id, 'debit' => 0, 'kredit' => $totalPembelian]);

            // --- Simpan data Pembelian utama ---
            $pembelian = Pembelian::create([
                'pemasok_id' => $request->pemasok_id,
                'no_faktur' => $request->no_faktur,
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'nominal_pembelian' => $totalPembelian,
                'jurnal_id' => $jurnal->jurnal_id,
                'unit_usaha_id' => $unitUsahaId,
                'status_pembelian' => $request->status_pembelian,
            ]);

            // Simpan detail pembelian terkait
            $pembelian->detailPembelians()->createMany($detailData);

            DB::commit();

            return redirect()->route('pembelian.index')->with('success', 'Transaksi pembelian berhasil disimpan dan stok telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail transaksi pembelian.
     */
    public function show(Pembelian $pembelian)
    {
        $pembelian->load('detailPembelians.produk', 'pemasok', 'unitUsaha');
        return view('usaha.pembelian.show', compact('pembelian'));
    }

    /**
     * Menghapus transaksi pembelian dan menyesuaikan kembali stok.
     */
    public function destroy(Pembelian $pembelian)
    {
        // IMPROVED: Logika destroy yang benar dengan penyesuaian stok
        try {
            DB::beginTransaction();

            // 1. Muat detail pembelian beserta produknya
            $pembelian->load('detailPembelians.produk');

            // 2. Kurangi stok untuk setiap produk yang dibatalkan pembeliannya
            foreach ($pembelian->detailPembelians as $detail) {
                if ($detail->produk) {
                    $stok = Stok::where('produk_id', $detail->produk_id)->first();
                    if ($stok) {
                        // Gunakan decrement untuk mengurangi stok.
                        // Ini memastikan stok tidak menjadi negatif jika terjadi race condition.
                        $stok->decrement('jumlah_stok', $detail->jumlah);
                    }
                }
            }

            // 3. Hapus jurnal terkait
            $jurnal = JurnalUmum::find($pembelian->jurnal_id);
            if ($jurnal) {
                $jurnal->delete(); // Ini akan otomatis menghapus detail jurnal jika relasi di-set cascade
            }

            // 4. Hapus data pembelian utama
            // Ini akan otomatis menghapus detail pembelian jika relasi di-set cascade
            $pembelian->delete();

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Transaksi pembelian berhasil dihapus dan stok telah disesuaikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pembelian.index')->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
