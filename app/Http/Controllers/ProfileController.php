<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use App\Models\Anggota;
use Illuminate\Validation\Rule;
use App\Models\UnitUsaha;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil dengan tab
     */
    public function edit()
    {
        $user = Auth::user();
        $anggota = $user->anggota ?? new Anggota();
        $unitUsahas = UnitUsaha::orderBy('nama_unit')->get();

        return view('admin.profile.edit', compact('user', 'anggota', 'unitUsahas'));
    }

    /**
     * Update data akun (nama, email, password, photo)
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required','string','email','max:255', Rule::unique('users','email')->ignore($user->user_id ?? $user->id, $user->getKeyName())],
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('tab', 'account');
        }

        DB::beginTransaction();
        try {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('photo')) {
                if (!empty($user->photo) && Storage::exists('public/photos/' . $user->photo)) {
                    Storage::delete('public/photos/' . $user->photo);
                }
                $photoPath = $request->file('photo')->store('public/photos');
                $userData['photo'] = basename($photoPath);
            }

            $user->update($userData);

            DB::commit();

            Auth::setUser($user->fresh());

            return redirect()->route('profile.edit')->with('success', 'Profil akun berhasil diperbarui!')->with('tab', 'account');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui profil: ' . $e->getMessage())->withInput()->with('tab', 'account');
        }
    }

    /**
     * Update data pribadi (NIK, alamat, no_telepon, dll.)
     */
    public function updatePersonal(Request $request)
    {
        $user = Auth::user();

        $anggota = $user->anggota ?? new Anggota();

        $validator = Validator::make($request->all(), [
            'nik' => ['required', 'string', 'digits:16', Rule::unique('anggotas', 'nik')->ignore(optional($anggota)->anggota_id, 'anggota_id')],
            'alamat' => 'required|string|max:500',
            'no_telepon' => 'required|string|max:50',
            'jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'unit_usaha_id' => [
                'nullable',
                'exists:unit_usahas,unit_usaha_id',
                Rule::requiredIf(function () use ($user) {
                    return $user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha']);
                }),
            ],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('tab', 'personal');
        }

        DB::beginTransaction();
        try {
            // Data untuk di-create atau di-update
            $anggotaData = [
                'nama_lengkap' => $user->name, // Mengambil dari tabel users
                'nik' => $request->nik,
                'alamat' => $request->alamat,
                'no_telepon' => $request->no_telepon,
                'jenis_kelamin' => $request->jenis_kelamin,
                'jabatan' => ucwords(str_replace('_', ' ', $user->getRoleNames()->first() ?? '')),
                'email' => $user->email, // Mengambil dari tabel users
            ];

            // Menangani kolom 'unit_usaha_id'
            if ($user->hasAnyRole(['manajer_unit_usaha', 'admin_unit_usaha'])) {
                $anggotaData['unit_usaha_id'] = $request->unit_usaha_id;
            } else {
                $anggotaData['unit_usaha_id'] = $user->anggota->unit_usaha_id ?? null;
            }

            // Mengisi tanggal_daftar hanya saat membuat record baru
            if (!$user->anggota) {
                $anggotaData['tanggal_daftar'] = now();
                $anggotaData['status_anggota'] = 'Aktif';
            }

            $anggota = Anggota::updateOrCreate(
                ['user_id' => $user->user_id],
                $anggotaData
            );

            // Mengelola upload foto
            if ($request->hasFile('photo')) {
                if (!empty($anggota->photo) && Storage::disk('public')->exists('photo_anggota/' . $anggota->photo)) {
                    Storage::disk('public')->delete('photo_anggota/' . $anggota->photo);
                }
                $photoPath = $request->file('photo')->store('photo_anggota', 'public');
                $anggota->photo = basename($photoPath);
                $anggota->save();
            }

            $user->is_profile_complete = true;
            $user->save();

            DB::commit();

            Auth::setUser($user->fresh());

            return redirect()->route('profile.edit')->with('success', 'Data pribadi berhasil diperbarui!')->with('tab', 'personal');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data pribadi: ' . $e->getMessage())->withInput()->with('tab', 'personal');
        }
    }
}
