<?php

// app/Models/KasBank.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KasBank extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak mengikuti konvensi Laravel (plural)
    protected $table = 'kas_banks';

    // Tentukan primary key
    protected $primaryKey = 'kas_bank_id';

    // Kolom yang boleh diisi
    protected $fillable = [
        'nama_akun_kas_bank',
        'nomor_rekening',
        'saldo_saat_ini',
        'akun_id',
        'bungdes_id',
        'user_id'
    ];

    // Relasi: Satu akun Kas/Bank memiliki banyak transaksi
    public function transaksiKasBanks(): HasMany
    {
        return $this->hasMany(TransaksiKasBank::class, 'kas_bank_id', 'kas_bank_id');
    }
}