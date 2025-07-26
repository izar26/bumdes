<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Uncomment if you use email verification
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    public $incrementing = true;

    protected $keyType = 'int'; // Or 'bigInteger' if your 'user_id' in migration is bigIncrements()

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     * Ensure all columns you expect to set are here.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username', // <--- Your custom login field
        'password',
        'role', // <--- Your custom role field
        'is_active', // <--- Your custom field
        'last_login', // <--- Your custom field
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed', // Keep this for password hashing
            'is_active' => 'boolean', // <--- Cast for your custom field
            'last_login' => 'datetime', // <--- Cast for your custom field
        ];
    }

}
