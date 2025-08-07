<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use App\Models\UnitUsaha;
use App\Models\Anggota; // BARU: Import model Anggota

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role', // Tetap ada untuk menyimpan peran
        'photo', // Kolom ini bisa dihapus jika foto hanya disimpan di tabel anggotas
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // BARU: Relasi ke tabel anggotas
    public function anggota()
    {
        return $this->hasOne(Anggota::class, 'user_id', 'user_id');
    }

    public static function getRolesOptions()
    {
        return [
            'admin_bumdes' => 'Direktur BUMDesa', // PERBAIKAN: Typo
            'manajer_unit_usaha' => 'Manajer Unit Usaha',
            'bendahara_bumdes' => 'Bendahara BUMDesa',
            'kepala_desa' => 'Kepala Desa',
            'admin_unit_usaha' => 'Admin Unit Usaha',
            'sekretaris_bumdes' => 'Sekretaris BUMDesa',
            'anggota_baru' => 'Anggota Baru', // BARU: Tambahkan peran baru
        ];
    }

    public function unitUsahas()
    {
        return $this->hasMany(UnitUsaha::class, 'user_id', 'user_id');
    }

    // Fungsi pembantu untuk memeriksa peran
    // CATATAN: Karena menggunakan Spatie\Permission, lebih disarankan menggunakan $user->hasRole('nama_role')
    // Metode ini tetap bisa digunakan jika Anda suka
    public function isAdminDirekturBumdes()
    {
        return $this->hasRole('admin_bumdes');
    }

    public function isManajerUnitUsaha()
    {
        return $this->hasRole('manajer_unit_usaha');
    }

    public function isBendaharaBumdes()
    {
        return $this->hasRole('bendahara_bumdes');
    }

    public function isKepalaDesa()
    {
        return $this->hasRole('kepala_desa');
    }

    public function isAdminUnitUsaha()
    {
        return $this->hasRole('admin_unit_usaha');
    }
    public function isSekretarisBumdes()
    {
        return $this->hasRole('sekretaris_bumdes');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function adminlte_image()
    {
        // PERBAIKAN: Ambil foto dari tabel anggotas jika ada
        if ($this->anggota && $this->anggota->foto && Storage::disk('public')->exists('foto_anggota/' . $this->anggota->foto)) {
            return asset('storage/foto_anggota/' . $this->anggota->foto);
        }

        // Kembali ke foto default jika tidak ada
        return asset('vendor/adminlte/dist/img/avatar.png');
    }

    public function adminlte_desc()
    {
        $roles = self::getRolesOptions();
        return $roles[$this->role] ?? ucfirst($this->role);
    }
}
