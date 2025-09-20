<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';


    protected $fillable = [
        'nama',
        'alamat',
        'status_pelanggan',
        'kontak'
    ];

    // Relasi menjadi lebih bersih tanpa parameter tambahan
    public function tagihan(): HasMany
{
    return $this->hasMany(Tagihan::class, 'pelanggan_id');
}

}
