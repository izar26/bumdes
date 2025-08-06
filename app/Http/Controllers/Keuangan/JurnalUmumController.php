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

    $jurnalQuery = JurnalUmum::with('detailJurnals.akun')->latest('tanggal_transaksi');

    // Filter role
    if ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
        $unitUsahaIds = $user->unitUsahas()->pluck('unit_usaha_id');
        $jurnalQuery->whereIn('unit_usaha_id', $unitUsahaIds);
    }

    // Filter tambahan
    if ($request->filled('start_date')) {
        $jurnalQuery->whereDate('tanggal_transaksi', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $jurnalQuery->whereDate('tanggal_transaksi', '<=', $request->end_date);
    }
    if ($request->filled('status')) {
        $jurnalQuery->whereRaw('ROUND(total_debit,2) ' . 
            ($request->status === 'seimbang' ? '=' : '!=') . 
            ' ROUND(total_kredit,2)');
    }
    if ($request->filled('unit_usaha_id')) {
        $jurnalQuery->where('unit_usaha_id', $request->unit_usaha_id);
    }
    if ($request->filled('year')) {
        $jurnalQuery->whereYear('tanggal_transaksi', $request->year);
    }

    $jurnals = $jurnalQuery->paginate(10);

    // Data tambahan
    $unitUsahas = $user->hasRole(['admin_bumdes', 'bendahara_bumdes']) 
        ? \App\Models\UnitUsaha::orderBy('nama_unit')->get() 
        : collect();

    $years = JurnalUmum::selectRaw('YEAR(tanggal_transaksi) as year')
        ->distinct()
        ->orderBy('year', 'desc')
        ->pluck('year');

    return view('keuangan.jurnal.index', compact('jurnals', 'unitUsahas', 'years'));
}



    public function edit(JurnalUmum $jurnalUmum)
    {
        $jurnal = $jurnalUmum->load('detailJurnals');

        // Ambil akun
        $akuns = Akun::where('is_header', 0)->orderBy('kode_akun')->get();

        // Ambil unit usaha untuk dropdown jika role Bendahara/Admin BUMDes
        $unitUsahas = collect();
        $user = Auth::user();
        if ($user->hasRole(['bendahara_bumdes', 'admin_bumdes'])) {
            $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        }

        return view('keuangan.jurnal.edit', compact('jurnal', 'akuns', 'unitUsahas'));
    }

    public function update(Request $request, JurnalUmum $jurnalUmum)
    {
        $user = Auth::user();

        // Validasi umum
        $rules = [
            'tanggal_transaksi' => 'required|date',
            'deskripsi' => 'required|string|max:500',
            'details' => 'required|array|min:2',
            'details.*.akun_id' => 'required|exists:akuns,akun_id',
            'details.*.debit' => 'required|numeric|min:0',
            'details.*.kredit' => 'required|numeric|min:0',
            'details.*.keterangan' => 'nullable|string|max:255',
        ];

        // Validasi unit usaha jika role bendahara/admin
        if ($user->hasRole(['bendahara_bumdes', 'admin_bumdes'])) {
            $rules['unit_usaha_id'] = 'nullable|exists:unit_usahas,unit_usaha_id';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Hitung total debit & kredit
            $totalDebit = 0;
            $totalKredit = 0;
            foreach ($request->details as $detail) {
                if ($detail['debit'] > 0 && $detail['kredit'] > 0) {
                    throw new \Exception('Satu baris tidak boleh memiliki Debit dan Kredit sekaligus.');
                }
                if ($detail['debit'] == 0 && $detail['kredit'] == 0) {
                    throw new \Exception('Setiap baris harus memiliki nilai Debit atau Kredit.');
                }
                $totalDebit += $detail['debit'];
                $totalKredit += $detail['kredit'];
            }
            if (round($totalDebit, 2) !== round($totalKredit, 2)) {
                throw new \Exception('Total Debit dan Kredit tidak seimbang.');
            }

            // Tentukan unit usaha ID
            $unitUsahaId = $jurnalUmum->unit_usaha_id; // default tidak berubah
            if ($user->hasRole(['bendahara_bumdes', 'admin_bumdes'])) {
                $unitUsahaId = $request->unit_usaha_id ?: null;
            } elseif ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
                $unitUsaha = UnitUsaha::where('user_id', $user->user_id)->first();
                if ($unitUsaha) {
                    $unitUsahaId = $unitUsaha->unit_usaha_id;
                }
            }

            // Update jurnal
            $jurnalUmum->update([
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'deskripsi' => $request->deskripsi,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'unit_usaha_id' => $unitUsahaId,
            ]);

            // Hapus detail lama dan buat ulang
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

    public function destroy(JurnalUmum $jurnalUmum)
    {
        $jurnalUmum->delete();
        return redirect()->route('jurnal-umum.index')->with('success', 'Jurnal berhasil dihapus.');
    }
}
