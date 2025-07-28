<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;
    protected $table = 'penjualans';
    protected $primaryKey = 'penjualan_id';
    protected $fillable = [
        'no_invoice',
        'tanggal_penjualan',
        'total_penjualan',
        'jurnal_id',
        'unit_usaha_id',
        'nama_pelanggan',
        'status_penjualan',
    ];

    public function detailPenjualans()
    {
        return $this->hasMany(DetailPenjualan::class, 'penjualan_id', 'penjualan_id');
    }
}