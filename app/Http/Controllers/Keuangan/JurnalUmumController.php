<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmum;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JurnalUmumController extends Controller
{
public function index(Request $request)
{
    $user = Auth::user();

    $jurnalQuery = JurnalUmum::with('detailJurnals.akun', 'unitUsaha')
        ->latest('tanggal_transaksi');

    // Filter role: hanya jurnal dari unit usaha yang di-manage user
    if ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
        $unitUsahaIds = $user->unitUsahas()->pluck('unit_usaha_id');
        $jurnalQuery->whereIn('unit_usaha_id', $unitUsahaIds);
    }

    // Default tahun sekarang jika tidak diisi
    $tahun = $request->year ?? date('Y');
    $jurnalQuery->whereYear('tanggal_transaksi', $tahun);

    // --- PERBAIKAN LOGIKA FILTER STATUS ---
    $statusJurnal = $request->approval_status;

    // Terapkan filter HANYA JIKA status yang dipilih bukan "semua" dan tidak kosong.
    if ($request->filled('approval_status') && $request->approval_status != 'semua') {
        $jurnalQuery->where('status', $statusJurnal);
    }
    // Jika user memilih "semua", tidak ada filter status yang ditambahkan.

    // Filter tanggal (opsional)
    if ($request->filled('start_date')) {
        $jurnalQuery->whereDate('tanggal_transaksi', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $jurnalQuery->whereDate('tanggal_transaksi', '<=', $request->end_date);
    }

    // Filter unit usaha (opsional)
    if ($request->filled('unit_usaha_id')) {
        $jurnalQuery->where('unit_usaha_id', $request->unit_usaha_id);
    }

    $jurnals = $jurnalQuery->paginate(10);

    $unitUsahas = $user->hasRole(['admin_bumdes', 'bendahara_bumdes'])
        ? UnitUsaha::orderBy('nama_unit')->get()
        : collect();

    $years = JurnalUmum::selectRaw('YEAR(tanggal_transaksi) as year')
        ->distinct()
        ->orderBy('year', 'desc')
        ->pluck('year');

    return view('keuangan.jurnal.index', compact('jurnals', 'unitUsahas', 'years', 'tahun', 'statusJurnal'));
}


public function edit(JurnalUmum $jurnalUmum)
{
    $user = Auth::user();

    // Batasi edit jika status disetujui & user bukan admin BUMDes/bendahara
    if ($jurnalUmum->status === 'disetujui' && !$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
        return redirect()->route('jurnal-umum.index')->with('error', 'Jurnal sudah disetujui dan tidak dapat diedit.');
    }

    $jurnal = $jurnalUmum->load('detailJurnals');
    $akuns = Akun::where('is_header', 0)->orderBy('kode_akun')->get();

    $unitUsahas = collect();
    if ($user->hasRole(['bendahara_bumdes', 'admin_bumdes'])) {
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
    }

    return view('keuangan.jurnal.edit', compact('jurnal', 'akuns', 'unitUsahas'));
}

public function update(Request $request, JurnalUmum $jurnalUmum)
{
    $user = Auth::user();

    // Batasi update jika status disetujui
    if ($jurnalUmum->status === 'disetujui' && !$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
        return redirect()->route('jurnal-umum.index')->with('error', 'Jurnal sudah disetujui dan tidak dapat diperbarui.');
    }

    $rules = [
        'tanggal_transaksi' => 'required|date',
        'deskripsi' => 'required|string|max:500',
        'details' => 'required|array|min:2',
        'details.*.akun_id' => 'required|exists:akuns,akun_id',
        'details.*.debit' => 'required|numeric|min:0',
        'details.*.kredit' => 'required|numeric|min:0',
        'details.*.keterangan' => 'nullable|string|max:255',
    ];
    if ($user->hasRole(['bendahara_bumdes', 'admin_bumdes'])) {
        $rules['unit_usaha_id'] = 'nullable|exists:unit_usahas,unit_usaha_id';
    }
    $request->validate($rules);

    try {
        DB::beginTransaction();

        $totalDebit = collect($request->details)->sum('debit');
        $totalKredit = collect($request->details)->sum('kredit');

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            throw new \Exception('Total Debit dan Kredit tidak seimbang.');
        }

        $unitUsahaId = $jurnalUmum->unit_usaha_id;
        if ($user->hasRole(['bendahara_bumdes', 'admin_bumdes'])) {
            $unitUsahaId = $request->unit_usaha_id ?: null;
        } elseif ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
            $unitUsaha = UnitUsaha::where('user_id', $user->user_id)->first();
            if ($unitUsaha) {
                $unitUsahaId = $unitUsaha->unit_usaha_id;
            }
        }

        // Reset status jika ditolak
        $status = $jurnalUmum->status === 'ditolak' ? 'menunggu' : $jurnalUmum->status;

        $jurnalUmum->update([
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'deskripsi' => $request->deskripsi,
            'total_debit' => $totalDebit,
            'total_kredit' => $totalKredit,
            'unit_usaha_id' => $unitUsahaId,
            'status' => $status
        ]);

        $jurnalUmum->detailJurnals()->delete();
        foreach ($request->details as $detail) {
            DetailJurnal::create([
                'jurnal_id' => $jurnalUmum->jurnal_id,
                'akun_id' => $detail['akun_id'],
                'debit' => $detail['debit'],
                'kredit' => $detail['kredit'],
                'keterangan' => $detail['keterangan'],
            ]);
        }

        DB::commit();
        return redirect()->route('jurnal-umum.index')->with('success', 'Jurnal berhasil diperbarui.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal memperbarui jurnal: ' . $e->getMessage())->withInput();
    }
}

}
