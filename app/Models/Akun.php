<?php

// app/Models/Akun.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    use HasFactory;
    protected $table = 'akuns';
    protected $primaryKey = 'akun_id';
    protected $fillable = ['kode_akun', 'nama_akun', 'tipe_akun', 'is_header', 'parent_id'];
}
