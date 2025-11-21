<?php

namespace App\Http\Controllers\Simpanan;

use App\Models\PengajuanPinjaman;
use App\Models\AngsuranPinjaman;
use App\Models\Anggota; // Import Model Anggota
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class PengajuanPinjamanController extends Controller
{
    /**
     * Menampilkan daftar semua pengajuan pinjaman.
     */
    public function index()
    {
        // Ambil semua pinjaman dengan data anggota terkait, diurutkan dari yang terbaru
        $pinjamans = PengajuanPinjaman::with('anggota')->orderBy('tanggal_pengajuan', 'desc')->get();
        return view('pinjaman.pengajuan.index', compact('pinjamans'));
    }

    /**
     * Menampilkan formulir untuk membuat pengajuan pinjaman baru.
     */
    public function create()
    {
        // Kita tidak akan load semua anggota di sini, karena menggunakan AJAX search di view.
        // Namun, jika diperlukan dropdown, Anda bisa mengambil data anggota di sini.
        $anggotas = Anggota::all();
        return view('pinjaman.pengajuan.create', compact('anggotas')); // compact('anggotas') jika menggunakan dropdown
    }

    /**
     * Menyimpan pengajuan pinjaman baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'anggota_id' => ['required', 'exists:anggotas,anggota_id'],
            'tanggal_pengajuan' => ['required', 'date'],
            'jumlah_pinjaman' => ['required', 'numeric', 'min:100000'],
            'tenor' => ['required', 'integer', 'min:1'],
            'tujuan_pinjaman' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $sequence = PengajuanPinjaman::count() + 1;
            $no_pinjaman = 'PNJ-' . date('Ymd') . '-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

            // --- PERBAIKAN: Hitung Angsuran per Bulan di sini ---
            // Kita gunakan ceil() agar pembulatan ke atas, sama seperti logika di approve
            $jumlahAngsuran = ceil($validatedData['jumlah_pinjaman'] / $validatedData['tenor']);

            $pinjaman = PengajuanPinjaman::create([
                'anggota_id' => $validatedData['anggota_id'],
                'no_pinjaman' => $no_pinjaman,
                'tanggal_pengajuan' => $validatedData['tanggal_pengajuan'],
                'jumlah_pinjaman' => $validatedData['jumlah_pinjaman'],
                'tenor' => $validatedData['tenor'],

                // Masukkan hasil perhitungan ke database
                'jumlah_angsuran_per_bulan' => $jumlahAngsuran,

                'tujuan_pinjaman' => $validatedData['tujuan_pinjaman'],
                'status' => 'pending',
                'user_id_admin_input' => auth()->user()->user_id,
            ]);

            return redirect()->route('simpanan.pinjaman.index')->with('success', 'Pengajuan pinjaman berhasil dibuat.');

        } catch (\Exception $e) {
            \Log::error("Store Pengajuan Pinjaman Gagal: " . $e->getMessage());
            return back()->with('error', 'Gagal membuat pengajuan pinjaman: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail pengajuan pinjaman dan jadwal angsuran.
     */
    public function show(PengajuanPinjaman $pengajuanPinjaman)
    {
        // Load data anggota dan angsuran untuk ditampilkan di detail.
        $pengajuanPinjaman->load('anggota', 'angsuran');
        return view('pinjaman.pengajuan.show', compact('pengajuanPinjaman'));
    }

    /**
     * Menampilkan formulir untuk mengedit pengajuan pinjaman.
     * Hanya boleh diedit jika status masih 'pending'.
     */
    public function edit(PengajuanPinjaman $pengajuanPinjaman)
    {
        if ($pengajuanPinjaman->status !== 'pending') {
            return redirect()->route('pinjaman.show', $pengajuanPinjaman->pinjaman_id)->with('error', 'Pinjaman hanya bisa diedit saat status masih Pending.');
        }
        return view('pinjaman.pengajuan.edit', compact('pengajuanPinjaman'));
    }

    /**
     * Memperbarui pengajuan pinjaman yang ada.
     * Hanya boleh diupdate jika status masih 'pending'.
     */
    public function update(Request $request, PengajuanPinjaman $pengajuanPinjaman)
    {
        if ($pengajuanPinjaman->status !== 'pending') {
            return redirect()->route('pinjaman.show', $pengajuanPinjaman->pinjaman_id)->with('error', 'Pinjaman hanya bisa diupdate saat status masih Pending.');
        }

        $validatedData = $request->validate([
            'jumlah_pinjaman' => ['required', 'numeric', 'min:100000'],
            'tenor' => ['required', 'integer', 'min:1'],
            'tujuan_pinjaman' => ['nullable', 'string', 'max:500'],
            // anggota_id dan tanggal_pengajuan tidak boleh diubah
        ]);

        try {
            $pengajuanPinjaman->update($validatedData);
            return redirect()->route('pinjaman.show', $pengajuanPinjaman->pinjaman_id)->with('success', 'Pengajuan pinjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui pengajuan pinjaman: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus pengajuan pinjaman.
     * Hanya boleh dihapus jika status masih 'pending'.
     */
    public function destroy(PengajuanPinjaman $pengajuanPinjaman)
    {
        if ($pengajuanPinjaman->status !== 'pending') {
            return back()->with('error', 'Pinjaman hanya bisa dihapus jika status masih Pending.');
        }

        try {
            $pengajuanPinjaman->delete();
            return redirect()->route('simpanan.pinjaman.index')->with('success', 'Pengajuan pinjaman berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus pengajuan pinjaman: ' . $e->getMessage());
        }
    }

    /**
     * Logika Persetujuan Pinjaman dan Pembuatan Jadwal Angsuran.
     */
    public function approve(Request $request, PengajuanPinjaman $pengajuanPinjaman)
    {
        if ($pengajuanPinjaman->status !== 'pending') {
            return back()->with('error', 'Pinjaman sudah diproses (bukan status pending).');
        }

        // --- VALIDASI TAMBAHAN (Opsional, jika ingin tanggal cair dimasukkan) ---
        $request->validate([
            'tanggal_cair' => ['nullable', 'date'],
        ]);
        // -----------------------------------------------------------------------

        try {
            DB::beginTransaction();

            // 1. Hitung Angsuran Pokok (TANPA BUNGA/Margin)
            // Menggunakan ceil agar sisa pembagian dibulatkan ke atas untuk angsuran
            $jumlahAngsuran = ceil($pengajuanPinjaman->jumlah_pinjaman / $pengajuanPinjaman->tenor);
            $tanggalCair = $request->input('tanggal_cair', now());

            // Tentukan Jatuh Tempo pertama (misalnya, 1 bulan setelah tanggal cair)
            $tanggalJatuhTempoAwal = \Carbon\Carbon::parse($tanggalCair)->addMonth()->startOfDay();

            // 2. Update Status Pinjaman
            $pengajuanPinjaman->status = 'approved';
            $pengajuanPinjaman->tanggal_approval = now();
            $pengajuanPinjaman->tanggal_pencairan = $tanggalCair;
            $pengajuanPinjaman->jumlah_angsuran_per_bulan = $jumlahAngsuran;
            $pengajuanPinjaman->user_id_admin_approve = auth()->user()->user_id;
            $pengajuanPinjaman->save();

            // 3. Generate Jadwal Angsuran
            $sisaPokok = $pengajuanPinjaman->jumlah_pinjaman;

            for ($i = 1; $i <= $pengajuanPinjaman->tenor; $i++) {

                $angsuranPokokSaatIni = ($i == $pengajuanPinjaman->tenor)
                    ? $sisaPokok // Angsuran terakhir mengambil sisa pokok
                    : $jumlahAngsuran;

                $sisaPokok -= $angsuranPokokSaatIni;

                // Tanggal Jatuh Tempo: Tambah (i-1) bulan dari tanggal jatuh tempo awal
                $jatuhTempo = $tanggalJatuhTempoAwal->copy()->addMonths($i - 1);

                AngsuranPinjaman::create([
                    'pinjaman_id' => $pengajuanPinjaman->pinjaman_id,
                    'angsuran_ke' => $i,
                    'jumlah_bayar' => $angsuranPokokSaatIni, // Disimpan sebagai angsuran pokok
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                    'status' => 'belum_bayar',
                    // user_id_admin_terima (dikosongkan dulu)
                ]);
            }

            // *OPSIONAL: Di sini Anda bisa menambahkan logika Jurnal/Kas untuk mencatat Uang Keluar (Pencairan)*

            DB::commit();

            return redirect()->route('pinjaman.show', $pengajuanPinjaman->pinjaman_id)->with('success', 'Pinjaman berhasil disetujui dan jadwal angsuran dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Approval Pinjaman Gagal: " . $e->getMessage());
            return back()->with('error', 'Persetujuan pinjaman gagal: ' . $e->getMessage());
        }
    }

    /**
     * Menolak pengajuan pinjaman.
     */
    public function reject(PengajuanPinjaman $pengajuanPinjaman)
    {
        if ($pengajuanPinjaman->status === 'pending') {
            $pengajuanPinjaman->update(['status' => 'rejected']);
            return back()->with('success', 'Pengajuan pinjaman berhasil ditolak.');
        }
        return back()->with('error', 'Pinjaman tidak dalam status pending.');
    }
}
