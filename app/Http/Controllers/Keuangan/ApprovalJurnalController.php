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
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class ApprovalJurnalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = JurnalUmum::with('detailJurnals.akun', 'unitUsaha', 'user')
            ->where('status', 'menunggu')
            ->latest('tanggal_transaksi');

        // Jika manajer unit: hanya jurnal dari admin_unit_usaha di unit yg dikelolanya
        if ($user->hasRole('manajer_unit_usaha')) {
            // FIX: Menambahkan nama tabel 'unit_usahas' untuk menghilangkan ambiguitas
            $unitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id')->toArray();
            $query->whereIn('unit_usaha_id', $unitIds);
            $query->whereHas('user', function ($q) {
                // FIX: Menggunakan whereHas untuk role
                $q->whereHas('roles', function ($q2) {
                    $q2->where('name', 'admin_unit_usaha');
                });
            });
        }
        // Jika admin BUMDes: hanya jurnal yang dibuat oleh bendahara_bumdes
        elseif ($user->hasRole('admin_bumdes')) {
            $query->whereHas('user', function ($q) {
                // FIX: Menggunakan whereHas untuk role
                $q->whereHas('roles', function ($q2) {
                    $q2->where('name', 'bendahara_bumdes');
                });
            });
        } else {
            abort(403);
        }

        // optional filters (tahun / tanggal / unit)
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

        // FIX: Ambil daftar unit usaha dengan benar untuk dropdown filter
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();

        return view('keuangan.approval.index', compact('jurnals', 'unitUsahas'));
    }

    /**
     * Pastikan user punya hak melakukan aksi pada jurnal ini.
     */
    protected function authorizeAction(JurnalUmum $jurnal)
    {
        $user = Auth::user();

        if ($jurnal->status !== 'menunggu') {
            abort(403, 'Jurnal bukan dalam status menunggu.');
        }

        if ($user->hasRole('manajer_unit_usaha')) {
            $unitIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id')->toArray();
            if (!in_array($jurnal->unit_usaha_id, $unitIds)) abort(403, 'Anda bukan bagian unit ini.');

            // Cek role user pembuat jurnal
            if (!$jurnal->user->hasRole('admin_unit_usaha')) abort(403, 'Hanya jurnal yang dibuat oleh admin unit yang bisa diapprove di sini.');
            return true;
        }

        if ($user->hasRole('admin_bumdes')) {
            // Cek role user pembuat jurnal
            if (!$jurnal->user->hasRole('bendahara_bumdes')) abort(403, 'Hanya jurnal yang dibuat oleh bendahara yang bisa diapprove di sini.');
            return true;
        }

        abort(403);
    }

    public function approve(JurnalUmum $jurnal)
    {
        $this->authorizeAction($jurnal);

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

    public function reject(Request $request, JurnalUmum $jurnal)
    {
        $request->validate([
            'rejected_reason' => 'required|string|max:500',
        ]);

        $this->authorizeAction($jurnal);

        DB::transaction(function () use ($jurnal, $request) {
            $jurnal->update([
                'status' => 'ditolak',
                'approved_by' => Auth::id(), // catat siapa yg menolak
                'approved_at' => now(),
                'rejected_reason' => $request->rejected_reason,
            ]);
        });

        return back()->with('success', 'Jurnal ditolak dan alasan disimpan.');
    }
}
