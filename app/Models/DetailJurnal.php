<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailJurnal extends Model
{
    use HasFactory;
    protected $table = 'detail_jurnals';
    protected $primaryKey = 'detail_jurnal_id';
    public $timestamps = false; // Tabel ini tidak punya created_at/updated_at
    protected $fillable = ['jurnal_id', 'akun_id', 'debit', 'kredit', 'keterangan'];

    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_id', 'akun_id');
    }

    /**
     * ==========================================================
     * TAMBAHKAN METHOD INI
     * Mendefinisikan bahwa satu DetailJurnal dimiliki oleh satu JurnalUmum
     * ==========================================================
     */
    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(JurnalUmum::class, 'jurnal_id', 'jurnal_id');
    }
}