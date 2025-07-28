<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitUsaha;
use App\Models\Bungdes;   // Import model Bungdes (untuk dropdown)
use App\Models\User;     // Import model User (untuk dropdown penanggung jawab)
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang sedang login

class UnitUsahaController extends Controller
{
    /**
     * Menampilkan daftar unit usaha.
     */
    public function index()
    {
        $unitUsahas = UnitUsaha::with(['bungdes', 'user'])->latest()->get(); // Ambil semua unit usaha dengan relasi
        return view('admin.manajemen_data.unit_usaha.index', compact('unitUsahas'));
    }

    /**
     * Menampilkan form untuk menambah unit usaha baru.
     */
    public function create()
    {
        $bungdeses = Bungdes::all();
        $users = User::where('role', 'manajer_unit_usaha')->get(); // Ambil user yang berhak jadi penanggung jawab
        return view('admin.manajemen_data.unit_usaha.create', compact('bungdeses', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan unit usaha baru ke database.
     */
     public function store(Request $request)
    {
        $user = Auth::user(); // Get the currently authenticated user

        $rules = [
            'nama_unit' => 'required|string|max:255',
            'jenis_usaha' => 'required|string|max:100',
            'bungdes_id' => 'required|exists:bungdeses,bungdes_id',
            'tanggal_mulai_operasi' => 'nullable|date',
            'status_operasi' => 'required|string|max:50',
        ];

        if ($user->role === 'admin') {
            $rules['user_id'] = 'nullable|exists:users,user_id';
        } else {
            $rules['user_id'] = 'nullable|exists:users,user_id'; // Can remain nullable
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();

        if ($user->role === 'admin') {
        } else if ($user->role === 'manajer_unit_usaha') {
            $data['user_id'] = $user->user_id;
        }

        UnitUsaha::create($data);

        return redirect()->route('admin.unit_usaha.index')->with('success', 'Unit usaha berhasil ditambahkan!');
    }

    public function show(UnitUsaha $unitUsaha)
    {
        $unitUsaha->load(['bungdes', 'user']);
        return view('admin.manajemen_data.unit_usaha.show', compact('unitUsaha'));
    }

    /**
     * Menampilkan form untuk mengedit unit usaha.
     */
    public function edit(UnitUsaha $unitUsaha)
    {
        $bungdeses = Bungdes::all();
        $users = User::where('role', 'manajer_unit_usaha')->get(); // Sesuaikan peran
        return view('admin.unit_usaha.edit', compact('unitUsaha', 'bungdeses', 'users'));
    }

    /**
     * Memperbarui unit usaha di database.
     */
    public function update(Request $request, UnitUsaha $unitUsaha)
    {
        $validator = Validator::make($request->all(), [
            'nama_unit' => 'required|string|max:255',
            'jenis_usaha' => 'required|string|max:100',
            'bungdes_id' => 'required|exists:bungdeses,bungdes_id',
            'tanggal_mulai_operasi' => 'nullable|date',
            'status_operasi' => 'required|string|max:50',
            'user_id' => 'nullable|exists:users,user_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $unitUsaha->update($request->all());

        return redirect()->route('admin.unit_usaha.index')->with('success', 'Unit usaha berhasil diperbarui!');
    }

    /**
     * Menghapus unit usaha dari database.
     */
    public function destroy(UnitUsaha $unitUsaha)
    {
        $unitUsaha->delete();
        return redirect()->route('admin.unit_usaha.index')->with('success', 'Unit usaha berhasil dihapus!');
    }
}
