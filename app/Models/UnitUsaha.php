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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function produks()
    {
        return $this->hasMany(Produk::class, 'unit_usaha_id', 'unit_usaha_id');
    }
}
