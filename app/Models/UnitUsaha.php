<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitUsaha extends Model
{
    use HasFactory;

    protected $primaryKey = 'unit_usaha_id'; // Custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama_unit',
        'jenis_usaha',
        'tanggal_mulai_operasi',
        'status_operasi',
        'user_id', // Untuk penanggung jawab (foreign key ke users)
    ];

    protected $casts = [
        'tanggal_mulai_operasi' => 'date',
    ];

public function users()
{
    return $this->belongsToMany(User::class, 'unit_usaha_user', 'unit_usaha_id', 'user_id');
}

    public function produks()
    {
        return $this->hasMany(Produk::class, 'unit_usaha_id', 'unit_usaha_id');
    }
}
