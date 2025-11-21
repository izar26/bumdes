<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model placeholder for JenisPinjaman.
 *
 * This file previously contained JenisSimpanan class which caused a PSR-4
 * autoloading conflict because the filename does not match the declared class.
 * We provide a minimal JenisPinjaman model to match the filename.
 */
class JenisPinjaman extends Model
{
    use HasFactory;

    protected $primaryKey = 'jenis_pinjaman_id';
    protected $guarded = [];

    /**
     * (Optional) Relasi ke pengajuan pinjaman jika digunakan.
     */
    public function pengajuanPinjaman()
    {
        return $this->hasMany(PengajuanPinjaman::class, 'jenis_pinjaman_id', 'jenis_pinjaman_id');
    }
}
