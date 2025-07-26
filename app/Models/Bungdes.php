<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bungdes extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bungdeses'; // Secara eksplisit definisikan nama tabel

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'bungdes_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer'; // Atau 'bigInteger' jika sesuai dengan migrasi Anda

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_bumdes',
        'alamat',
        'tanggal_berdiri',
        'deskripsi',
        'telepon',
        'struktur_organisasi',
        'logo',
        'aset_usaha',
        'email',
        // 'user_id' dihilangkan dari fillable
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_berdiri' => 'date',
    ];

    // Metode relasi user() telah dihapus
}
