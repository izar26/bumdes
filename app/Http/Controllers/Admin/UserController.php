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

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('unitUsahas')->get();
        $rolesOptions = User::getRolesOptions();
        return view('admin.manajemen_data.user.index', compact('users', 'rolesOptions'));
    }

    public function create()
    {
        $rolesOptions = User::getRolesOptions();
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        // Inisialisasi variabel dengan array kosong untuk form create
        $assignedUnitUsahaIds = [];

        return view('admin.manajemen_data.user.create', compact('rolesOptions', 'unitUsahas', 'assignedUnitUsahaIds'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(array_keys(User::getRolesOptions()))],
            'unit_usaha_ids' => 'nullable|array', // Validasi input sebagai array
            'unit_usaha_ids.*' => 'exists:unit_usahas,unit_usaha_id',
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
                'role' => $request->role,
                'is_active' => true,
            ]);

            $assignRoles = ['manajer_unit_usaha', 'admin_unit_usaha'];
            $selectedUnitUsahaIds = $request->input('unit_usaha_ids', []);

            // Hapus penugasan user lain dari unit yang baru dipilih
            UnitUsaha::whereIn('unit_usaha_id', $selectedUnitUsahaIds)->update(['user_id' => null]);

            if (in_array($user->role, $assignRoles) && !empty($selectedUnitUsahaIds)) {
                // Tetapkan user_id baru ke unit usaha yang dipilih
                UnitUsaha::whereIn('unit_usaha_id', $selectedUnitUsahaIds)
                            ->update(['user_id' => $user->user_id]);
            } else {
                // Jika peran tidak ditugaskan, pastikan tidak ada unit usaha yang dikelola
                UnitUsaha::where('user_id', $user->user_id)->update(['user_id' => null]);
            }

            DB::commit();

            return redirect()->route('admin.manajemen-data.user.index')->with('success', 'Pengguna berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan pengguna: ' . $e->getMessage())->withInput();
        }
    }

    public function show(User $user)
    {
        $rolesOptions = User::getRolesOptions();
        $user->load('unitUsahas');
        return view('admin.manajemen_data.user.show', compact('user', 'rolesOptions'));
    }

    public function edit(User $user)
    {
        $rolesOptions = User::getRolesOptions();
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        // Ambil semua ID unit usaha yang ditugaskan ke user
        $assignedUnitUsahaIds = $user->unitUsahas()->pluck('unit_usaha_id')->toArray();
        return view('admin.manajemen_data.user.edit', compact('user', 'rolesOptions', 'unitUsahas', 'assignedUnitUsahaIds'));
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->user_id, 'user_id')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(array_keys(User::getRolesOptions()))],
            'is_active' => 'boolean',
            'unit_usaha_ids' => 'nullable|array',
            'unit_usaha_ids.*' => 'exists:unit_usahas,unit_usaha_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $userData = [
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'role' => $request->role,
                'is_active' => $request->has('is_active') ? true : false,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            $assignRoles = ['manajer_unit_usaha', 'admin_unit_usaha'];
            $selectedUnitUsahaIds = $request->input('unit_usaha_ids', []);

            // Hapus penugasan user ini dari SEMUA unit usaha terlebih dahulu
            UnitUsaha::where('user_id', $user->user_id)->update(['user_id' => null]);

            // Kemudian, tetapkan kembali ke unit-unit yang baru dipilih
            if (in_array($user->role, $assignRoles) && !empty($selectedUnitUsahaIds)) {
                UnitUsaha::whereIn('unit_usaha_id', $selectedUnitUsahaIds)
                            ->update(['user_id' => $user->user_id]);
            }

            DB::commit();

            return redirect()->route('admin.manajemen-data.user.index')->with('success', 'Pengguna berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui pengguna: ' . $e->getMessage())->withInput();
        }
    }

    public function toggleActive(User $user)
    {
        if (Auth::id() === $user->user_id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menonaktifkan akun Anda sendiri!');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Pengguna '{$user->name}' berhasil {$status}.");
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->user_id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri!');
        }

        DB::transaction(function () use ($user) {
            UnitUsaha::where('user_id', $user->user_id)->update(['user_id' => null]);
            $user->delete();
        });

        return redirect()->route('admin.manajemen-data.user.index')->with('success', 'Pengguna berhasil dihapus secara permanen!');
    }
}
