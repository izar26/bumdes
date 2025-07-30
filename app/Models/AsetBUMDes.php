<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsetBUMDes extends Model
{
    use HasFactory;

    protected $table = 'asets'; // Diubah: Nama tabel menjadi 'asets'
    protected $primaryKey = 'aset_id';

    protected $fillable = [
        'nama_aset',
        'jenis_aset',
        'nilai_perolehan',
        'tanggal_perolehan',
        'kondisi',
        'lokasi',
        'unit_usaha_id',
        'penanggung_jawab',
    ];

    protected $casts = [
        'tanggal_perolehan' => 'date',
        'nilai_perolehan' => 'decimal:2',
    ];


    public function unitUsaha()
    {
        return $this->belongsTo(UnitUsaha::class, 'unit_usaha_id', 'unit_usaha_id');
    }

    public function penanggungJawabUser()
    {
        return $this->belongsTo(User::class, 'penanggung_jawab', 'id'); // Diubah: merujuk ke 'id'
    }
}
