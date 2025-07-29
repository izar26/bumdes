<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    use HasFactory;
    protected $table = 'stok';
    protected $primaryKey = 'stok_id';
    public $timestamps = false; // Tabel ini tidak punya created_at/updated_at
    protected $fillable = [
        'produk_id',
        'unit_usaha_id',
        'jumlah_stok',
        'tanggal_perbarui',
    ];
}