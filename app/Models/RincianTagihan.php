<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RincianTagihan extends Model
{
    use HasFactory;

    protected $table = 'rincian_tagihan';


    // Sesuaikan nama foreign key di $fillable
    protected $fillable = [
        'tagihan_id', // <-- BERUBAH
        'deskripsi',
        'kuantitas',
        'harga_satuan',
        'subtotal',
    ];

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class); // <-- LEBIH RINGKAS
    }
}
