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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule; // Tambahkan baris ini

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

        if ($user->hasRole(['bendahara_bumdes', 'admin_bumdes'])) {
            $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        } elseif ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
            $unitUsahas = $user->unitUsahas()
                               ->where('status_operasi', 'Aktif')
                               ->select('unit_usahas.unit_usaha_id', 'unit_usahas.nama_unit')
                               ->orderBy('nama_unit')
                               ->get();
        }

        return view('keuangan.jurnal_manual.create', compact('akuns', 'unitUsahas'));
    }

    /**
     * Simpan jurnal
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $managedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usahas.unit_usaha_id');

        $unitUsahaValidation = [
            'nullable',
            Rule::in($managedUnitUsahaIds)
        ];

        if (!$user->hasRole(['admin_bumdes', 'bendahara_bumdes'])) {
            $unitUsahaValidation[] = 'required';
        }

        $request->validate([
            'tanggal_transaksi' => 'required|date',
            'deskripsi' => 'required|string|max:500',
            'details' => 'required|array|min:2',
            'details.*.akun_id' => 'required|exists:akuns,akun_id',
            'details.*.debit' => 'required|numeric|min:0',
            'details.*.kredit' => 'required|numeric|min:0',
            'details.*.keterangan' => 'nullable|string|max:255',
            'unit_usaha_id' => $unitUsahaValidation
        ]);

        try {
            DB::beginTransaction();

            $totalDebit = collect($request->details)->sum('debit');
            $totalKredit = collect($request->details)->sum('kredit');

            if (round($totalDebit, 2) !== round($totalKredit, 2)) {
                throw new \Exception('Total Debit dan Kredit tidak seimbang.');
            }

            $unitUsahaIdToSave = $request->unit_usaha_id;

            if ($user->hasRole(['admin_unit_usaha', 'manajer_unit_usaha'])) {
                if (!$managedUnitUsahaIds->contains($unitUsahaIdToSave)) {
                    throw new AuthorizationException('Anda tidak memiliki izin untuk membuat jurnal di unit usaha ini.');
                }
            } else {
                if (empty($unitUsahaIdToSave)) {
                     throw new \Exception('Unit usaha harus dipilih.');
                }
            }

            $jurnal = JurnalUmum::create([
                'user_id' => $user->user_id,
                'unit_usaha_id' => $unitUsahaIdToSave,
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
