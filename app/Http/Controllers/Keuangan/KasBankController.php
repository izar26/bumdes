<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\KasBank;
use App\Models\Akun;
use Illuminate\Http\Request;

class KasBankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kasBanks = KasBank::all();
        return view('keuangan.kas_bank.index', compact('kasBanks'));
    }

    /**
     * Show the form for creating a new resource.
     * FUNGSI INI YANG KEMUNGKINAN BESAR HILANG DARI FILE ANDA
     */
    public function create()
    {
        // Ambil hanya akun dengan tipe 'Kas & Bank' dari Chart of Accounts
        $akun_list = Akun::where('tipe_akun', 'Kas & Bank')
                            ->where('is_header', 0)
                            ->get();
        return view('keuangan.kas_bank.create', compact('akun_list'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_akun_kas_bank' => 'required|string|max:255',
            'akun_id' => 'required|exists:akuns,akun_id|unique:kas_banks,akun_id',
            'nomor_rekening' => 'nullable|string|max:100',
            'saldo_saat_ini' => 'required|numeric|min:0',
        ]);

        KasBank::create([
            'nama_akun_kas_bank' => $request->nama_akun_kas_bank,
            'akun_id' => $request->akun_id,
            'nomor_rekening' => $request->nomor_rekening,
            'saldo_saat_ini' => $request->saldo_saat_ini,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('kas-bank.index')
                         ->with('success', 'Akun Kas/Bank baru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(KasBank $kasBank)
    {
        $kasBank->load(['transaksiKasBanks' => function ($query) {
            $query->orderBy('tanggal_transaksi', 'desc');
        }]);
        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun', 'asc')->get();
        return view('keuangan.kas_bank.show', compact('kasBank', 'akuns'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KasBank $kasBank)
    {
        // Untuk nanti jika perlu fitur edit
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KasBank $kasBank)
    {
        // Untuk nanti jika perlu fitur edit
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KasBank $kasBank)
    {
        // Untuk nanti jika perlu fitur hapus
    }
}
