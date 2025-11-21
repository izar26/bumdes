<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisSimpanan extends Model
{
    use HasFactory;

    protected $primaryKey = 'jenis_simpanan_id';
    protected $guarded = [];

    /**
     * Satu jenis simpanan dimiliki oleh banyak rekening.
     */
    public function rekeningSimpanan()
    {
        return $this->hasMany(RekeningSimpanan::class, 'jenis_simpanan_id', 'jenis_simpanan_id');
    }
}
