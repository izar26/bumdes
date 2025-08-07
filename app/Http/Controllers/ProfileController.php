<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\Anggota;

class ProfileController extends Controller
{
    /**
     * Tampilkan form edit profil.
     */
    public function edit()
    {
        $user = Auth::user();

        // Cek jika user adalah anggota_baru dan belum melengkapi profil
        if ($user->hasRole('anggota_baru') && !$user->is_active) {
            return view('anggota.profil_lengkap');
        }

        // Jika sudah melengkapi profil, tampilkan halaman edit profil biasa
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Perbarui data profil.
     */
    public function update(Request $request)
{
    $user = Auth::user();

    // Logika untuk anggota_baru yang melengkapi profil
    if ($user->hasRole('anggota_baru') && !$user->is_active) {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|unique:anggotas,nik',
            'alamat' => 'required|string|max:500',
            'no_telepon' => 'required|string|max:50',
            'jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'unit_usaha_id' => 'required|exists:unit_usahas,unit_usaha_id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'jabatan' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Ambil atau buat record Anggota yang terkait dengan user saat ini
            $anggota = Anggota::firstOrCreate(['user_id' => $user->id]);

            $data = $request->except(['_token']);

            // Handle upload foto
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('public/foto_anggota');
                $data['foto'] = basename($fotoPath);
            }

            // Update data di tabel anggotas
            $anggota->update($data);

            // Update status di tabel users
            $user->update(['is_active' => true]);

            // Opsional: Ubah peran dari anggota_baru menjadi anggota
            // Anda perlu menyesuaikan ini dengan model peran Anda.
            // $user->removeRole('anggota_baru');
            // $user->assignRole('anggota');

            DB::commit();
            return redirect()->route('admin.dashboard')->with('success', 'Profil Anda berhasil dilengkapi. Akun sudah aktif!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal melengkapi profil: ' . $e->getMessage())->withInput();
        }
    }
}
}
