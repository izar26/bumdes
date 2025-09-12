<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
       protected $table = 'tarifs';

    protected $fillable = [
        'jenis_tarif',
        'deskripsi',
        'batas_bawah',
        'batas_atas',
        'harga',
    ];
}
