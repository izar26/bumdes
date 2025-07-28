<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Import model User
use App\Models\UnitUsaha; // Import model UnitUsaha
use Illuminate\Support\Facades\Hash; // Untuk hashing password
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Untuk Rule::unique
use Illuminate\Support\Facades\Auth; // Untuk cek user yang login

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * Menampilkan daftar pengguna.
     */
    public function index()
    {
        // Eager load the 'unitUsahas' relationship to display which units a manager oversees
        $users = User::with('unitUsahas')->get();
        $rolesOptions = User::getRolesOptions(); // Untuk menampilkan label peran
        return view('admin.manajemen_data.user.index', compact('users', 'rolesOptions'));
    }

    /**
     * Show the form for creating a new resource.
     * Menampilkan form untuk menambah pengguna baru.
     */
    public function create()
    {
        $rolesOptions = User::getRolesOptions();
        // Get all unit usahas for the assignment dropdown
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        return view('admin.user.create', compact('rolesOptions', 'unitUsahas'));
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan pengguna baru ke database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' will look for password_confirmation
            'role' => ['required', 'string', Rule::in(array_keys(User::getRolesOptions()))],
            'unit_usaha_ids' => 'array', // Expects an array of Unit Usaha IDs
            'unit_usaha_ids.*' => 'exists:unit_usahas,unit_usaha_id', // Validate each ID exists
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true, // New users are active by default
        ]);

        // If the user's role is 'manajer_unit_usaha', assign the selected units
        if ($user->role === 'manajer_unit_usaha' && $request->has('unit_usaha_ids')) {
            // First, ensure any units previously managed by this user (if this was an re-assignment scenario) are cleared.
            // (Though for 'create', this user won't have previous assignments yet, it's good practice for 'update' consistency).
            UnitUsaha::where('user_id', $user->user_id)->update(['user_id' => null]);

            // Assign the new units to this user
            UnitUsaha::whereIn('unit_usaha_id', $request->unit_usaha_ids)->update(['user_id' => $user->user_id]);
        }


        return redirect()->route('admin.user.index')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     * Menampilkan detail pengguna (opsional).
     */
    public function show(User $user)
    {
        $rolesOptions = User::getRolesOptions();
        // Eager load unit usahas to display which units this manager oversees
        $user->load('unitUsahas');
        return view('admin.manajemen_data.user.show', compact('user', 'rolesOptions'));
    }

    /**
     * Show the form for editing the specified resource.
     * Menampilkan form untuk mengedit pengguna.
     */
    public function edit(User $user)
    {
        $rolesOptions = User::getRolesOptions();
        // Get all unit usahas for the assignment dropdown
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();
        // Get the IDs of units currently managed by this user
        $assignedUnitUsahaIds = $user->unitUsahas->pluck('unit_usaha_id')->toArray();

        return view('admin.manajemen_data.user.edit', compact('user', 'rolesOptions', 'unitUsahas', 'assignedUnitUsahaIds'));
    }

    /**
     * Update the specified resource in storage.
     * Memperbarui pengguna di database.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->user_id, 'user_id')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'password' => 'nullable|string|min:8|confirmed', // Password opsional saat update
            'role' => ['required', 'string', Rule::in(array_keys(User::getRolesOptions()))],
            'is_active' => 'boolean', // Validation for the new 'is_active' field
            'unit_usaha_ids' => 'array', // For selected unit usahas
            'unit_usaha_ids.*' => 'exists:unit_usahas,unit_usaha_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->has('is_active') ? true : false, // Update status based on checkbox
        ];

        // Only update password if it's filled
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        // Update unit usaha assignments based on the new role
        if ($user->role === 'manajer_unit_usaha') {
            // First, remove this user as manager from any units they previously managed
            // This prevents a unit from being managed by multiple people if the user_id column in unit_usahas is unique
            UnitUsaha::where('user_id', $user->user_id)->update(['user_id' => null]);

            // Then, assign this user as manager to the selected units
            if ($request->has('unit_usaha_ids')) {
                UnitUsaha::whereIn('unit_usaha_id', $request->unit_usaha_ids)->update(['user_id' => $user->user_id]);
            }
        } else {
            // If the user's role is no longer 'manajer_unit_usaha', clear any existing assignments
            UnitUsaha::where('user_id', $user->user_id)->update(['user_id' => null]);
        }

        return redirect()->route('admin.user.index')->with('success', 'Pengguna berhasil diperbarui!');
    }

    /**
     */
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

        // Optional: Implement checks if the user has critical related data
        // For example, if user is tied to transactions and you want to prevent deletion
        // if ($user->transactions()->count() > 0) {
        //     return redirect()->back()->with('error', 'Pengguna ini tidak bisa dihapus karena masih memiliki transaksi terkait.');
        // }

        $user->delete(); // This will permanently delete the user
        return redirect()->route('admin.user.index')->with('success', 'Pengguna berhasil dihapus secara permanen!');
    }
}
