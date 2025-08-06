<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akun; // Make sure Akun model is imported

use Illuminate\Support\Facades\Validator;

class AkunController extends Controller
{
    /**
     * Menampilkan daftar akun keuangan.
     */
    public function index()
    {
        $akuns = Akun::orderBy('kode_akun')->get();
        $topLevelAkuns = Akun::whereNull('parent_id')->orderBy('kode_akun')->get();

        // Add this line to get tipeAkunOptions from your Akun model
        $tipeAkunOptions = Akun::getTipeAkunOptions(); // Assuming this static method exists in your Akun model

        return view('keuangan.akun.index', compact('akuns', 'topLevelAkuns', 'tipeAkunOptions'));
    }

    /**
     * Menampilkan form untuk menambah akun baru.
     */
    public function create()
    {
        $tipeAkunOptions = Akun::getTipeAkunOptions();
        // Ambil semua akun yang bisa jadi parent (biasanya akun header)
        $parentAkuns = Akun::where('is_header', true)->orderBy('kode_akun')->get();

        return view('keuangan.akun.create', compact('tipeAkunOptions', 'parentAkuns'));
    }

    /**
     * Menyimpan akun baru ke database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_akun' => 'required|string|max:50|unique:akuns,kode_akun',
            'nama_akun' => 'required|string|max:255',
            'tipe_akun' => 'required|string|max:50',
            'is_header' => 'boolean', // Akan otomatis false jika tidak ada di request (checkbox tidak dicentang)
            'parent_id' => 'nullable|exists:akuns,akun_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Akun::create($request->all());

        return redirect()->route('keuangan.akun.index')->with('success', 'Akun keuangan berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail akun (opsional).
     */
    public function show(Akun $akun)
    {
        // Load parent dan children jika ingin menampilkan detail hierarki
        $akun->load(['parent', 'children']);
        return view('keuangan.akun.show', compact('akun'));
    }

    /**
     * Menampilkan form untuk mengedit akun.
     */
    public function edit(Akun $akun)
    {
        $tipeAkunOptions = Akun::getTipeAkunOptions();
        // Ambil semua akun yang bisa jadi parent, KECUALI akun itu sendiri dan children-nya
        $parentAkuns = Akun::where('is_header', true)
                            ->where('akun_id', '!=', $akun->akun_id)
                            ->whereDoesntHave('children', function ($query) use ($akun) {
                                $query->where('akun_id', $akun->akun_id);
                            }) // Hindari menjadi parent dari dirinya sendiri atau child-nya
                            ->orderBy('kode_akun')
                            ->get();

        return view('keuangan.akun.edit', compact('akun', 'tipeAkunOptions', 'parentAkuns'));
    }

    /**
     * Memperbarui akun di database.
     */
    public function update(Request $request, Akun $akun)
    {
        $validator = Validator::make($request->all(), [
            'nama_akun' => 'required|string|max:255',
            'tipe_akun' => 'required|string|max:50',
            'is_header' => 'boolean', // Will be 0 if checkbox is unchecked
            'parent_id' => [
                'nullable',
                'exists:akuns,akun_id',
                function ($attribute, $value, $fail) use ($akun) {
                    if ($value !== null && $value == $akun->akun_id) { // Use == for comparison, not ===
                        $fail('Akun induk tidak bisa akun itu sendiri.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // 422 Unprocessable Entity
        }

        $akun->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Akun keuangan berhasil diperbarui!',
            'data' => $akun->load('parent')
        ]);
    }


    /**
     * Menghapus akun dari database.
     */
    public function destroy(Akun $akun)
    {
        if ($akun->children()->count() > 0) {
            return redirect()->back()->with('error', 'Akun ini tidak bisa dihapus karena memiliki sub-akun.');
        }
        // Tambahkan cek transaksi jika sudah ada modul transaksi

        $akun->delete();
        return redirect()->route('keuangan.akun.index')->with('success', 'Akun keuangan berhasil dihapus!');
    }
}
