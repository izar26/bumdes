<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tagihan';


    // Sesuaikan nama foreign key di $fillable
    protected $fillable = [
        'pelanggan_id', // <-- BERUBAH
        'petugas_id', // <-- BERUBAH
        'periode_tagihan',
        'tanggal_cetak',
        'meter_awal',
        'meter_akhir',
        'total_pemakaian_m3',
        'total_harus_dibayar',
        'status_pembayaran',
    ];

    protected $casts = [
        'periode_tagihan' => 'date',
        'tanggal_cetak' => 'date',
    ];

   public function pelanggan(): BelongsTo
{
    return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
}

public function petugas(): BelongsTo
{
    return $this->belongsTo(Petugas::class, 'petugas_id');
}

    public function rincian(): HasMany
    {
        return $this->hasMany(RincianTagihan::class); // <-- LEBIH RINGKAS
    }
}
