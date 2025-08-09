<?php

// app/Models/JurnalUmum.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JurnalUmum extends Model
{
    use HasFactory;
    protected $table = 'jurnal_umums';
    protected $primaryKey = 'jurnal_id';
    protected $fillable = [
    'user_id',
    'unit_usaha_id',
    'tanggal_transaksi',
    'deskripsi',
    'total_debit',
    'total_kredit',
    'status',
    'approved_at',
    'approved_by',
    'rejected_reason',
];


    public function detailJurnals(): HasMany
    {
        return $this->hasMany(DetailJurnal::class, 'jurnal_id', 'jurnal_id');
    }

    public function unitUsaha()
{
    return $this->belongsTo(\App\Models\UnitUsaha::class, 'unit_usaha_id', 'unit_usaha_id');
}

public function user()
{
    return $this->belongsTo(\App\Models\User::class, 'user_id', 'user_id');
}

}
