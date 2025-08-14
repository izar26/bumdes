<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;
    protected $table = 'pembelians';
    protected $primaryKey = 'pembelian_id';
    protected $fillable = [
        'pemasok_id',
        'no_faktur',
        'tanggal_pembelian',
        'nominal_pembelian',
        'jurnal_id',
        'unit_usaha_id',
        'status_pembelian',
    ];

    public function detailPembelians()
    {
        return $this->hasMany(DetailPembelian::class, 'pembelian_id', 'pembelian_id');
    }

    public function pemasok()
    {
        return $this->belongsTo(Pemasok::class, 'pemasok_id', 'pemasok_id');
    }

    public function unitUsaha()
    {
        return $this->belongsTo(UnitUsaha::class, 'unit_usaha_id', 'unit_usaha_id');
    }
}