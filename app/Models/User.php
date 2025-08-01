<?php

namespace App\Models;

// Import trait HasRoles dari Spatie
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
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

    /**
     * Get a list of available roles for selection.
     */
    public static function getRolesOptions()
    {
        return [
            'admin_bumdes' => 'Admin BUMDes',
            'manajer_unit_usaha' => 'Manajer Unit Usaha',
            'bendahara_bumdes' => 'Bendahara BUMDes',
            'kepala_desa' => 'Kepala Desa',
            'admin_unit_usaha' => 'Admin Unit Usaha'
        ];
    }

    public function unitUsahas()
    {
        return $this->hasMany(UnitUsaha::class, 'user_id', 'user_id');
    }

    // Fungsi pembantu untuk memeriksa peran menggunakan Spatie
    public function isAdminBumdes()
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

    // Scopes for active/inactive users
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
