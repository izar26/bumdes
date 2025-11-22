<?php

namespace App\Http\Controllers\Simpanan;

use App\Models\PengajuanPinjaman;
use App\Models\AngsuranPinjaman;
use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class PengajuanPinjamanController extends Controller
{
    /**
     * Menampilkan daftar semua pinjaman.
     */
    public function index()
    {
        // Tambahkan 'angsuran' di dalam with()
        $pinjamans = PengajuanPinjaman::with(['anggota', 'angsuran'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pinjaman.pengajuan.index', compact('pinjamans'));
    }

    /**
     * Menampilkan formulir pencatatan pinjaman baru.
     */
    public function create()
    {
        $anggotas = Anggota::all(); // Atau gunakan query yang lebih spesifik jika perlu
        return view('pinjaman.pengajuan.create', compact('anggotas'));
    }

    /**
     * Menyimpan pinjaman baru & LANGSUNG Generate Angsuran.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'anggota_id' => ['required', 'exists:anggotas,anggota_id'],
            'tanggal_pengajuan' => ['required', 'date'], // Ini dianggap Tanggal Cair
            'jumlah_pinjaman' => ['required', 'numeric', 'min:100000'],
            'tenor' => ['required', 'integer', 'min:1'],
            'tujuan_pinjaman' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::beginTransaction();

            // 1. Persiapan Data Header Pinjaman
            $sequence = PengajuanPinjaman::count() + 1;
            $no_pinjaman = 'PNJ-' . date('Ymd') . '-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

            // Hitung angsuran per bulan (pembulatan ke atas)
            $jumlahAngsuran = ceil($validatedData['jumlah_pinjaman'] / $validatedData['tenor']);

            // Tanggal Jatuh Tempo Pertama = 1 Bulan setelah Tanggal Pengajuan (Pencairan)
            $tanggalCair = $validatedData['tanggal_pengajuan'];
            $tanggalJatuhTempoAwal = Carbon::parse($tanggalCair)->addMonth()->startOfDay();

            // 2. Create Pinjaman (Langsung Status Approved / Active)
            $pinjaman = PengajuanPinjaman::create([
                'anggota_id' => $validatedData['anggota_id'],
                'no_pinjaman' => $no_pinjaman,
                'tanggal_pengajuan' => $tanggalCair,
                'tanggal_pencairan' => $tanggalCair, // Langsung set cair
                'tanggal_approval' => now(),         // Langsung set approve saat ini
                'jumlah_pinjaman' => $validatedData['jumlah_pinjaman'],
                'tenor' => $validatedData['tenor'],
                'jumlah_angsuran_per_bulan' => $jumlahAngsuran,
                'tujuan_pinjaman' => $validatedData['tujuan_pinjaman'],
                'status' => 'approved', // Status langsung Approved/Aktif
                'user_id_admin_input' => auth()->user()->user_id ?? null, // Sesuaikan dengan field user login
                'user_id_admin_approve' => auth()->user()->user_id ?? null, // Auto approve by creator
            ]);

            // 3. Generate Jadwal Angsuran Langsung
            $sisaPokok = $validatedData['jumlah_pinjaman'];

            for ($i = 1; $i <= $validatedData['tenor']; $i++) {
                // Angsuran terakhir menutupi sisa (jika ada selisih pembulatan)
                $angsuranPokokSaatIni = ($i == $validatedData['tenor'])
                    ? $sisaPokok
                    : $jumlahAngsuran;

                $sisaPokok -= $angsuranPokokSaatIni;

                $jatuhTempo = $tanggalJatuhTempoAwal->copy()->addMonths($i - 1);

                AngsuranPinjaman::create([
                    'pinjaman_id' => $pinjaman->pinjaman_id,
                    'angsuran_ke' => $i,
                    'jumlah_bayar' => $angsuranPokokSaatIni,
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                    'status' => 'belum_bayar',
                ]);
            }

            DB::commit();

            return redirect()->route('simpanan.pinjaman.index')
                ->with('success', 'Pinjaman berhasil dicatat dan jadwal angsuran telah dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Store Pinjaman Gagal: " . $e->getMessage());
            return back()->with('error', 'Gagal mencatat pinjaman: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PengajuanPinjaman $pengajuanPinjaman)
    {
        $pengajuanPinjaman->load('anggota', 'angsuran');
        return view('pinjaman.pengajuan.show', compact('pengajuanPinjaman'));
    }

    // --- Method Edit & Destroy (Opsional untuk mode catat-mencatat) ---

    public function destroy(PengajuanPinjaman $pengajuanPinjaman)
    {
        // Hapus pinjaman beserta angsurannya (Cascade delete sebaiknya diatur di database,
        // tapi ini handle manual via code jika belum diset di migration)

        try {
            DB::beginTransaction();
            $pengajuanPinjaman->angsuran()->delete(); // Hapus anak (angsuran) dulu
            $pengajuanPinjaman->delete();             // Hapus induk
            DB::commit();

            return redirect()->route('simpanan.pinjaman.index')->with('success', 'Data pinjaman berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // Method approve() dan reject() DIHAPUS karena tidak dipakai lagi.
}
