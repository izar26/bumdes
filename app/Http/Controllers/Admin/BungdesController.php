<?php

namespace App\Http\Controllers;

use App\Models\Bungdes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon; // Untuk menangani tanggal jika diperlukan

class BungdesController extends Controller
{
    /**
     * Display a listing of the Bungdes.
     */
    public function index()
    {
        $bungdeses = Bungdes::all(); // Tidak perlu eager load user lagi
        return response()->json([
            'message' => 'Daftar BUMDes berhasil diambil',
            'data' => $bungdeses
        ], 200);
    }

    /**
     * Store a newly created Bungdes in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_bumdes' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'tanggal_berdiri' => 'nullable|date',
            'deskripsi' => 'nullable|string',
            'telepon' => 'nullable|string|max:50',
            'struktur_organisasi' => 'nullable|string|max:500',
            'logo' => 'nullable|string|max:255', // Pertimbangkan penanganan upload file untuk produksi
            'aset_usaha' => 'nullable|string|max:500',
            'email' => 'nullable|string|email|max:255|unique:bungdeses,email',
            // 'user_id' dihapus dari validasi
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Kesalahan Validasi',
                'errors' => $validator->errors()
            ], 422);
        }

        $bungdes = Bungdes::create($request->all());

        return response()->json([
            'message' => 'BUMDes berhasil dibuat',
            'data' => $bungdes
        ], 201);
    }

    /**
     * Display the specified Bungdes.
     */
    public function show(string $id)
    {
        $bungdes = Bungdes::find($id); // Tidak perlu eager load user lagi

        if (!$bungdes) {
            return response()->json(['message' => 'BUMDes tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'BUMDes berhasil diambil',
            'data' => $bungdes
        ], 200);
    }

    /**
     * Update the specified Bungdes in storage.
     */
    public function update(Request $request, string $id)
    {
        $bungdes = Bungdes::find($id);

        if (!$bungdes) {
            return response()->json(['message' => 'BUMDes tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_bumdes' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'tanggal_berdiri' => 'nullable|date',
            'deskripsi' => 'nullable|string',
            'telepon' => 'nullable|string|max:50',
            'struktur_organisasi' => 'nullable|string|max:500',
            'logo' => 'nullable|string|max:255',
            'aset_usaha' => 'nullable|string|max:500',
            'email' => 'nullable|string|email|max:255|unique:bungdeses,email,' . $bungdes->bungdes_id . ',bungdes_id',
            // 'user_id' dihapus dari validasi
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Kesalahan Validasi',
                'errors' => $validator->errors()
            ], 422);
        }

        $bungdes->update($request->all());

        return response()->json([
            'message' => 'BUMDes berhasil diperbarui',
            'data' => $bungdes
        ], 200);
    }

    /**
     * Remove the specified Bungdes from storage.
     */
    public function destroy(string $id)
    {
        $bungdes = Bungdes::find($id);

        if (!$bungdes) {
            return response()->json(['message' => 'BUMDes tidak ditemukan'], 404);
        }

        $bungdes->delete();

        return response()->json([
            'message' => 'BUMDes berhasil dihapus'
        ], 200);
    }
}
