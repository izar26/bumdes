<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $primaryKey = 'produk_id'; // Keep this as produk_id for the Produk model

    protected $fillable = [
        'nama_produk',
        'deskripsi_produk',
        'harga_beli',
        'harga_jual',
        'satuan_unit',
        'unit_usaha_id',
        'stok_minimum',
        'kategori_id', // Add this to fillable
    ];

    public function unitUsaha()
    {
        return $this->belongsTo(UnitUsaha::class, 'unit_usaha_id', 'unit_usaha_id');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function stok()
    {
        return $this->hasOne(Stok::class, 'produk_id', 'produk_id');
    }

    public function getCurrentStockAttribute()
    {
        return $this->stok ? $this->stok->jumlah_stok : 0;
    }
}
