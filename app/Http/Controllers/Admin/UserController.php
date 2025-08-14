<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UnitUsaha;
use App\Models\Anggota;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua user.
     */
    public function index()
    {
        $users = User::with('roles', 'anggota')->get();
        $rolesOptions = Role::pluck('name');
        return view('admin.manajemen_data.user.index', compact('users', 'rolesOptions'));
    }

    /**
     * Menampilkan form untuk membuat user baru.
     */
    public function create()
    {
        $rolesOptions = Role::pluck('name', 'name');
        // Filter roles agar 'anggota' menjadi default dan tidak bisa dipilih
        $rolesOptions = $rolesOptions->except(['anggota']);
        return view('admin.manajemen_data.user.create', compact('rolesOptions'));
    }

    /**
     * Menyimpan user baru ke database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(Role::pluck('name'))],
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
                'is_active' => false,
                'is_profile_complete' => false,
            ]);

            $user->assignRole($request->input('role'));

            if ($user->hasRole(['anggota', 'anggota_baru'])) {
                Anggota::create([
                    'user_id' => $user->user_id,
                    'nama_lengkap' => $request->name,
                    'tanggal_daftar' => now(),
                    'status_anggota' => 'aktif',
                    'is_profile_complete' => false,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.manajemen-data.user.index')->with('success', 'Akun pengguna berhasil dibuat. Silakan informasikan pengguna untuk melengkapi profilnya.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat pengguna: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan user tertentu.
     */
    public function show(User $user)
    {
        $user->load('anggota', 'roles');
        return view('admin.manajemen_data.user.show', compact('user'));
    }

    /**
     * Menampilkan form untuk mengedit user.
     */
    public function edit(User $user)
    {
        $rolesOptions = Role::pluck('name', 'name');
        return view('admin.manajemen_data.user.edit', compact('user', 'rolesOptions'));
    }

    /**
     * Memperbarui user di database.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->user_id . ',user_id',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
            'is_profile_complete' => 'nullable|boolean',
            // Validasi data anggota
            'anggota.nama_lengkap' => 'nullable|string|max:255',
            'anggota.nik' => ['nullable', 'string', 'digits:16', Rule::unique('anggotas', 'nik')->ignore(optional($user->anggota)->anggota_id, 'anggota_id')],
            'anggota.alamat' => 'nullable|string|max:500',
            'anggota.no_telepon' => 'nullable|string|max:50',
            'anggota.jenis_kelamin' => 'nullable|string|in:Laki-laki,Perempuan',
            'anggota.jabatan' => 'nullable|string|max:100',
            'anggota.status_anggota' => 'nullable|string|in:Aktif,Nonaktif',
        ]);

        DB::beginTransaction();
        try {
            $userData = $request->only(['name', 'username', 'email', 'is_active', 'is_profile_complete']);

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            if ($user->hasRole(['anggota', 'anggota_baru']) && $user->anggota) {
                $user->anggota->update($request->input('anggota', []));
            }

            DB::commit();
            return redirect()->route('admin.manajemen-data.user.index')->with('success', 'Pengguna berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui pengguna: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus user dari database.
     */
    public function destroy(User $user)
    {
        if ($user->user_id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        DB::beginTransaction();
        try {
            if ($user->anggota) {
                $user->anggota->delete();
            }

            $user->delete();
            DB::commit();
            return redirect()->route('admin.manajemen-data.user.index')->with('success', 'Pengguna berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Mengubah peran (role) user.
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'string', Rule::in(Role::pluck('name'))],
        ]);

        DB::beginTransaction();
        try {
            $user->syncRoles([$request->input('role')]);

            if ($user->hasRole(['anggota', 'anggota_baru'])) {
                Anggota::firstOrCreate(
                    ['user_id' => $user->user_id],
                    [
                        'nama_lengkap' => $user->name,
                        'tanggal_daftar' => now(),
                        'status_anggota' => 'aktif',
                        'is_profile_complete' => false,
                    ]
                );
            } else {
                if ($user->anggota) {
                    $user->anggota->delete();
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Jabatan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }

    /**
     * Mengubah status aktif user.
     */
    public function toggleActive(User $user)
    {
        if ($user->user_id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()->back()->with('success', 'Status pengguna berhasil diperbarui!');
    }
}
