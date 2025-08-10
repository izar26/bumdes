<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Menampilkan form edit profil.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Memperbarui informasi akun (nama, email, password, foto).
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::user();
        $anggota = $user->anggota;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Perbarui nama di tabel anggota juga jika relasi ada
            if ($anggota) {
                $anggota->nama_lengkap = $request->name;
                $anggota->save();
            }

            // Tangani upload foto
            if ($request->hasFile('photo')) {
                if (!$anggota) {
                    return redirect()->back()->with('error', 'Silakan lengkapi informasi personal terlebih dahulu untuk mengunggah foto.');
                }

                // Hapus foto lama jika ada
                if ($anggota->photo && Storage::disk('public')->exists($anggota->photo)) {
                    Storage::disk('public')->delete($anggota->photo);
                }

                // Simpan foto baru
                $path = $request->file('photo')->store('photos/anggota', 'public');
                $anggota->photo = $path;
                $anggota->save();
            }

            DB::commit();

            return redirect()->route('admin.profile.edit')->with(['success' => 'Informasi akun berhasil diperbarui.', 'tab' => 'account']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => 'Gagal memperbarui akun: ' . $e->getMessage(), 'tab' => 'account']);
        }
    }

    /**
     * Memperbarui informasi personal (anggota).
     */
    public function updatePersonal(Request $request)
    {
        $user = Auth::user();

        // Pastikan ada relasi anggota sebelum melanjutkan
        if (!$user->anggota) {
            // Jika tidak ada data anggota, buat entri baru terlebih dahulu
            $anggota = new \App\Models\Anggota();
            $anggota->user_id = $user->user_id;
        } else {
            $anggota = $user->anggota;
        }

        $rules = [
            'nik' => ['required', 'string', 'digits:16', Rule::unique('anggotas')->ignore($anggota->anggota_id, 'anggota_id')],
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat' => 'required|string|max:500',
            'no_telepon' => 'required|string|max:50',
        ];

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $anggota->fill($request->only(['nik', 'jenis_kelamin', 'alamat', 'no_telepon']));
            $anggota->is_profile_complete = true; // Tandai profil sebagai lengkap
            $anggota->save();

            // Tandai profil user sebagai lengkap
            $user->is_profile_complete = true;
            $user->save();

            DB::commit();

            return redirect()->route('admin.profile.edit')->with(['success' => 'Informasi personal berhasil diperbarui.', 'tab' => 'personal']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => 'Gagal memperbarui informasi personal: ' . $e->getMessage(), 'tab' => 'personal']);
        }
    }
}
