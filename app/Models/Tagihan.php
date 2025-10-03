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
    'pelanggan_id',
    'petugas_id',
    'periode_tagihan',
    'meter_awal',
    'meter_akhir',
    'total_pemakaian_m3',
    'subtotal_pemakaian',
    'biaya_lainnya',
    'denda',
    'tunggakan',
    'total_harus_dibayar',
    'jumlah_dibayar',
    'status_pembayaran',
    'tanggal_cetak',
    'tanggal_pembayaran',
];

   protected $casts = [
    'tanggal_cetak' => 'datetime',
    'tanggal_pembayaran' => 'datetime',
    'meter_awal' => 'integer',
    'meter_akhir' => 'integer',
    'periode_tagihan' => 'date',
    'total_harus_dibayar' => 'float',
    'jumlah_dibayar' => 'float',
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
