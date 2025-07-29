<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    // No need for protected $primaryKey = 'id'; as 'id' is the default

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    public function produks()
    {
        return $this->hasMany(Produk::class);
    }
}
