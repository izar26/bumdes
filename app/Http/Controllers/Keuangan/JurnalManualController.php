<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Akun;
use App\Models\JurnalUmum;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;

class JurnalManualController extends Controller
{
    /**
     * Form create jurnal
     */
    public function create()
    {
        $user = Auth::user();
        $akuns = Akun::orderBy('kode_akun')->get();
        $unitUsahas = collect();

        // Tentukan unit usaha yang ditampilkan di view
        if ($user->hasRole('bendahara_bumdes') || $user->hasRole('admin_bumdes')) {
            // Bendahara & Admin BUMDes bisa akses semua unit usaha
            $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        } elseif ($user->hasRole('admin_unit_usaha') || $user->hasRole('manajer_unit_usaha')) {
            // Admin & Manajer Unit Usaha hanya punya unit usahanya sendiri
            $unitUsahas = UnitUsaha::where('user_id', $user->user_id)->get();
        }

        return view('keuangan.jurnal_manual.create', compact('akuns', 'unitUsahas'));
    }

    /**
     * Simpan jurnal
     */
    public function store(Request $request)
{
    
    $request->validate([
        'tanggal_transaksi' => 'required|date',
        'deskripsi' => 'required|string|max:500',
        'details' => 'required|array|min:2',
        'details.*.akun_id' => 'required|exists:akuns,akun_id',
        'details.*.debit' => 'required|numeric|min:0',
        'details.*.kredit' => 'required|numeric|min:0',
        'details.*.keterangan' => 'nullable|string|max:255',
    ]);

    try {
        DB::beginTransaction();

        $totalDebit = collect($request->details)->sum('debit');
        $totalKredit = collect($request->details)->sum('kredit');

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            throw new \Exception('Total Debit dan Kredit tidak seimbang.');
        }

        $user = Auth::user();

        // PATCH: Ambil unit usaha langsung dari DB tanpa cek role
        $unitUsaha = UnitUsaha::where('user_id', $user->user_id)->first();
        $unitUsahaId = $unitUsaha ? $unitUsaha->unit_usaha_id : $request->unit_usaha_id;

        // Simpan Jurnal
        $jurnal = JurnalUmum::create([
            'user_id' => $user->user_id,
            'unit_usaha_id' => $unitUsahaId,
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'deskripsi' => $request->deskripsi,
            'total_debit' => $totalDebit,
            'total_kredit' => $totalKredit,
        ]);

        foreach ($request->details as $detail) {
            DetailJurnal::create([
                'jurnal_id' => $jurnal->jurnal_id,
                'akun_id' => $detail['akun_id'],
                'debit' => $detail['debit'],
                'kredit' => $detail['kredit'],
                'keterangan' => $detail['keterangan'] ?? null,
            ]);
        }

        DB::commit();
        return redirect()->route('jurnal-umum.index')->with('success', 'Jurnal berhasil disimpan.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal menyimpan jurnal: ' . $e->getMessage())->withInput();
    }
}

}
