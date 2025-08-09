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
use App\Models\Anggota;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
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

    public function anggota()
    {
        return $this->hasOne(Anggota::class, 'user_id', 'user_id');
    }

// app/Models/User.php

public function unitUsahas()
{
    // Parameter: Model yang terkait, nama tabel perantara, foreign key dari model ini di tabel perantara, foreign key dari model terkait di tabel perantara
    return $this->belongsToMany(UnitUsaha::class, 'unit_usaha_user', 'user_id', 'unit_usaha_id');
}// Tambahkan metode baru untuk peran direktur
    public function isDirekturBumdes()
    {
        return $this->hasRole('direktur_bumdes');
    }

    // Metode isXXX() lainnya
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

    public function isSekretarisBumdes()
    {
        return $this->hasRole('sekretaris_bumdes');
    }

    public function isAnggota()
    {
        return $this->hasRole('anggota');
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
        if ($this->anggota && $this->anggota->photo && Storage::disk('public')->exists('photos/' . $this->anggota->photo)) {
            return asset('storage/photos/' . $this->anggota->photo);
        }

        return asset('vendor/adminlte/dist/img/avatar.png');
    }

    public function adminlte_desc()
    {
        $role = $this->getRoleNames()->first();
        if ($role) {
            return Str::title(str_replace('_', ' ', $role));
        }
        return 'Anggota';
    }
}
