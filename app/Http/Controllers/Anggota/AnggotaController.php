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
    // ... metode index, create, store, edit, dan updateRole tidak berubah ...
   public function index()
    {
        // REVISI: Mengambil langsung dari model Anggota
        $anggotas = Anggota::with(['user', 'user.roles', 'unitUsaha'])->get();
        $rolesOptions = Role::pluck('name', 'name');
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
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|string|digits:16|unique:anggotas,nik',
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
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_profile_complete' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $user = null;
            $anggotaData = $request->except(['email', 'password', 'password_confirmation', 'role']);

            if ($request->filled('email') && $request->filled('password')) {
                $user = User::create([
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'is_active' => true,
                    'is_profile_complete' => true,
                ]);
                $user->assignRole($request->role);

                $anggotaData['user_id'] = $user->user_id;
                $anggotaData['email'] = $user->email;
                $anggotaData['is_profile_complete'] = true;
            } else {
                $anggotaData['user_id'] = null;
                $anggotaData['email'] = null;
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

    public function edit($anggotaId)
    {
        $anggota = Anggota::with('user.roles', 'unitUsaha')->findOrFail($anggotaId);
        $roles = Role::all();
        $unitUsahas = UnitUsaha::all();

        return view('admin.manajemen_data.anggota.edit', compact('anggota', 'roles', 'unitUsahas'));
    }

    // REVISI METODE UPDATE
    public function update(Request $request, $anggotaId)
    {
        // Temukan anggota berdasarkan ID yang dikirimkan
        $anggota = Anggota::findOrFail($anggotaId);

        // Aturan validasi hanya untuk field yang ada di form yang disederhanakan
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => ['required', 'string', 'digits:16', Rule::unique('anggotas')->ignore($anggota->anggota_id, 'anggota_id')],
            'alamat' => 'required|string|max:500',
            'no_telepon' => 'required|string|max:50',
            'jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $anggotaData = $request->only([
                'nama_lengkap', 'nik', 'alamat', 'no_telepon', 'jenis_kelamin'
            ]);

            // Tangani photo upload
            if ($request->hasFile('photo')) {
                if ($anggota->photo) {
                    Storage::disk('public')->delete($anggota->photo);
                }
                $anggotaData['photo'] = $request->file('photo')->store('photos/anggota', 'public');
            }

            // Perbarui data anggota
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
            return redirect()->route('admin.manajemen-data.anggota.index')
                             ->with('success', 'Jabatan anggota berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui jabatan: ' . $e->getMessage());
        }
    }
}
