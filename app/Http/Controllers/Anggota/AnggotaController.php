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
        // FIX: Eager loading relasi user.unitUsahas untuk view
        $anggotas = Anggota::with(['user', 'user.roles', 'unitUsaha', 'user.unitUsahas'])->latest()->get();
        $rolesOptions = Role::where('name', '!=', 'admin_bumdes')->pluck('name');
        return view('admin.manajemen_data.anggota.index', compact('anggotas', 'rolesOptions'));
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
     * Menyimpan anggota baru ke database dengan pembuatan akun wajib.
     */
    public function store(Request $request)
    {
        if (!$request->filled('role')) {
            $request->merge(['role' => 'anggota']);
        }
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|digits:16|unique:anggotas,nik',
            'alamat' => 'required|string|max:500',
            'no_telepon' => 'required|string|max:50',
            'jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'unit_usaha_id' => ['nullable','exists:unit_usahas,unit_usaha_id', Rule::requiredIf(fn() => in_array($request->role, ['manajer_unit_usaha', 'admin_unit_usaha']))],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            // FIX: Tambahkan validasi username
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
        ]);

        DB::beginTransaction();
        try {
            // Buat user baru (wajib)
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'is_active' => true,
                'is_profile_complete' => true,
            ]);

            $user->assignRole($request->role);
if (in_array($request->role, ['manajer_unit_usaha', 'admin_unit_usaha']) && $request->filled('unit_usaha_id')) {
    $user->unitUsahas()->sync([$request->unit_usaha_id]);
}
            $anggotaData = $request->except(['email', 'password', 'password_confirmation', 'role', 'username']);

            $anggotaData['user_id'] = $user->user_id;
            $anggotaData['tanggal_daftar'] = now();
            $anggotaData['status_anggota'] = 'Aktif';
            $anggotaData['jabatan'] = Str::title(str_replace('_', ' ', $request->role));
            $anggotaData['email'] = $request->email;
            $anggotaData['is_profile_complete'] = true;

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
     * Menampilkan form untuk mengedit data anggota.
     */
    public function edit($anggotaId)
    {
        // FIX: Tambahkan 'user.unitUsahas' ke eager loading
        $anggota = Anggota::with(['user.roles', 'unitUsaha', 'user.unitUsahas'])->findOrFail($anggotaId);
        $roles = Role::all();
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        return view('admin.manajemen_data.anggota.edit', compact('anggota', 'roles', 'unitUsahas'));
    }

    /**
     * Memperbarui data anggota.
     */
    public function update(Request $request, $anggotaId)
    {
        $anggota = Anggota::findOrFail($anggotaId);
        $user = $anggota->user; // Mendapatkan user yang terkait

        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nik' => ['required', 'string', 'digits:16', Rule::unique('anggotas')->ignore($anggota->anggota_id, 'anggota_id')],
            'alamat' => 'required|string|max:500',
            'no_telepon' => 'required|string|max:50',
            'jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'status_anggota' => 'required|string|in:Aktif,Nonaktif',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            // Validasi untuk data User
            'role' => ['nullable', 'exists:roles,name'], // Role bisa null jika tidak ada user
            'unit_usaha_id' => [
                'nullable', 'exists:unit_usahas,unit_usaha_id',
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->role, ['manajer_unit_usaha', 'admin_unit_usaha']);
                }),
            ],
            // Validasi email dan password hanya jika user ada
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore(optional($user)->user_id, 'user_id')],
            'password' => 'nullable|string|min:8|confirmed',
        ];

        // Validasi tambahan untuk user jika ada
        if ($user) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')];
            $rules['role'] = ['required', 'exists:roles,name'];
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            // Update data Anggota
            $anggotaData = $request->only([
                'nama_lengkap', 'nik', 'alamat', 'no_telepon', 'jenis_kelamin',
                'status_anggota', 'unit_usaha_id'
            ]);
            $anggotaData['jabatan'] = Str::title(str_replace('_', ' ', $request->role));
            $anggotaData['email'] = $request->email;

            if ($request->hasFile('photo')) {
                if ($anggota->photo) {
                    Storage::disk('public')->delete($anggota->photo);
                }
                $anggotaData['photo'] = $request->file('photo')->store('photos/anggota', 'public');
            }
            $anggota->update($anggotaData);

            // Update data User jika ada
            if ($user) {
                $userData = $request->only(['nama_lengkap', 'email']);
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }
                $user->update($userData);

                // Sinkronkan role dan unit usaha
                $user->syncRoles($request->role);
                if ($request->filled('unit_usaha_id')) {
                    $user->unitUsahas()->sync([$request->unit_usaha_id]);
                } else {
                    $user->unitUsahas()->detach();
                }
            }

            DB::commit();

            return redirect()->route('admin.manajemen-data.anggota.index')
                             ->with('success', 'Data anggota berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui data: ' . $e->getMessage())
                             ->withInput();
        }
    }

       public function updateRole(Request $request, $userId)
    {
        $user = User::with('anggota')->findOrFail($userId);
        $request->validate(['role' => 'required|exists:roles,name']);
        DB::beginTransaction();
        try {
            $user->syncRoles($request->role);

            if ($user->anggota) {
                $user->anggota->jabatan = Str::title(str_replace('_', ' ', $request->role));
                $user->anggota->save();
            }
            DB::commit();
            return redirect()->back()
                             ->with('success', 'Jabatan anggota berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }

    public function destroy(Anggota $anggota)
    {
        if ($anggota->user && $anggota->user->user_id === Auth::id()) {
            return redirect()->route('admin.manajemen-data.anggota.index')
                             ->with('error', 'Anda tidak dapat menghapus data anggota Anda sendiri.');
        }

        DB::beginTransaction();
        try {
            $namaAnggota = $anggota->nama_lengkap ?? 'Anggota';
            $user = $anggota->user;

            if ($anggota->photo) {
                Storage::disk('public')->delete($anggota->photo);
            }

            $anggota->delete();

            if ($user) {
                if (method_exists($user, 'unitUsahas')) {
                    $user->unitUsahas()->detach();
                }

                $user->delete();
            }

            DB::commit();

            return redirect()->route('admin.manajemen-data.anggota.index')
                             ->with('success', "Anggota '{$namaAnggota}' beserta data terkait berhasil dihapus.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.manajemen-data.anggota.index')
                             ->with('error', 'Gagal menghapus anggota: ' . $e->getMessage());
        }
    }
}
