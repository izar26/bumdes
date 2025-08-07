<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalJurnalController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = JurnalUmum::with('detailJurnals.akun', 'unitUsaha', 'user')
            ->where('status', 'menunggu')
            ->latest('tanggal_transaksi');

        if ($user->hasRole('manajer_unit_usaha')) {
            $unitIds = $user->unitUsahas()->pluck('unit_usaha_id');
            $query->whereIn('unit_usaha_id', $unitIds);
        }

        $jurnals = $query->get();
        return view('keuangan.approval.index', compact('jurnals'));
    }

    public function approve($id)
    {
        $jurnal = JurnalUmum::findOrFail($id);

        DB::transaction(function () use ($jurnal) {
            $jurnal->update([
                'status' => 'disetujui',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejected_reason' => null,
            ]);
        });

        return back()->with('success', 'Jurnal berhasil disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $jurnal = JurnalUmum::findOrFail($id);

        DB::transaction(function () use ($jurnal, $request) {
            $jurnal->update([
                'status' => 'ditolak',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejected_reason' => $request->reason,
            ]);
        });

        return back()->with('error', 'Jurnal ditolak dengan alasan: ' . $request->reason);
    }
}
