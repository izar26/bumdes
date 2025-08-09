<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Anggota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncUsersToAnggotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Temukan semua user yang belum memiliki relasi anggota
        $usersWithoutAnggota = User::doesntHave('anggota')->get();

        $this->command->info("Menemukan " . $usersWithoutAnggota->count() . " pengguna yang belum memiliki data anggota.");

        DB::beginTransaction();

        try {
            foreach ($usersWithoutAnggota as $user) {
                // Tentukan jabatan default, misalnya 'anggota_baru' atau berdasarkan role yang ada
                $role = $user->roles->isNotEmpty() ? $user->getRoleNames()->first() : 'anggota_baru';

                // Buat entri baru di tabel anggotas
                Anggota::create([
                    'user_id' => $user->user_id,
                    'nama_lengkap' => $user->name,
                    'nik' => Str::random(16), // Atur NIK default atau acak jika belum ada
                    'alamat' => 'Belum diisi',
                    'no_telepon' => 'Belum diisi',
                    'tanggal_daftar' => now(),
                    'jenis_kelamin' => 'Laki-laki', // Atau tentukan default lain
                    'status_anggota' => 'aktif',
                    'jabatan' => ucwords(str_replace('_', ' ', $role)),
                    'is_profile_complete' => false,
                ]);

                $this->command->info("Data anggota berhasil dibuat untuk pengguna: " . $user->name);
            }

            DB::commit();
            $this->command->info("Proses sinkronisasi pengguna ke anggota selesai.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Gagal menyinkronkan pengguna ke anggota: " . $e->getMessage());
        }
    }
}
