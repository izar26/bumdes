<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;

use App\Models\Tagihan;
use App\Models\Pelanggan;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TagihanController extends Controller
{
    /**
     * Menampilkan daftar semua tagihan.
     */
    public function index()
    {
        // Ambil data tagihan, eager load relasi 'pelanggan' untuk efisiensi query
        // Diurutkan berdasarkan yang terbaru, dan dibagi per 10 data per halaman
        $semua_tagihan = Tagihan::with('pelanggan')->latest()->paginate(10);

        // Arahkan ke view 'tagihan.index' dengan membawa data
        return view('usaha.tagihan.index', compact('semua_tagihan'));
    }

    /**
     * Menampilkan form untuk membuat tagihan baru.
     */
    public function create()
    {
        // Ambil semua data pelanggan dan petugas untuk ditampilkan di form (dropdown)
        $semua_pelanggan = Pelanggan::where('status_pelanggan', 'Aktif')->orderBy('nama')->get();
        $semua_petugas = Petugas::orderBy('nama_petugas')->get();

        return view('usaha.tagihan.create', compact('semua_pelanggan', 'semua_petugas'));
    }

    /**
     * Menyimpan tagihan baru ke dalam database.
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $validator = Validator::make($request->all(), [
            'pelanggan_id' => 'required|exists:pelanggan,id',
            'petugas_id' => 'nullable|exists:petugas,id',
            'periode_tagihan' => 'required|date',
            'meter_awal' => 'required|numeric|min:0',
            'meter_akhir' => 'required|numeric|gt:meter_awal',
            // Validasi untuk rincian (diasumsikan dikirim dalam bentuk array)
            'rincian' => 'required|array|min:1',
            'rincian.*.deskripsi' => 'required|string|max:255',
            'rincian.*.subtotal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Gunakan DB Transaction untuk memastikan semua query berhasil
        // Jika ada satu saja yang gagal, semua akan dibatalkan (rollback)
        DB::beginTransaction();
        try {
            // 1. Hitung total dan buat data tagihan utama
            $total_pemakaian = $request->meter_akhir - $request->meter_awal;
            $total_harus_dibayar = collect($request->rincian)->sum('subtotal');

            $tagihan = Tagihan::create([
                'pelanggan_id' => $request->pelanggan_id,
                'petugas_id' => $request->petugas_id,
                'periode_tagihan' => $request->periode_tagihan,
                'tanggal_cetak' => now(),
                'meter_awal' => $request->meter_awal,
                'meter_akhir' => $request->meter_akhir,
                'total_pemakaian_m3' => $total_pemakaian,
                'total_harus_dibayar' => $total_harus_dibayar,
                'status_pembayaran' => 'Belum Lunas',
            ]);

            // 2. Simpan setiap item rincian tagihan
            foreach ($request->rincian as $item) {
                $tagihan->rincian()->create([
                    'deskripsi' => $item['deskripsi'],
                    'kuantitas' => $item['kuantitas'] ?? 1,
                    'harga_satuan' => $item['harga_satuan'] ?? $item['subtotal'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit(); // Simpan semua perubahan jika berhasil

            return redirect()->route('usaha.tagihan.show', $tagihan)->with('success', 'Tagihan baru berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua jika ada error
            // Sebaiknya di-log errornya: Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.')->withInput();
        }
    }

    /**
     * Menampilkan detail satu tagihan.
     * Laravel otomatis mengambil data Tagihan berdasarkan ID di URL (Route Model Binding)
     */
    public function show(Tagihan $tagihan)
    {
        // Eager load semua relasi yang dibutuhkan di halaman detail
        $tagihan->load(['pelanggan', 'petugas', 'rincian']);

        return view('usaha.tagihan.show', compact('tagihan'));
    }

    /**
     * Menampilkan form untuk mengedit tagihan.
     */
    public function edit(Tagihan $tagihan)
    {
        // Sama seperti method create, kita butuh data pelanggan dan petugas
        $semua_pelanggan = Pelanggan::where('status_pelanggan', 'Aktif')->orderBy('nama')->get();
        $semua_petugas = Petugas::orderBy('nama_petugas')->get();

        // Eager load rincian tagihan untuk ditampilkan di form
        $tagihan->load('rincian');

        return view('usaha.tagihan.edit', compact('tagihan', 'semua_pelanggan', 'semua_petugas'));
    }

    /**
     * Memperbarui data tagihan di database.
     */
    public function update(Request $request, Tagihan $tagihan)
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_id' => 'required|exists:pelanggan,id',
            'petugas_id' => 'nullable|exists:petugas,id',
            'periode_tagihan' => 'required|date',
            'meter_awal' => 'required|numeric|min:0',
            'meter_akhir' => 'required|numeric|gt:meter_awal',
            'rincian' => 'required|array|min:1',
            'rincian.*.deskripsi' => 'required|string|max:255',
            'rincian.*.subtotal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // 1. Update data tagihan utama
            $total_pemakaian = $request->meter_akhir - $request->meter_awal;
            $total_harus_dibayar = collect($request->rincian)->sum('subtotal');

            $tagihan->update([
                'pelanggan_id' => $request->pelanggan_id,
                'petugas_id' => $request->petugas_id,
                'periode_tagihan' => $request->periode_tagihan,
                'meter_awal' => $request->meter_awal,
                'meter_akhir' => $request->meter_akhir,
                'total_pemakaian_m3' => $total_pemakaian,
                'total_harus_dibayar' => $total_harus_dibayar,
            ]);

            // 2. Hapus rincian lama dan buat yang baru dari input form
            $tagihan->rincian()->delete();
            foreach ($request->rincian as $item) {
                $tagihan->rincian()->create([
                    'deskripsi' => $item['deskripsi'],
                    'kuantitas' => $item['kuantitas'] ?? 1,
                    'harga_satuan' => $item['harga_satuan'] ?? $item['subtotal'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();

            return redirect()->route('usaha.tagihan.show', $tagihan)->with('success', 'Tagihan berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data.')->withInput();
        }
    }

    /**
     * Menghapus tagihan dari database.
     */
    public function destroy(Tagihan $tagihan)
    {
        try {
            // Karena di migrasi kita set 'onDelete('cascade')' untuk rincian,
            // maka saat tagihan dihapus, semua rinciannya akan ikut terhapus.
            $tagihan->delete();
            return redirect()->route('usaha.tagihan.index')->with('success', 'Tagihan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('usaha.tagihan.index')->with('error', 'Gagal menghapus tagihan.');
        }
    }
     public function cetakMassal(Request $request)
    {
        // 1. Validasi input filter dari form <select>
        $validator = Validator::make($request->all(), [
            'periode_bulan' => 'required|integer|between:1,12',
            'periode_tahun' => 'required|integer|digits:4',
        ]);

        // Jika validasi gagal, kembalikan pengguna ke halaman sebelumnya dengan pesan error
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Filter periode tidak valid. Silakan pilih bulan dan tahun.');
        }

        $periode = $request->periode_tahun . '-' . str_pad($request->periode_bulan, 2, '0', STR_PAD_LEFT) . '-01';

        $semua_tagihan = Tagihan::with(['pelanggan', 'petugas', 'rincian'])
            ->where('periode_tagihan', $periode)
            ->get();

        // Jika tidak ada tagihan yang ditemukan, kembalikan juga dengan pesan
        if ($semua_tagihan->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada tagihan yang ditemukan untuk periode tersebut.')->withInput();
        }

        // 4. Jika data ditemukan, kirim ke view khusus untuk dicetak
        return view('usaha.tagihan.cetak-massal', compact('semua_tagihan'));
    }

 public function cetakSelektif(Request $request)
    {
        // 1. Validasi input: pastikan 'tagihan_ids' dikirim, berupa array, dan tidak kosong.
        $request->validate([
            'tagihan_ids'   => 'required|array|min:1',
            'tagihan_ids.*' => 'exists:tagihan,id' // Pastikan setiap ID ada di database
        ], [
            'tagihan_ids.required' => 'Tidak ada tagihan yang dipilih. Silakan centang tagihan yang ingin dicetak.'
        ]);

        $selectedIds = $request->input('tagihan_ids');

        // 2. Ambil semua data tagihan berdasarkan ID yang dipilih dari checkbox.
        // Kita tetap EAGER LOAD relasi untuk efisiensi.
        $semua_tagihan = Tagihan::with(['pelanggan', 'petugas', 'rincian'])
            ->whereIn('id', $selectedIds)
            ->get();

        // 3. Kirim data ke view cetak yang SAMA SEPERTI SEBELUMNYA.
        // Kita bisa menggunakan ulang view `cetak-massal.blade.php` karena ia hanya
        // butuh variabel `$semua_tagihan` untuk di-loop. Sangat efisien!
        return view('usaha.tagihan.cetak-massal', compact('semua_tagihan'));
    }
    
}
