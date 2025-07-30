<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
            'admin' => 'Admin BUMDes',
            'manajer_unit_usaha' => 'Manajer Unit Usaha',
            'staf' => 'Staf',
        ];
    }

    // Add the hasMany relationship for UnitUsaha
    /**
     * Get the unit usahas that the user is responsible for.
     */
    public function unitUsahas()
    {
        return $this->hasMany(UnitUsaha::class, 'user_id', 'user_id');
    }

    // Helper methods for role checking (keep these)
    public function isAdminBumdes()
    {
        return $this->role === 'admin';
    }

    public function isManajerUnitUsaha()
    {
        return $this->role === 'manajer_unit_usaha';
    }

    public function isStaf()
    {
        return $this->role === 'staf';
    }

    // Scopes for active/inactive users (keep these)
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
