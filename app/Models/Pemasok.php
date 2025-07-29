<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemasok extends Model
{
    use HasFactory;
    protected $table = 'pemasoks';
    protected $primaryKey = 'pemasok_id';
    protected $fillable = [
        'nama_pemasok',
        'alamat',
        'no_telepon',
        'email',
        'unit_usaha_id',
    ];

    public function unitUsaha()
    {
        return $this->belongsTo(UnitUsaha::class, 'unit_usaha_id', 'unit_usaha_id');
    }
}