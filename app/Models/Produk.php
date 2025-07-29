<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks'; // Pastikan nama tabelnya 'produks'

    protected $primaryKey = 'produk_id'; // Sesuai dengan primary key di migrasi
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama_produk',        // <-- Perubahan: nama_produk
        'deskripsi_produk',   // <-- Kolom baru
        'harga_beli',         // <-- Kolom baru
        'harga_jual',         // <-- Perubahan: harga_jual
        'satuan_unit',        // <-- Kolom baru
        'unit_usaha_id',      // Konsisten
        'stok_minimum',       // Konsisten
        'kategori',           // <-- Kolom baru
    ];

    // Relasi ke UnitUsaha
    public function unitUsaha()
    {
        return $this->belongsTo(UnitUsaha::class, 'unit_usaha_id', 'unit_usaha_id');
    }

    public function stok()
    {
        return $this->hasOne(Stok::class, 'produk_id', 'produk_id');
    }

    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelian::class, 'produk_id', 'produk_id');
    }

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'produk_id', 'produk_id');
    }
}
