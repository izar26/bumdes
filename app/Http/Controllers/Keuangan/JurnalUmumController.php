<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmum;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use App\Models\Bungdes; // Menambahkan model Bumdes
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class JurnalUmumController extends Controller
{
    /**
     * Menampilkan daftar semua jurnal umum dengan filter.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Mengambil tahun dan status dari request, atau menggunakan nilai default
        $tahun = $request->year ?? date('Y');
        $statusJurnal = $request->approval_status ?? 'disetujui'; // Nilai default: 'disetujui'

        // Query dasar untuk mengambil jurnal dengan relasi yang dibutuhkan
        $jurnalQuery = JurnalUmum::with('detailJurnals.akun', 'unitUsaha')
            ->latest('tanggal_transaksi');

        // Filter berdasarkan peran pengguna
        if ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
            $unitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            $jurnalQuery->whereIn('unit_usaha_id', $unitUsahaIds);
        }

        // Filter berdasarkan tahun
        $jurnalQuery->whereYear('tanggal_transaksi', $tahun);

        // Filter berdasarkan status
        if ($statusJurnal !== 'semua') {
            $jurnalQuery->where('status', $statusJurnal);
        }

        // Filter berdasarkan rentang tanggal
        if ($request->filled('start_date')) {
            $jurnalQuery->whereDate('tanggal_transaksi', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $jurnalQuery->whereDate('tanggal_transaksi', '<=', $request->end_date);
        }

        // Filter berdasarkan unit usaha (hanya untuk Admin BUMDes)
        if ($user->hasRole(['admin_bumdes', 'bendahara_bumdes']) && $request->filled('unit_usaha_id')) {
            $jurnalQuery->where('unit_usaha_id', $request->unit_usaha_id);
        }

        // Clone query untuk menghitung total tanpa pagination
        $totalQuery = clone $jurnalQuery;
        $totals = $totalQuery->select(
            DB::raw('SUM(total_debit) as total_debit_all'),
            DB::raw('SUM(total_kredit) as total_kredit_all')
        )->first();

        // Mengambil data jurnal dengan pagination
        $jurnals = $jurnalQuery->paginate(10);
        
        $totalDebitAll = $totals->total_debit_all ?? 0;
        $totalKreditAll = $totals->total_kredit_all ?? 0;

        // Mendapatkan semua tahun yang tersedia untuk filter
        $years = JurnalUmum::selectRaw('YEAR(tanggal_transaksi) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Mendapatkan unit usaha yang sesuai dengan peran
        $unitUsahas = $user->hasRole(['admin_bumdes', 'bendahara_bumdes'])
            ? UnitUsaha::orderBy('nama_unit')->get()
            : $user->unitUsahas()->orderBy('nama_unit')->get();

        return view('keuangan.jurnal.index', compact(
            'jurnals', 
            'unitUsahas', 
            'years', 
            'tahun', 
            'statusJurnal', 
            'totalDebitAll', 
            'totalKreditAll'
        ));
    }

    /**
     * Menampilkan form untuk mengedit jurnal.
     */
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

    /**
     * Memperbarui jurnal dan detailnya.
     */
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
                $unitUsaha = $user->unitUsahas->first();
                if ($unitUsaha) {
                    $unitUsahaId = $unitUsaha->unit_usaha_id;
                }
            }

            // Reset status jika ditolak dan ada perubahan
            $status = $jurnalUmum->status === 'ditolak' ? 'menunggu' : $jurnalUmum->status;
            
            $jurnalUmum->update([
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'deskripsi' => $request->deskripsi,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'unit_usaha_id' => $unitUsahaId,
                'status' => $status,
                'rejected_reason' => null, // Hapus alasan penolakan jika diperbarui
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

    /**
     * Menghapus jurnal dan semua detailnya.
     */
    public function destroy(JurnalUmum $jurnal)
    {
        try {
            DB::beginTransaction();

            // Hapus detail jurnal terlebih dahulu
            $jurnal->detailJurnals()->delete();
            
            // Hapus jurnal induk
            $jurnal->delete();

            DB::commit();
            return redirect()->route('jurnal-umum.index')->with('success', 'Jurnal berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus jurnal: ' . $e->getMessage());
        }
    }

    public function show($id, Request $request)
{
    if ($id === 'print') {
        $bumdes = \App\Models\Bungdes::first();
        $tahun = $request->get('year', date('Y'));
        $statusJurnal = $request->get('approval_status', 'semua');

        // Ambil query sama seperti index
        $query = JurnalUmum::with('detailJurnals.akun')
            ->whereYear('tanggal_transaksi', $tahun);

        if ($statusJurnal !== 'semua') {
            $query->where('status', $statusJurnal);
        }

        if ($request->start_date) {
            $query->whereDate('tanggal_transaksi', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('tanggal_transaksi', '<=', $request->end_date);
        }

        if ($request->unit_usaha_id) {
            $query->where('unit_usaha_id', $request->unit_usaha_id);
        }

        $jurnals = $query->orderBy('tanggal_transaksi')->get();

        return view('keuangan.jurnal.print', compact('jurnals', 'tahun', 'statusJurnal', 'bumdes'));
    }

    // Kalau bukan print, berarti detail jurnal biasa
    $jurnal = JurnalUmum::with('detailJurnals.akun')->findOrFail($id);
    return view('keuangan.jurnal.index', compact('jurnal'));
}

 
}
