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
     public function index()
    {
        $anggotas = Anggota::with(['user', 'user.roles', 'unitUsaha'])->latest()->get();
        $rolesOptions = Role::where('name', '!=', 'admin_bumdes')->pluck('name');
        return view('admin.manajemen_data.anggota.index', compact('anggotas', 'rolesOptions'));
    }

    public function create()
    {
        $roles = Role::all();
        $unitUsahas = UnitUsaha::all();
        return view('admin.manajemen_data.anggota.create', compact('roles', 'unitUsahas'));
    }

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
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'nullable|required_with:email|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_profile_complete' => 'boolean',
        ]);
        DB::beginTransaction();
        try {
            $anggotaData = $request->except(['email', 'password', 'password_confirmation', 'role']);
            if ($request->filled('email') && $request->filled('password')) {
                $user = User::create([
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                    'username' => $request->username,
                    'password' => Hash::make($request->password),
                    'is_active' => true,
                ]);
                $user->assignRole($request->role);
                $anggotaData['user_id'] = $user->user_id;
            }
            $anggotaData['tanggal_daftar'] = now();
            $anggotaData['status_anggota'] = 'Aktif';
            $anggotaData['jabatan'] = Str::title(str_replace('_', ' ', $request->role));
            if ($request->hasFile('photo')) {
                $anggotaData['photo'] = $request->file('photo')->store('photos/anggota', 'public');
            }
            Anggota::create($anggotaData);
            DB::commit();
            return redirect()->route('admin.manajemen-data.anggota.index')->with('success', 'Anggota baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan anggota: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($anggotaId)
    {
        $anggota = Anggota::with('user.roles', 'unitUsaha')->findOrFail($anggotaId);
        $roles = Role::all(); // REVISI: Ambil semua role untuk dropdown
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        return view('admin.manajemen_data.anggota.edit', compact('anggota', 'roles', 'unitUsahas')); // REVISI: Kirim variabel $roles
    }

    // REVISI METODE UPDATE UNTUK MENAMBAH ROLE
    public function update(Request $request, $anggotaId)
    {
        $anggota = Anggota::findOrFail($anggotaId);
        $user = $anggota->user;

        // Validasi untuk semua field di form edit
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nik' => ['required', 'string', 'digits:16', Rule::unique('anggotas')->ignore($anggota->anggota_id, 'anggota_id')],
            'alamat' => 'required|string|max:500',
            'no_telepon' => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'status_anggota' => 'required|string|in:Aktif,Nonaktif',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            // Validasi untuk role (Wajib ada)
            'role' => 'required|exists:roles,name',

            // Validasi untuk unit_usaha_id (Kondisional)
            'unit_usaha_id' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->role, ['manajer_unit_usaha', 'admin_unit_usaha']);
                }),
                'nullable',
                'exists:unit_usahas,unit_usaha_id',
            ],

            // Validasi untuk user account jika ada user
            'email' => ['nullable','string','email','max:255', Rule::unique('users', 'email')->ignore(optional($user)->user_id, 'user_id')],
            'password' => 'nullable|string|min:8|confirmed',
        ];

        $request->validate($rules);

        DB::beginTransaction();
        try {
            // Update data User jika ada
            if ($user) {
                $user->name = $request->nama_lengkap;
                $user->email = $request->email;
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                $user->syncRoles($request->role); // Sync role di model User
                $user->save();
            }

            // Update data Anggota
            $anggotaData = $request->only([
                'nama_lengkap', 'nik', 'alamat', 'no_telepon', 'jenis_kelamin',
                'unit_usaha_id', 'status_anggota'
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
    public function show(Anggota $anggota)
    {
        // Memuat relasi yang diperlukan untuk ditampilkan di view
        $anggota->load(['user', 'user.roles', 'unitUsaha']);
        return view('admin.manajemen_data.anggota.show', compact('anggota'));
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
