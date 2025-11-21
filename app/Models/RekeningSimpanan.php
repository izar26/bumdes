<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekeningSimpanan extends Model
{
    use HasFactory;

    protected $primaryKey = 'rekening_id';
    protected $guarded = [];

    /**
     * Rekening ini milik siapa.
     */
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'anggota_id', 'anggota_id');
    }

    /**
     * Jenis rekening ini apa (Pokok, Wajib, Sukarela).
     */
    public function jenisSimpanan()
    {
        return $this->belongsTo(JenisSimpanan::class, 'jenis_simpanan_id', 'jenis_simpanan_id');
    }

    /**
     * Satu rekening punya banyak histori transaksi.
     */
    public function transaksiSimpanan()
    {
        return $this->hasMany(TransaksiSimpanan::class, 'rekening_id', 'rekening_id');
    }
}
