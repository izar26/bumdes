<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisSimpanan extends Model
{
    use HasFactory;
        protected $table = 'jenis_simpanans';

    protected $primaryKey = 'jenis_simpanan_id';
    protected $guarded = [];

    /**
     * Satu jenis simpanan dimiliki oleh banyak rekening.
     */
    public function rekeningSimpanan()
    {
        return $this->hasMany(RekeningSimpanan::class, 'jenis_simpanan_id', 'jenis_simpanan_id');
    }
    public function pengajuanPinjaman()
    {
        return $this->hasMany(PengajuanPinjaman::class, 'jenis_pinjaman_id', 'jenis_pinjaman_id');
    }
}
