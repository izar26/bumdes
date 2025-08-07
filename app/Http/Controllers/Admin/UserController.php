<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UnitUsaha;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role; // Tambahkan ini jika menggunakan spatie
use App\Models\Anggota;

class UserController extends Controller
{
    public function index()
    {
        // Sesuaikan query untuk mengambil data anggota, bukan user biasa
        $users = User::with('roles')->get();
        $rolesOptions = Role::pluck('name'); // Ambil semua nama role dari spatie
        return view('admin.manajemen_data.user.index', compact('users', 'rolesOptions'));
    }

    public function create()
    {
        // Form create hanya untuk akun dasar, tanpa pemilihan role
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        $assignedUnitUsahaIds = [];
        return view('admin.manajemen_data.user.create', compact('unitUsahas', 'assignedUnitUsahaIds'));
    }


public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'username' => 'nullable|string|max:255|unique:users,username',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    DB::beginTransaction();

    try {
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => false, // User tidak aktif sampai profil diisi
        ]);

        // Tetapkan peran default 'anggota_baru'
        $defaultRole = Role::firstOrCreate(['name' => 'anggota_baru']);
        $user->assignRole($defaultRole);

        // Buat entri kosong di tabel 'anggotas' yang terhubung dengan user
        Anggota::create([
            'user_id' => $user->id,
            'nama_lengkap' => $request->name, // Ambil nama dari form awal
            'tanggal_daftar' => now(),
            'status_anggota' => 'aktif',
            // Kolom lain akan diisi oleh user itu sendiri
        ]);

        DB::commit();

        return redirect()->route('admin.manajemen-data.user.index')->with('success', 'Akun pengguna berhasil dibuat. Silakan informasikan pengguna untuk melengkapi profilnya.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Gagal membuat pengguna: ' . $e->getMessage())->withInput();
    }
}

    // Metode baru untuk mengupdate peran (role) dari halaman index
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'string', Rule::in(Role::pluck('name'))],
        ]);

        try {
            $user->syncRoles([$request->input('role')]);

            $user->update(['role' => $request->input('role')]);

            return redirect()->back()->with('success', 'Jabatan berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, User $user)
    {
        // ...
        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);


        DB::commit();

        return redirect()->route('admin.manajemen-data.user.index')->with('success', 'Pengguna berhasil diperbarui!');
        // ...
    }
}
