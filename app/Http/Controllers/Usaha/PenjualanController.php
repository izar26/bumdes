<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Produk;
use App\Models\Akun;
use App\Models\JurnalUmum;
use App\Models\DetailJurnal;
use App\Models\Stok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PenjualanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $penjualanQuery = Penjualan::with('unitUsaha')->latest('tanggal_penjualan');

        // Filter berdasarkan peran pengguna
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usaha_id');
            $penjualanQuery->whereIn('unit_usaha_id', $unitUsahaIds);
        }
        // Bendahara dan peran di atasnya bisa melihat semua

        $penjualans = $penjualanQuery->get();
        return view('usaha.penjualan.index', compact('penjualans'));
    }

    public function create()
    {
        $user = Auth::user();
        $produkQuery = Produk::orderBy('nama_produk');

        // Filter produk berdasarkan unit usaha yang dikelola pengguna
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usaha_id');
            $produkQuery->whereIn('unit_usaha_id', $unitUsahaIds);
        }

        $produks = $produkQuery->get();
        return view('usaha.penjualan.create', compact('produks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_penjualan' => 'required|date',
            'status_penjualan' => 'required|in:Lunas,Belum Lunas',
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => 'required|exists:produks,produk_id',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            // 1. Pengecekan Stok
            foreach ($request->produk_id as $key => $id_produk) {
                $produk = Produk::find($id_produk);
                $stok = Stok::where('produk_id', $id_produk)->first();
                $jumlahDiminta = $request->jumlah[$key];

                if (!$stok || $stok->jumlah_stok < $jumlahDiminta) {
                    throw ValidationException::withMessages([
                        'produk_id' => 'Stok untuk produk "' . $produk->nama_produk . '" tidak mencukupi. Sisa stok: ' . ($stok->jumlah_stok ?? 0),
                    ]);
                }
            }
            
            // 2. Hitung Total & Siapkan Detail
            $totalPenjualan = 0;
            $detailData = [];
            foreach ($request->produk_id as $key => $id_produk) {
                $produk = Produk::find($id_produk);
                $jumlah = $request->jumlah[$key];
                $subtotal = $jumlah * $produk->harga_jual;
                $totalPenjualan += $subtotal;

                $detailData[] = [
                    'produk_id' => $id_produk,
                    'jumlah' => $jumlah,
                    'harga_unit' => $produk->harga_jual,
                    'subtotal' => $subtotal,
                ];
            }
            
            // Tentukan Unit Usaha dari produk pertama yang dijual
            $unitUsahaId = Produk::find($request->produk_id[0])->unit_usaha_id;

            // 3. Buat Jurnal Akuntansi
            $akunPendapatan = Akun::where('kode_akun', '4.2.01.91')->firstOrFail();
            $deskripsiJurnal = 'Penjualan barang dagang';

            if ($request->status_penjualan == 'Lunas') {
                $akunDebit = Akun::where('kode_akun', '1.1.01.01')->firstOrFail();
            } else {
                $akunDebit = Akun::where('kode_akun', '1.1.03.01')->firstOrFail();
            }

            $jurnal = JurnalUmum::create([
                'user_id' => Auth::id(),
                'unit_usaha_id' => $unitUsahaId,
                'tanggal_transaksi' => $request->tanggal_penjualan,
                'deskripsi' => $deskripsiJurnal,
                'total_debit' => $totalPenjualan,
                'total_kredit' => $totalPenjualan,
            ]);

            DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $akunDebit->akun_id, 'debit' => $totalPenjualan, 'kredit' => 0]);
            DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $akunPendapatan->akun_id, 'debit' => 0, 'kredit' => $totalPenjualan]);
            
            // 4. Buat record Penjualan utama
            $penjualan = Penjualan::create([
                'no_invoice' => 'INV-' . time(),
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'total_penjualan' => $totalPenjualan,
                'jurnal_id' => $jurnal->jurnal_id,
                'unit_usaha_id' => $unitUsahaId,
                'nama_pelanggan' => $request->nama_pelanggan,
                'status_penjualan' => $request->status_penjualan,
            ]);
            
            // 5. Simpan detail & kurangi stok
            $penjualan->detailPenjualans()->createMany($detailData);
            foreach ($request->produk_id as $key => $id_produk) {
                $jumlahDijual = $request->jumlah[$key];
                $stok = Stok::where('produk_id', $id_produk)->first();
                $stok->decrement('jumlah_stok', $jumlahDijual);
            }

            DB::commit();
            return redirect()->route('penjualan.index')->with('success', 'Transaksi penjualan berhasil disimpan dan stok telah diperbarui.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Penjualan $penjualan)
    {
        $penjualan->load('detailPenjualans.produk', 'unitUsaha');
        return view('usaha.penjualan.show', compact('penjualan'));
    }

    public function destroy(Penjualan $penjualan)
    {
        try {
            DB::beginTransaction();
            $jurnal = JurnalUmum::find($penjualan->jurnal_id);
            $penjualan->delete();
            if ($jurnal) {
                $jurnal->delete();
            }
            DB::commit();
            return redirect()->route('penjualan.index')->with('success', 'Transaksi penjualan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('penjualan.index')->with('error', 'Gagal menghapus transaksi.');
        }
    }
}
