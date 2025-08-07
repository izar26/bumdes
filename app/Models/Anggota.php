<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model ini.
     * Laravel akan secara otomatis mencari tabel 'anggotas' karena nama modelnya 'Anggota'.
     * Baris ini opsional, tetapi bagus untuk kejelasan.
     * @var string
     */
    protected $table = 'anggotas';

    /**
     * Nama kolom primary key pada tabel.
     * @var string
     */
    protected $primaryKey = 'anggota_id';

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     * @var array
     */
    protected $fillable = [
        'nama_lengkap',
        'nik',
        'alamat',
        'no_telepon',
        'tanggal_daftar',
        'unit_usaha_id',
        'status_anggota',
        'jenis_kelamin',
        'foto',
        'email',
        'jabatan',
        'user_id',
    ];

    /**
     * Atribut yang harus di-casting.
     * Ini akan mengubah kolom 'tanggal_daftar' menjadi objek Carbon.
     * @var array
     */
    protected $casts = [
        'tanggal_daftar' => 'date',
    ];

    /**
     * Mendefinisikan relasi "many-to-one" ke model User.
     * Setiap anggota dimiliki oleh satu user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Mendefinisikan relasi "many-to-one" ke model UnitUsaha.
     * Setiap anggota terkait dengan satu unit usaha.
     */
    public function unitUsaha()
    {
        return $this->belongsTo(UnitUsaha::class, 'unit_usaha_id', 'unit_usaha_id');
    }
}
