<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmum;
use App\Models\user;
use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\UnitUsaha;
use App\Models\Bungdes; // Menambahkan model Bumdes
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
class JurnalUmumController extends Controller
{
    /**
     * Menampilkan daftar semua jurnal umum dengan filter.
     */
     public function index(Request $request)
    {
        $user = Auth::user();

        $tahun = $request->year ?? date('Y');
        $statusJurnal = $request->approval_status ?? 'disetujui';

        $jurnalQuery = JurnalUmum::with('detailJurnals.akun', 'unitUsaha')
            ->latest('tanggal_transaksi');

        $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');

        if ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
            $jurnalQuery->whereIn('unit_usaha_id', $managedUnitUsahaIds);
        }

        $jurnalQuery->whereYear('tanggal_transaksi', $tahun);

        if ($statusJurnal !== 'semua') {
            $jurnalQuery->where('status', $statusJurnal);
        }

        if ($request->filled('start_date')) {
            $jurnalQuery->whereDate('tanggal_transaksi', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $jurnalQuery->whereDate('tanggal_transaksi', '<=', $request->end_date);
        }

        if ($user->hasRole(['admin_bumdes', 'bendahara_bumdes']) && $request->filled('unit_usaha_id')) {
            $jurnalQuery->where('unit_usaha_id', $request->unit_usaha_id);
        }   

        $totalQuery = clone $jurnalQuery;
        $totals = $totalQuery->select(
            DB::raw('SUM(total_debit) as total_debit_all'),
            DB::raw('SUM(total_kredit) as total_kredit_all')
        )->first();

        $jurnals = $jurnalQuery->paginate(10);

        $totalDebitAll = $totals->total_debit_all ?? 0;
        $totalKreditAll = $totals->total_kredit_all ?? 0;

        $years = JurnalUmum::selectRaw('YEAR(tanggal_transaksi) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

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

        // Otorisasi: Cek apakah user punya hak akses ke jurnal ini
        if (!$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
            $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            if (!$managedUnitUsahaIds->contains($jurnalUmum->unit_usaha_id)) {
                throw new AuthorizationException('Anda tidak memiliki izin untuk mengedit jurnal ini.');
            }
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

        // Otorisasi: Cek apakah user punya hak akses ke jurnal ini sebelum update
        if (!$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
            $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
            if (!$managedUnitUsahaIds->contains($jurnalUmum->unit_usaha_id)) {
                throw new AuthorizationException('Anda tidak memiliki izin untuk memperbarui jurnal ini.');
            }
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
            if ($user->hasRole(['bendahara_bumdes', 'admin_bumdes']) && $request->filled('unit_usaha_id')) {
                $unitUsahaId = $request->unit_usaha_id;
            } elseif ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
                $unitUsahaId = $jurnalUmum->unit_usaha_id;
            }

            if ($jurnalUmum->status === 'disetujui' || $jurnalUmum->status === 'ditolak') {
                $status = 'menunggu';
            } else {
                $status = $jurnalUmum->status;
            }

            $jurnalUmum->update([
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'deskripsi' => $request->deskripsi,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'unit_usaha_id' => $unitUsahaId,
                'status' => $status,
                'rejected_reason' => null,
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
            return redirect()->route('jurnal-umum.index')->with('success', 'Jurnal berhasil diperbarui. Status disetujui direset untuk verifikasi ulang.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui jurnal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus jurnal dan semua detailnya.
     */
   public function destroy($id)
{
    $user = Auth::user();
    $jurnal = JurnalUmum::findOrFail($id);

    if (!$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
        $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
        if (!$managedUnitUsahaIds->contains($jurnal->unit_usaha_id)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk menghapus jurnal ini.');
        }
    }

    try {
        DB::beginTransaction();

        $jurnal->detailJurnals()->delete();
        $jurnal->delete();

        DB::commit();
        return redirect()->route('jurnal-umum.index')->with('success', 'Jurnal berhasil dihapus.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal menghapus jurnal: ' . $e->getMessage());
    }
}


    // Batasi hapus jika jurnal sudah disetujui & user bukan Admin BUMDes
    // if ($jurnal->status === 'disetujui' && !$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
    //     return redirect()->back()->with('error', 'Jurnal sudah disetujui dan tidak dapat dihapus.');
    // }

public function show($id, Request $request)
    {
        if ($id === 'print') {
            // --- PERBAIKAN DIMULAI DI SINI ---
            $request->validate(['tanggal_cetak' => 'nullable|date']);

            $bumdes = Bungdes::first();
            $user = Auth::user();
            $tanggalCetak = $request->tanggal_cetak ? Carbon::parse($request->tanggal_cetak) : now();
            $lokasi = optional($bumdes)->alamat ? explode(',', $bumdes->alamat)[0] : 'Lokasi BUMDes';

            $query = JurnalUmum::with('detailJurnals.akun', 'unitUsaha');

            if ($request->filled('year')) {
                $query->whereYear('tanggal_transaksi', $request->year);
            }
            if ($request->filled('approval_status') && $request->approval_status != 'semua') {
                $query->where('status', $request->approval_status);
            }
            if ($request->filled('start_date')) {
                $query->whereDate('tanggal_transaksi', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('tanggal_transaksi', '<=', $request->end_date);
            }
            if ($user->hasAnyRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
                $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');
                $query->whereIn('unit_usaha_id', $managedUnitUsahaIds);
            }
            if ($user->hasAnyRole(['admin_bumdes', 'bendahara_bumdes']) && $request->filled('unit_usaha_id')) {
                 $query->where('unit_usaha_id', $request->unit_usaha_id);
            }

            $jurnals = $query->orderBy('tanggal_transaksi')->get();

            $direktur = User::role('direktur_bumdes')->with('anggota')->first();
            $penandaTangan1 = ['jabatan' => 'Direktur', 'nama' => $direktur && $direktur->anggota ? $direktur->anggota->nama_lengkap : '....................'];
            $bendahara = User::role('bendahara_bumdes')->with('anggota')->first();
            $penandaTangan2 = ['jabatan' => 'Bendahara', 'nama' => $bendahara && $bendahara->anggota ? $bendahara->anggota->nama_lengkap : '....................'];

            // Variabel tahun dan statusJurnal untuk judul
            $tahun = $request->year ?? 'Semua';
            $statusJurnal = $request->approval_status ?? 'semua';

            return view('keuangan.jurnal.print', compact('jurnals', 'tahun', 'statusJurnal', 'bumdes', 'tanggalCetak', 'penandaTangan1', 'penandaTangan2', 'lokasi'));
            // --- AKHIR PERBAIKAN ---
        }

        // Logika untuk menampilkan detail satu jurnal (jika diperlukan)
        $jurnal = JurnalUmum::with('detailJurnals.akun', 'unitUsaha')->findOrFail($id);
        // return view('keuangan.jurnal.show', compact('jurnal'));
    }
}
