<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bungdes extends Model
{
    use HasFactory;

    protected $table = 'bungdeses';
    protected $primaryKey = 'bungdes_id'; // pastikan di migration kamu pakai id('bungdes_id')

    protected $fillable = [
        'nama_bungdes',
        'alamat',
        'tanggal_berdiri',
        'deskripsi',
        'telepon',
        'email',
        'user_id',
    ];

    // Relasi ke unit usaha
    public function unitUsahas()
    {
        return $this->hasMany(UnitUsaha::class, 'bungdes_id', 'bungdes_id');
    }

    // (Opsional) Jika Bungdes dimiliki oleh user tertentu
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
