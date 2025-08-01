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
use Illuminate\Support\Facades\DB; // Import DB facade untuk transaksi

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('unitUsahas')->get();
        $rolesOptions = User::getRolesOptions();
        return view('admin.manajemen_data.user.index', compact('users', 'rolesOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rolesOptions = User::getRolesOptions();
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        return view('admin.manajemen_data.user.create', compact('rolesOptions', 'unitUsahas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(array_keys(User::getRolesOptions()))],
            'unit_usaha_ids' => 'array',
            'unit_usaha_ids.*' => 'exists:unit_usahas,unit_usaha_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction(); // Mulai transaksi database

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => true,
            ]);

            // Jika peran adalah manajer_unit_usaha, update relasi unit_usahas
            if ($user->role === 'manajer_unit_usaha') {
                $selectedUnitUsahaIds = $request->input('unit_usaha_ids', []);

                UnitUsaha::whereIn('unit_usaha_id', $selectedUnitUsahaIds)
                            ->update(['user_id' => $user->user_id]);

                UnitUsaha::where('user_id', $user->user_id)
                         ->whereNotIn('unit_usaha_id', $selectedUnitUsahaIds)
                         ->update(['user_id' => null]);
            } else {
                // Jika peran bukan manajer_unit_usaha, pastikan dia tidak mengelola unit usaha apapun
                UnitUsaha::where('user_id', $user->user_id)->update(['user_id' => null]);
            }

            DB::commit(); // Commit transaksi jika semua berhasil

            return redirect()->route('admin.manajemen-data.user.index')->with('success', 'Pengguna berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi error
            return redirect()->back()->with('error', 'Gagal menambahkan pengguna: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $rolesOptions = User::getRolesOptions();
        $user->load('unitUsahas');
        return view('admin.manajemen_data.user.show', compact('user', 'rolesOptions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $rolesOptions = User::getRolesOptions();
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        $assignedUnitUsahaIds = $user->unitUsahas->pluck('unit_usaha_id')->toArray();

        return view('admin.manajemen_data.user.edit', compact('user', 'rolesOptions', 'unitUsahas', 'assignedUnitUsahaIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->user_id, 'user_id')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(array_keys(User::getRolesOptions()))],
            'is_active' => 'boolean',
            'unit_usaha_ids' => 'array',
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

            // Update unit usaha assignments based on the new role
            if ($user->role === 'manajer_unit_usaha') {
                $selectedUnitUsahaIds = $request->input('unit_usaha_ids', []);

                // Menugaskan user_id ini ke unit usaha yang dipilih
                UnitUsaha::whereIn('unit_usaha_id', $selectedUnitUsahaIds)
                            ->update(['user_id' => $user->user_id]);

                // Mengosongkan user_id dari unit usaha yang sebelumnya dikelola oleh user ini,
                // tetapi sekarang tidak lagi dipilih dalam list `selectedUnitUsahaIds`.
                UnitUsaha::where('user_id', $user->user_id)
                         ->whereNotIn('unit_usaha_id', $selectedUnitUsahaIds)
                         ->update(['user_id' => null]);

            } else {
                // Jika peran bukan manajer_unit_usaha, pastikan dia tidak mengelola unit usaha apapun
                // Hapus user_id dari semua unit usaha yang sebelumnya dikelola oleh user ini
                UnitUsaha::where('user_id', $user->user_id)->update(['user_id' => null]);
            }

            DB::commit(); // Commit transaksi

            return redirect()->route('admin.manajemen-data.user.index')->with('success', 'Pengguna berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi
            return redirect()->back()->with('error', 'Gagal memperbarui pengguna: ' . $e->getMessage())->withInput();
        }
    }
public function toggleActive(User $user)
    {
        // Prevent admin from deactivating themselves
        if (Auth::id() === $user->user_id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menonaktifkan akun Anda sendiri!');
        }

        $user->is_active = !$user->is_active; // Toggle the status (true to false, false to true)
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Pengguna '{$user->name}' berhasil {$status}.");
    }

    /**
     */
    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if (Auth::id() === $user->user_id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri!');
        }

        //     return redirect()->back()->with('error', 'Pengguna ini tidak bisa dihapus karena masih memiliki transaksi terkait.');
        // }

        $user->delete(); // This will permanently delete the user
        return redirect()->route('admin.manajemen-data.user.index')->with('success', 'Pengguna berhasil dihapus secara permanen!');
    }
}
