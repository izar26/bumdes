<?php

namespace App\Http\Controllers\Anggota;

use App\Models\Anggota;
use App\Models\User;
use App\Models\UnitUsaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AnggotaController extends Controller
{
    /**
     * Menampilkan daftar semua anggota (untuk admin).
     */
    public function index()
    {
        $users = User::has('anggota')->with('anggota', 'roles')->get();
        $rolesOptions = Role::pluck('name', 'name');
        return view('admin.manajemen_data.anggota.index', compact('users', 'rolesOptions'));
    }

    /**
     * Menampilkan form untuk membuat anggota baru.
     */
    public function create()
    {
        $roles = Role::all();
        $unitUsahas = UnitUsaha::all();
        return view('admin.manajemen_data.anggota.create', compact('roles', 'unitUsahas'));
    }

    /**
     * Menyimpan anggota baru ke database dengan pembuatan akun opsional.
     */
    public function store(Request $request)
    {
        // Validasi utama untuk data Anggota
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|digits:16|unique:anggotas',
            'alamat' => 'required|string|max:500',
            'no_telepon' => 'required|string|max:50',
            'jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'unit_usaha_id' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->role, ['manajer_unit_usaha', 'admin_unit_usaha']);
                }),
                'nullable',
                'exists:unit_usahas,unit_usaha_id',
            ],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // Validasi untuk user account dibuat opsional
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_profile_complete' => 'boolean', // Pastikan ini ada di form jika ingin digunakan
        ]);

        DB::beginTransaction();
        try {
            $user = null;
            $anggotaData = $request->except(['email', 'password', 'password_confirmation', 'role']);

            // Jika email dan password diisi, buat akun User baru
            if ($request->filled('email') && $request->filled('password')) {
                $user = User::create([
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'is_active' => true,
                    'is_profile_complete' => true, // Atur profil lengkap jika akun dibuat
                ]);
                $user->assignRole($request->role);

                $anggotaData['user_id'] = $user->user_id;
                $anggotaData['email'] = $user->email;
                $anggotaData['is_profile_complete'] = true;
            } else {
                // Jika tidak ada akun, set user_id menjadi null
                $anggotaData['user_id'] = null;
                $anggotaData['email'] = null; // Email di tabel anggota juga bisa null
                $anggotaData['is_profile_complete'] = false;
            }

            $anggotaData['tanggal_daftar'] = now();
            $anggotaData['status_anggota'] = 'Aktif';
            $anggotaData['jabatan'] = Str::title(str_replace('_', ' ', $request->role));

            if ($request->hasFile('photo')) {
                $anggotaData['photo'] = $request->file('photo')->store('photos/anggota', 'public');
            }

            Anggota::create($anggotaData);

            DB::commit();

            return redirect()->route('admin.manajemen-data.anggota.index')
                             ->with('success', 'Anggota baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Gagal menambahkan anggota: ' . $e->getMessage())
                             ->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit jabatan anggota.
     */
    public function edit($userId)
    {
        $user = User::with('anggota')->findOrFail($userId);
        $roles = Role::all();
        $unitUsahas = UnitUsaha::all();

        return view('admin.manajemen_data.anggota.edit', compact('user', 'roles', 'unitUsahas'));
    }

    /**
     * Memperbarui jabatan anggota.
     */
    public function update(Request $request, $userId)
    {
        $user = User::with('anggota', 'anggota.unitUsaha')->findOrFail($userId);
        $request->validate([
            'role' => 'required|exists:roles,name',
            'unit_usaha_id' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->role, ['manajer_unit_usaha', 'admin_unit_usaha']);
                }),
                'nullable',
                'exists:unit_usahas,unit_usaha_id',
            ],
            'status_anggota' => 'required|string|in:Aktif,Nonaktif',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $user->syncRoles($request->role);

            if ($user->anggota) {
                $user->anggota->unit_usaha_id = $request->unit_usaha_id;
                $user->anggota->status_anggota = $request->status_anggota;
                $user->anggota->jabatan = Str::title(str_replace('_', ' ', $request->role));

                if ($request->hasFile('photo')) {
                    if ($user->anggota->photo) {
                        Storage::disk('public')->delete($user->anggota->photo);
                    }
                    $user->anggota->photo = $request->file('photo')->store('photos/anggota', 'public');
                }

                $user->anggota->save();
            }

            DB::commit();

            return redirect()->route('admin.manajemen-data.anggota.index')
                             ->with('success', 'Data anggota berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui data anggota: ' . $e->getMessage())
                             ->withInput();
        }
    }

    /**
     * Memperbarui peran (role) anggota langsung dari tabel.
     */
    public function updateRole(Request $request, $userId)
    {
        $user = User::with('anggota')->findOrFail($userId);
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        DB::beginTransaction();
        try {
            $user->syncRoles($request->role);

            if ($user->anggota) {
                $user->anggota->jabatan = Str::title(str_replace('_', ' ', $request->role));
                $user->anggota->save();
            }

            DB::commit();

            return redirect()->route('admin.manajemen-data.anggota.index')
                             ->with('success', 'Jabatan anggota berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }
}
