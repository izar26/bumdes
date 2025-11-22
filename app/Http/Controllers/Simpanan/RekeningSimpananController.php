<?php

namespace App\Http\Controllers\Simpanan;

use App\Models\Anggota;
use App\Models\RekeningSimpanan;
use App\Models\JenisSimpanan; // Import Model Jenis Simpanan
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RekeningSimpananController extends Controller
{
    /**
     * Menampilkan daftar semua rekening simpanan.
     */
    public function index()
    {
        // Ambil semua Rekening Simpanan dengan data Anggota terkait
        $rekenings = RekeningSimpanan::with('anggota', 'jenisSimpanan')->get();

        // Pastikan path view sesuai dengan struktur folder Anda
        return view('simpanan.rekening.index', compact('rekenings'));
    }

    /**
     * Menampilkan form untuk membuka rekening baru.
     */
    public function create()
    {
        // Kita butuh data Anggota dan Jenis Simpanan untuk dropdown di form
        $anggotas = Anggota::all();
        $jenisSimpanans = JenisSimpanan::all();

        return view('simpanan.rekening.create', compact('anggotas', 'jenisSimpanans'));
    }

    /**
     * Menyimpan rekening baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'anggota_id' => 'required|exists:anggotas,anggota_id',
            'jenis_simpanan_id' => 'required|exists:jenis_simpanans,jenis_simpanan_id', // Sesuaikan nama tabel jika 'jenis_simpanans'
        ]);

        // 1. Cek Duplikasi (Opsional)
        // Apakah anggota ini sudah punya rekening untuk jenis simpanan ini?
        // Hapus blok ini jika anggota boleh punya banyak rekening untuk jenis yang sama.
        $cek = RekeningSimpanan::where('anggota_id', $request->anggota_id)
                ->where('jenis_simpanan_id', $request->jenis_simpanan_id)
                ->exists();

        if ($cek) {
            return back()->with('error', 'Anggota ini sudah memiliki rekening untuk jenis simpanan tersebut.');
        }

        // 2. Generate Nomor Rekening Otomatis
        // Format: [KODE_JENIS].[ID_ANGGOTA].[RANDOM] -> Contoh: SW.0001.882
        $jenis = JenisSimpanan::find($request->jenis_simpanan_id);

        // Padding ID anggota dengan nol di depan (misal ID 1 jadi 0001)
        $idFormatted = str_pad($request->anggota_id, 4, '0', STR_PAD_LEFT);

        $no_rekening = $jenis->kode_jenis . '.' . $idFormatted . '.' . mt_rand(100, 999);

        // 3. Simpan ke Database
        RekeningSimpanan::create([
            'anggota_id' => $request->anggota_id,
            'jenis_simpanan_id' => $request->jenis_simpanan_id,
            'no_rekening' => $no_rekening,
            'saldo' => 0, // Saldo awal selalu 0 saat buka rekening baru
            'status' => 'aktif',
        ]);

        return redirect()->route('simpanan.rekening.index')->with('success', 'Rekening berhasil dibuka! Nomor: ' . $no_rekening);
    }

    /**
     * Menampilkan detail rekening dan histori transaksi anggota.
     */
    public function show($anggota_id)
    {
        $anggota = Anggota::with([
            'rekeningSimpanan.transaksiSimpanan' => function($query) {
                // Urutkan histori transaksi dari yang terbaru
                $query->orderBy('tanggal_transaksi', 'desc');
            },
            'rekeningSimpanan.jenisSimpanan'
        ])->find($anggota_id);

        if (!$anggota) {
            return back()->with('error', 'Data anggota tidak ditemukan.');
        }

        return view('simpanan.rekening.show', compact('anggota'));
    }
}
