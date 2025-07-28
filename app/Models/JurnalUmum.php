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
    protected $fillable = ['tanggal_transaksi', 'deskripsi', 'user_id', 'bungdes_id', 'total_debit', 'total_kredit'];

    public function detailJurnals(): HasMany
    {
        return $this->hasMany(DetailJurnal::class, 'jurnal_id', 'jurnal_id');
    }
}
