<?php

namespace App\Http\Controllers\Api;

use App\Models\Anggota;
use App\Models\RekeningSimpanan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SimplifyingSearchRekeningController extends Controller
{
    /**
     * Search for anggota by NIK or nama, and return their rekening simpanan.
     *
     * Query params:
     *   - q: search query (NIK or nama)
     *
     * Response:
     *   {
     *     "success": true,
     *     "data": [
     *       {
     *         "rekening_id": 1,
     *         "anggota_id": 10,
     *         "anggota_nama": "John Doe",
     *         "no_rekening": "REC-001",
     *         "jenis_simpanan": "Wajib",
     *         "saldo": 150000
     *       },
     *       ...
     *     ]
     *   }
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen(trim($query)) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal 2 karakter untuk pencarian',
                'data' => [],
            ]);
        }

        // Search anggota by NIK or nama
        $anggotas = Anggota::where('nik', 'like', "%{$query}%")
            ->orWhere('nama_lengkap', 'like', "%{$query}%")
            ->limit(20)
            ->get();

        if ($anggotas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota tidak ditemukan',
                'data' => [],
            ]);
        }

        // Get rekening for each anggota
        $result = [];
        foreach ($anggotas as $anggota) {
            $rekenings = RekeningSimpanan::where('anggota_id', $anggota->anggota_id)
                ->with('jenisSimpanan')
                ->get();

            foreach ($rekenings as $rekening) {
                $result[] = [
                    'rekening_id' => $rekening->rekening_id,
                    'anggota_id' => $anggota->anggota_id,
                    'anggota_nama' => $anggota->nama_lengkap,
                    'anggota_nik' => $anggota->nik,
                    'no_rekening' => $rekening->no_rekening,
                    'jenis_simpanan' => $rekening->jenisSimpanan->nama_jenis ?? 'N/A',
                    'saldo' => (int) $rekening->saldo,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
