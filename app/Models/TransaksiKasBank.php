<?php

// app/Models/TransaksiKasBank.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiKasBank extends Model
{
    use HasFactory;

    protected $table = 'transaksi_kas_banks';
    protected $primaryKey = 'transaksi_kas_bank_id';

    // Nonaktifkan auto-increment jika primary key bukan integer
    // public $incrementing = false; 

    protected $fillable = [
        'kas_bank_id',
        'tanggal_transaksi',
        'metode_transaksi', // Meskipun ada di DB, kita fokus ke debit/kredit dulu
        'jumlah_debit',
        'jumlah_kredit',
        'deskripsi',
        'user_id',
        // 'detail_jurnal_id' kita abaikan dulu untuk saat ini
    ];

    // Relasi: Satu transaksi dimiliki oleh satu akun Kas/Bank
    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(KasBank::class, 'kas_bank_id', 'kas_bank_id');
    }

    // Relasi: Satu transaksi dibuat oleh satu user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
