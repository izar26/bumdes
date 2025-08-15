<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmum;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ApprovalJurnalController extends Controller
{
    /**
     * Middleware untuk memastikan hanya peran yang benar yang bisa mengakses controller ini.
     */
    public function __construct()
    {
        $this->middleware('role:manajer_unit_usaha|direktur_bumdes');
    }

    /**
     * Menampilkan daftar jurnal yang menunggu persetujuan.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = JurnalUmum::with('detailJurnals.akun', 'unitUsaha', 'user')
            ->where('status', 'menunggu')
            ->latest('tanggal_transaksi');

        // Jika manajer unit: hanya jurnal dari admin_unit_usaha di unit yg dikelolanya
        if ($user->hasRole('manajer_unit_usaha')) {
            $unitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id')->toArray();
            $query->whereIn('unit_usaha_id', $unitIds);
            $query->whereHas('user', function ($q) {
                $q->whereHas('roles', function ($q2) {
                    $q2->where('name', 'admin_unit_usaha');
                });
            });
        }
        // Jika direktur BUMDes: hanya jurnal yang dibuat oleh bendahara_bumdes
        elseif ($user->hasRole('direktur_bumdes')) {
            $query->whereHas('user', function ($q) {
                $q->whereHas('roles', function ($q2) {
                    $q2->where('name', 'bendahara_bumdes');
                });
            });
        }

        // Filter opsional
        if ($request->filled('unit_usaha_id')) {
            $query->where('unit_usaha_id', $request->unit_usaha_id);
        }
        // ... filter lainnya bisa ditambahkan di sini

        $jurnals = $query->paginate(15);
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();

        return view('keuangan.approval.index', compact('jurnals', 'unitUsahas'));
    }

    /**
     * Helper untuk otorisasi aksi.
     */
    protected function authorizeAction(JurnalUmum $jurnal)
    {
        $user = Auth::user();

        if ($jurnal->status !== 'menunggu') {
            abort(403, 'Jurnal bukan dalam status menunggu.');
        }

        if ($user->hasRole('manajer_unit_usaha')) {
            $unitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id')->toArray();
            if (!in_array($jurnal->unit_usaha_id, $unitIds) || !$jurnal->user->hasRole('admin_unit_usaha')) {
                abort(403, 'Anda tidak memiliki izin untuk memproses jurnal ini.');
            }
            return true;
        }

        if ($user->hasRole('direktur_bumdes')) {
            if (!$jurnal->user->hasRole('bendahara_bumdes')) {
                abort(403, 'Anda tidak memiliki izin untuk memproses jurnal ini.');
            }
            return true;
        }

        abort(403);
    }

    /**
     * Menyetujui satu jurnal.
     */
    public function approve(JurnalUmum $jurnal)
    {
        $this->authorizeAction($jurnal);

        $jurnal->update([
            'status' => 'disetujui',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejected_reason' => null,
        ]);

        return back()->with('success', 'Jurnal berhasil disetujui.');
    }

    /**
     * Menolak satu jurnal.
     */
    public function reject(Request $request, JurnalUmum $jurnal)
    {
        $request->validate(['rejected_reason' => 'required|string|max:500']);
        $this->authorizeAction($jurnal);

        $jurnal->update([
            'status' => 'ditolak',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejected_reason' => $request->rejected_reason,
        ]);

        return back()->with('success', 'Jurnal ditolak dan alasan disimpan.');
    }

    /**
     * Menyetujui beberapa jurnal yang dipilih.
     */
    public function approveSelected(Request $request)
    {
        $request->validate(['jurnal_ids' => 'required|array|min:1']);

        $jurnalsToApprove = JurnalUmum::whereIn('jurnal_id', $request->jurnal_ids)->get();

        DB::transaction(function () use ($jurnalsToApprove) {
            foreach ($jurnalsToApprove as $jurnal) {
                // Jalankan otorisasi untuk setiap jurnal yang akan diapprove
                $this->authorizeAction($jurnal);

                $jurnal->update([
                    'status' => 'disetujui',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'rejected_reason' => null,
                ]);
            }
        });

        return back()->with('success', 'Jurnal yang dipilih berhasil disetujui.');
    }
}
