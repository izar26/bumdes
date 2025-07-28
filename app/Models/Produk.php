<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;
    protected $table = 'produks';
    protected $primaryKey = 'produk_id';
    protected $fillable = [
        'nama_produk',
        'deskripsi_produk',
        'harga_beli',
        'harga_jual',
        'satuan_unit',
        'unit_usaha_id',
    ];

    public function unitUsaha()
    {
        return $this->belongsTo(UnitUsaha::class, 'unit_usaha_id', 'unit_usaha_id');
    }
}
