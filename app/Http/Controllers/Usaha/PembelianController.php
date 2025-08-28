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
use App\Models\Bungdes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;

class PembelianController extends Controller
{
    /**
     * Helper function untuk otorisasi berdasarkan unit usaha.
     * @param int|array $unitUsahaId
     * @param string $message
     * @throws AuthorizationException
     */
    private function authorizeUserUnitUsaha($unitUsahaId, $message = 'Anda tidak memiliki izin untuk mengakses data ini.')
    {
        $user = Auth::user();
        if (!$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
            $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            if (is_array($unitUsahaId)) {
                $check = collect($unitUsahaId)->every(function($id) use ($managedUnitUsahaIds) {
                    return $managedUnitUsahaIds->contains($id);
                });
                if (!$check) {
                    throw new AuthorizationException($message);
                }
            } else {
                if (!$managedUnitUsahaIds->contains($unitUsahaId)) {
                    throw new AuthorizationException($message);
                }
            }
        }
    }

    /**
     * Menampilkan daftar transaksi pembelian.
     */
    public function index()
    {
        $user = Auth::user();
        $pembelianQuery = Pembelian::with('pemasok', 'unitUsaha')->latest('tanggal_pembelian');

        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $pembelianQuery->whereIn('unit_usaha_id', $unitUsahaIds);
        }

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

        $unitUsahaIds = [];
        if ($user->hasRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id')->toArray();
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
        $user = Auth::user();
        $rules = [
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
        ];

        // Aturan validasi tambahan untuk otorisasi
        if (!$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
            $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $rules['pemasok_id'] = [
                'required',
                Rule::exists('pemasoks', 'pemasok_id')->whereIn('unit_usaha_id', $managedUnitUsahaIds)
            ];
            $rules['produk_id.*'] = [
                'required',
                Rule::exists('produks', 'produk_id')->whereIn('unit_usaha_id', $managedUnitUsahaIds)
            ];
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $totalPembelian = 0;
            $detailData = [];

            // Tentukan Unit Usaha dari pemasok yang dipilih
            $pemasok = Pemasok::findOrFail($request->pemasok_id);
            $unitUsahaId = $pemasok->unit_usaha_id;

            // Otorisasi: Verifikasi unit usaha dari pemasok
            $this->authorizeUserUnitUsaha($unitUsahaId, 'Anda tidak memiliki izin untuk membuat pembelian di unit usaha ini.');

            // Otorisasi: Verifikasi setiap produk
            foreach ($request->produk_id as $id_produk) {
                $produk = Produk::findOrFail($id_produk);
                $this->authorizeUserUnitUsaha($produk->unit_usaha_id, 'Salah satu produk tidak berada di unit usaha yang dikelola.');
            }

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
                $stok = Stok::firstOrNew(['produk_id' => $id_produk, 'unit_usaha_id' => $unitUsahaId]);
                $stok->jumlah_stok += $jumlah;
                $stok->save();
            }

            // --- Jurnal Akuntansi ---
            $akunDebit = Akun::where('kode_akun', '1.1.05.01')->firstOrFail();
            $deskripsiJurnal = 'Pembelian barang dagang dari ' . $pemasok->nama_pemasok;

            if ($request->status_pembelian == 'Lunas') {
                $akunKredit = Akun::where('kode_akun', '1.1.01.01')->firstOrFail();
            } else {
                $akunKredit = Akun::where('kode_akun', '2.1.01.01')->firstOrFail();
            }

            $jurnal = JurnalUmum::create([
                'user_id' => Auth::id(),
                'unit_usaha_id' => $unitUsahaId,
                'tanggal_transaksi' => $request->tanggal_pembelian,
                'deskripsi' => $deskripsiJurnal,
                'total_debit' => $totalPembelian,
                'total_kredit' => $totalPembelian,
                'status' => 'menunggu', // Status awal selalu menunggu persetujuan
            ]);

            DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $akunDebit->akun_id, 'debit' => $totalPembelian, 'kredit' => 0]);
            DetailJurnal::create(['jurnal_id' => $jurnal->jurnal_id, 'akun_id' => $akunKredit->akun_id, 'debit' => 0, 'kredit' => $totalPembelian]);

            $pembelian = Pembelian::create([
                'pemasok_id' => $request->pemasok_id,
                'no_faktur' => $request->no_faktur,
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'nominal_pembelian' => $totalPembelian,
                'jurnal_id' => $jurnal->jurnal_id,
                'unit_usaha_id' => $unitUsahaId,
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

    /**
     * Menampilkan detail transaksi pembelian.
     */
    public function show(Pembelian $pembelian)
    {
        // Otorisasi: Pastikan user punya hak akses ke pembelian ini
        $this->authorizeUserUnitUsaha($pembelian->unit_usaha_id, 'Anda tidak memiliki izin untuk melihat detail pembelian ini.');
    $bumdes = Bungdes::first();

        $pembelian->load('detailPembelians.produk', 'pemasok', 'unitUsaha');
        return view('usaha.pembelian.show', compact('pembelian', 'bumdes'));
    }

    /**
     * Menghapus transaksi pembelian dan menyesuaikan kembali stok.
     */
    public function destroy(Pembelian $pembelian)
    {
        // Otorisasi: Pastikan user punya hak akses ke pembelian ini
        $this->authorizeUserUnitUsaha($pembelian->unit_usaha_id, 'Anda tidak memiliki izin untuk menghapus transaksi ini.');

        try {
            DB::beginTransaction();

            // Cek otorisasi jurnal sebelum dihapus
            $jurnal = JurnalUmum::find($pembelian->jurnal_id);
            if ($jurnal && ($jurnal->status === 'disetujui' || $jurnal->status === 'ditolak')) {
                throw new AuthorizationException('Transaksi pembelian tidak dapat dihapus karena jurnal terkait sudah diverifikasi. Silakan hubungi admin keuangan.');
            }

            $pembelian->load('detailPembelians.produk');

            foreach ($pembelian->detailPembelians as $detail) {
                if ($detail->produk) {
                    $stok = Stok::where('produk_id', $detail->produk_id)->first();
                    if ($stok) {
                        $stok->decrement('jumlah_stok', $detail->jumlah);
                    }
                }
            }

            if ($jurnal) {
                $jurnal->delete();
            }

            $pembelian->delete();

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Transaksi pembelian berhasil dihapus dan stok telah disesuaikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pembelian.index')->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
