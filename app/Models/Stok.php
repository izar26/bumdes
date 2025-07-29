<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    use HasFactory;

    protected $table = 'stok'; 
    protected $primaryKey = 'stok_id'; // Sesuai dengan primary key di migrasi stok
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'produk_id',
        'unit_usaha_id',
        'jumlah_stok',
        'tanggal_perbarui',
        'lokasi_penyimpanan',
    ];

    protected $casts = [
        'tanggal_perbarui' => 'datetime',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }

    public function unitUsaha()
    {
        return $this->belongsTo(UnitUsaha::class, 'unit_usaha_id', 'unit_usaha_id');
    }
}
