<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Akun;
use App\Models\JurnalUmum;
use App\Models\DetailJurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualans = Penjualan::latest('tanggal_penjualan')->get();
        return view('usaha.penjualan.index', compact('penjualans'));
    }

    public function create()
    {
        $produks = Produk::orderBy('nama_produk')->get();
        return view('usaha.penjualan.create', compact('produks'));
    }

    public function store(Request $request)
    {
        // 1. Validasi data yang masuk
        $request->validate([
            'tanggal_penjualan' => 'required|date',
            'status_penjualan' => 'required|in:Lunas,Belum Lunas',
            'produk_id' => 'required|array|min:1',
            'produk_id.*' => 'required|exists:produks,produk_id',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|numeric|min:1',
        ]);

        // Gunakan DB Transaction untuk memastikan semua proses berhasil
        try {
            DB::beginTransaction();

            // 2. Hitung Total Penjualan & Siapkan Detail
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

            // 3. Buat Jurnal Akuntansi
            $akunPendapatan = Akun::where('kode_akun', '4-10100')->firstOrFail(); // Pendapatan Usaha Toko
            $deskripsiJurnal = 'Penjualan barang dagang dengan invoice sementara';

            if ($request->status_penjualan == 'Lunas') {
                $akunDebit = Akun::where('kode_akun', '1-10101')->firstOrFail(); // Kas di Tangan
            } else { // Kredit
                $akunDebit = Akun::where('kode_akun', '1-10200')->firstOrFail(); // Piutang Usaha
            }

            $jurnal = JurnalUmum::create([
                'bungdes_id' => 1, // Asumsi
                'user_id' => Auth::id(),
                'tanggal_transaksi' => $request->tanggal_penjualan,
                'deskripsi' => $deskripsiJurnal,
                'total_debit' => $totalPenjualan,
                'total_kredit' => $totalPenjualan,
            ]);

            // Debit: Kas atau Piutang
            DetailJurnal::create([
                'jurnal_id' => $jurnal->jurnal_id,
                'akun_id' => $akunDebit->akun_id,
                'debit' => $totalPenjualan,
                'kredit' => 0,
            ]);

            // Kredit: Pendapatan
            DetailJurnal::create([
                'jurnal_id' => $jurnal->jurnal_id,
                'akun_id' => $akunPendapatan->akun_id,
                'debit' => 0,
                'kredit' => $totalPenjualan,
            ]);
            
            // 4. Buat record Penjualan utama
            $penjualan = Penjualan::create([
                'no_invoice' => 'INV-' . time(), // Buat nomor invoice sementara
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'total_penjualan' => $totalPenjualan,
                'jurnal_id' => $jurnal->jurnal_id,
                'unit_usaha_id' => Produk::find($request->produk_id[0])->unit_usaha_id, // Ambil unit usaha dari produk pertama
                'nama_pelanggan' => $request->nama_pelanggan,
                'status_penjualan' => $request->status_penjualan,
            ]);
            
            // 5. Simpan detail penjualan
            $penjualan->detailPenjualans()->createMany($detailData);

            // Jika semua berhasil, konfirmasi transaksi
            DB::commit();

            return redirect()->route('penjualan.index')->with('success', 'Transaksi penjualan berhasil disimpan.');

        } catch (\Exception $e) {
            // Jika ada error, batalkan semua proses
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * ==========================================================
     * LENGKAPI METHOD INI
     * ==========================================================
     */
    public function show(Penjualan $penjualan)
    {
        // Load relasi agar data detail dan produknya bisa diakses di view
        $penjualan->load('detailPenjualans.produk');
        return view('usaha.penjualan.show', compact('penjualan'));
    }

     public function destroy(Penjualan $penjualan)
    {
        try {
            DB::beginTransaction();

            // 1. Ambil jurnal umum yang terkait dengan penjualan ini
            $jurnal = JurnalUmum::find($penjualan->jurnal_id);

            // 2. Hapus penjualan (detailnya akan terhapus otomatis karena cascade)
            $penjualan->delete();

            // 3. Hapus jurnal jika ada (detailnya juga akan terhapus otomatis)
            if ($jurnal) {
                $jurnal->delete();
            }

            DB::commit();

            return redirect()->route('penjualan.index')
                             ->with('success', 'Transaksi penjualan berhasil dibatalkan dan dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('penjualan.index')
                             ->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}