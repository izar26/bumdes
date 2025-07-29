<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembelian extends Model
{
    use HasFactory;
    protected $table = 'detail_pembelians';
    protected $primaryKey = 'detail_pembelian_id';
    protected $fillable = [
        'pembelian_id',
        'produk_id',
        'jumlah',
        'harga_unit',
        'subtotal',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'produk_id');
    }
}