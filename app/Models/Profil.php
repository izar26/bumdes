<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profil extends Model
{
    protected $fillable = [
    'deskripsi', 'jumlah_penduduk', 'jumlah_kk', 'luas_wilayah', 
    'alamat', 'email', 'telepon','nama_desa', 'logo'
];
}
