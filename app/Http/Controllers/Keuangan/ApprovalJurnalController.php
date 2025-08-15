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
        // Tambahkan peran  ke middleware
        $this->middleware('role:manajer_unit_usaha|direktur_bumdes|');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = JurnalUmum::with('detailJurnals.akun', 'unitUsaha', 'user')
            ->where('status', 'menunggu')
            ->latest('tanggal_transaksi');

        $unitUsahasUntukFilter = collect();
        $isManajerOrWisata = $user->hasRole(['manajer_unit_usaha']);
        $isDirektur = $user->hasRole('direktur_bumdes');

        if ($isManajerOrWisata) {
            $unitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id')->toArray();
            $query->whereIn('unit_usaha_id', $unitIds);

            // Filter jurnal yang dibuat hanya oleh admin_unit_usaha
            $query->whereHas('user', function ($q) {
                $q->whereHas('roles', fn ($q2) => $q2->where('name', 'admin_unit_usaha'));
            });

            $unitUsahasUntukFilter = $user->unitUsahas;

        } elseif ($isDirektur) {
            // Filter jurnal yang dibuat hanya oleh bendahara_bumdes
            $query->whereHas('user', function ($q) {
                $q->whereHas('roles', fn ($q2) => $q2->where('name', 'bendahara_bumdes'));
            });

            // Direktur bisa melihat semua unit usaha
            $unitUsahasUntukFilter = UnitUsaha::all();
        } else {
            abort(403);
        }

        // Terapkan filter opsional (unit usaha, tanggal, tahun)
        if ($request->filled('unit_usaha_id')) {
            $query->where('unit_usaha_id', $request->unit_usaha_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->end_date);
        }
        if ($request->filled('year')) {
            $query->whereYear('tanggal_transaksi', $request->year);
        }

        $jurnals = $query->paginate(15);
        $jurnals->appends(request()->query()); // Tambahkan ini agar paginasi tidak merusak filter

        return view('keuangan.approval.index', compact('jurnals', 'unitUsahasUntukFilter'));
    }

    protected function authorizeAction(JurnalUmum $jurnal)
    {
        $user = Auth::user();

        if ($jurnal->status !== 'menunggu') {
            abort(403, 'Jurnal bukan dalam status menunggu.');
        }

        if ($user->hasRole(['manajer_unit_usaha'])) {
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
