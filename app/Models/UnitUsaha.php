<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitUsaha extends Model
{
    use HasFactory;
    protected $table = 'unit_usahas';
    protected $primaryKey = 'unit_usaha_id';
    protected $fillable = ['nama_unit', 'jenis_usaha', 'bungdes_id', 'status_operasi'];

    public function produks()
    {
        return $this->hasMany(Produk::class, 'unit_usaha_id', 'unit_usaha_id');
    }
}