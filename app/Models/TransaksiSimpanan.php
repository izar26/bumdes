<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiSimpanan extends Model
{
    use HasFactory;

    protected $primaryKey = 'transaksi_id';
    protected $guarded = [];

    /**
     * Casts (agar 'jumlah' dan 'saldo_setelah_transaksi' jadi angka).
     */
    protected $casts = [
        'tanggal_transaksi' => 'date',
        'jumlah' => 'integer',
        'saldo_setelah_transaksi' => 'integer',
    ];

    /**
     * Transaksi ini masuk ke rekening mana.
     */
    public function rekening()
    {
        return $this->belongsTo(RekeningSimpanan::class, 'rekening_id', 'rekening_id');
    }

    /**
     * Transaksi ini dicatat oleh admin siapa.
     */
    public function admin()
    {
        // Asumsi: Foreign 'user_id_admin' -> Primary 'user_id' di tabel 'users'
        return $this->belongsTo(User::class, 'user_id_admin', 'user_id');
    }
}
