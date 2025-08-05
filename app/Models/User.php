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

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'photo',
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

    public static function getRolesOptions()
    {
        return [
            'admin_bumdes' => 'Admin BUMDes',
            'manajer_unit_usaha' => 'Manajer Unit Usaha',
            'bendahara_bumdes' => 'Bendahara BUMDes',
            'kepala_desa' => 'Kepala Desa',
            'admin_unit_usaha' => 'Admin Unit Usaha',
        ];
    }

    public function unitUsahas()
    {
        return $this->hasMany(UnitUsaha::class, 'user_id', 'user_id');
    }

    // Fungsi pembantu untuk memeriksa peran menggunakan kolom role
    public function isAdminBumdes()
    {
        return $this->role === 'admin_bumdes';
    }

    public function isManajerUnitUsaha()
    {
        return $this->role === 'manajer_unit_usaha';
    }

    public function isBendaharaBumdes()
    {
        return $this->role === 'bendahara_bumdes';
    }

    public function isKepalaDesa()
    {
        return $this->role === 'kepala_desa';
    }

    public function isAdminUnitUsaha()
    {
        return $this->role === 'admin_unit_usaha';
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
        if ($this->photo && Storage::disk('public')->exists('photos/' . $this->photo)) {
            return asset('storage/photos/' . $this->photo);
        }

        return asset('vendor/adminlte/dist/img/avatar.png');
    }

    public function adminlte_desc()
    {
        $roles = self::getRolesOptions();
        return $roles[$this->role] ?? ucfirst($this->role);
    }
}
