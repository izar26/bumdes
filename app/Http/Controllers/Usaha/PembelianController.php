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
    public function index()
    {
        $user = Auth::user();
        $pembelianQuery = Pembelian::with('pemasok')->latest('tanggal_pembelian');

        // Filter berdasarkan peran pengguna
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usaha_id');
            $pembelianQuery->whereIn('unit_usaha_id', $unitUsahaIds);
        }
        // Bendahara dan peran di atasnya bisa melihat semua

        $pembelians = $pembelianQuery->get();
        return view('usaha.pembelian.index', compact('pembelians'));
    }

    public function create()
    {
        $user = Auth::user();
        $pemasokQuery = Pemasok::orderBy('nama_pemasok');
        $produkQuery = Produk::orderBy('nama_produk');

        // Filter pemasok dan produk berdasarkan unit usaha yang dikelola pengguna
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usaha_id');
            $pemasokQuery->whereIn('unit_usaha_id', $unitUsahaIds);
            $produkQuery->whereIn('unit_usaha_id', $unitUsahaIds);
        }

        $pemasoks = $pemasokQuery->get();
        $produks = $produkQuery->get();
        
        return view('usaha.pembelian.create', compact('pemasoks', 'produks'));
    }

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

                // Logika Update Stok
                $stok = Stok::where('produk_id', $id_produk)->first();
                if ($stok) {
                    $stok->increment('jumlah_stok', $jumlah);
                }
            }

            $pemasok = Pemasok::find($request->pemasok_id);

            // Buat Jurnal Akuntansi
            $akunDebit = Akun::where('kode_akun', '1.1.05.01')->firstOrFail(); // Persediaan Barang Dagang
            $deskripsiJurnal = 'Pembelian barang dagang dari ' . $pemasok->nama_pemasok;

            if ($request->status_pembelian == 'Lunas') {
                $akunKredit = Akun::where('kode_akun', '1.1.01.01')->firstOrFail(); // Kas di Tangan
            } else {
                $akunKredit = Akun::where('kode_akun', '2.1.01.01')->firstOrFail(); // Utang Usaha
            }

            $jurnal = JurnalUmum::create([
                'user_id' => Auth::id(),
                'unit_usaha_id' => $pemasok->unit_usaha_id,
                'tanggal_transaksi' => $request->tanggal_pembelian,
                'deskripsi' => $deskripsiJurnal,
                'total_debit' => $totalPembelian,
                'total_kredit' => $totalPembelian,
            ]);

            DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $akunDebit->akun_id, 'debit' => $totalPembelian, 'kredit' => 0]);
            DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $akunKredit->akun_id, 'debit' => 0, 'kredit' => $totalPembelian]);

            // Simpan data Pembelian utama
            $pembelian = Pembelian::create([
                'pemasok_id' => $request->pemasok_id,
                'no_faktur' => $request->no_faktur,
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'nominal_pembelian' => $totalPembelian,
                'jurnal_id' => $jurnal->jurnal_id,
                'unit_usaha_id' => $pemasok->unit_usaha_id,
                'status_pembelian' => $request->status_pembelian,
            ]);

            $pembelian->detailPembelians()->createMany($detailData);
            
            DB::commit();

            return redirect()->route('pembelian.index')->with('success', 'Transaksi pembelian berhasil disimpan dan stok telah diperbarui.');

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
            return redirect()->route('pembelian.index')->with('success', 'Transaksi pembelian berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pembelian.index')->with('error', 'Gagal menghapus transaksi.');
        }
    }
}
