<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\KasBank;
use App\Models\Akun;
use App\Models\TransaksiKasBank; // Diperlukan untuk show
use App\Models\DetailJurnal; // Diperlukan untuk perhitungan saldo
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class KasBankController extends Controller
{
    /**
     * Tambahkan middleware otorisasi untuk semua method
     */
    public function __construct()
    {
        $this->middleware('role:bendahara_bumdes|admin_bumdes|admin_unit_usaha|manajer_unit_usaha');
    }

    /**
     * Menampilkan daftar kas/bank yang dikelola user.
     */
    public function index()
    {
        $user = Auth::user();
        $kasBankQuery = KasBank::with('akun');

        // Filter berdasarkan unit usaha yang dikelola, jika bukan admin BUMDes
        if (!$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
            $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            // Asumsi model KasBank memiliki relasi dengan UnitUsaha atau akunnya
            $kasBankQuery->whereIn('unit_usaha_id', $managedUnitUsahaIds);
        }

        $kasBanks = $kasBankQuery->get();
        return view('keuangan.kas_bank.index', compact('kasBanks'));
    }

    /**
     * Menampilkan form untuk membuat akun Kas/Bank baru.
     */
    public function create()
    {
        // Hanya Admin BUMDes yang seharusnya bisa membuat akun kas baru
        if (!Auth::user()->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk membuat akun kas/bank baru.');
        }

        $akun_list = Akun::whereIn('tipe_akun', ['Aset'])
                         ->where('nama_akun', 'like', '%Kas%')
                         ->orWhere('nama_akun', 'like', '%Bank%')
                         ->where('is_header', 0)
                         ->get();
        return view('keuangan.kas_bank.create', compact('akun_list'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk membuat akun kas/bank baru.');
        }

        $request->validate([
            'nama_akun_kas_bank' => 'required|string|max:255',
            'akun_id' => 'required|exists:akuns,akun_id|unique:kas_banks,akun_id',
            'nomor_rekening' => 'nullable|string|max:100',
            'saldo_saat_ini' => 'required|numeric|min:0',
        ]);

        // Simpan juga unit_usaha_id agar relasi jelas
        $unitUsahaId = Auth::user()->unitUsahas->first()->unit_usaha_id ?? null;

        KasBank::create([
            'nama_akun_kas_bank' => $request->nama_akun_kas_bank,
            'akun_id' => $request->akun_id,
            'nomor_rekening' => $request->nomor_rekening,
            'saldo_saat_ini' => $request->saldo_saat_ini,
            'user_id' => Auth::id(),
            'unit_usaha_id' => $unitUsahaId,
        ]);

        return redirect()->route('kas-bank.index')->with('success', 'Akun Kas/Bank baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail spesifik resource.
     */
    public function show(KasBank $kasBank)
    {
        // Otorisasi: Pastikan user punya hak akses ke kasBank ini
        $user = Auth::user();
        if (!$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
            $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            if (!$managedUnitUsahaIds->contains($kasBank->unit_usaha_id)) {
                throw new AuthorizationException('Anda tidak memiliki izin untuk melihat detail akun kas/bank ini.');
            }
        }

        // REKOMENDASI: Hitung saldo secara dinamis
        $saldoAwal = DetailJurnal::where('akun_id', $kasBank->akun_id)
                                 ->join('jurnal_umums', 'detail_jurnals.jurnal_id', '=', 'jurnal_umums.jurnal_id')
                                 ->where('jurnal_umums.status', 'disetujui')
                                 ->sum(DB::raw('debit - kredit'));

        $transaksiKasBanks = TransaksiKasBank::where('kas_bank_id', $kasBank->kas_bank_id)
                                             ->orderBy('tanggal_transaksi', 'desc')
                                             ->get();

        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun', 'asc')->get();

        return view('keuangan.kas_bank.show', compact('kasBank', 'transaksiKasBanks', 'akuns', 'saldoAwal'));
    }

    /**
     * Hapus akun Kas/Bank dari storage.
     */
    public function destroy(KasBank $kasBank)
    {
        $user = Auth::user();
        if (!$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk menghapus akun kas/bank.');
        }

        try {
            $kasBank->delete();
            return redirect()->route('kas-bank.index')->with('success', 'Akun Kas/Bank berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus akun: ' . $e->getMessage());
        }
    }
}
