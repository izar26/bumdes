<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsetBUMDes extends Model
{
    use HasFactory;

    protected $table = 'aset_bumdes';

    protected $primaryKey = 'aset_id';

    /**
     */
    protected $fillable = [
        'nomor_inventaris',
        'nama_aset',
        'jenis_aset',
        'nilai_perolehan',
        'tanggal_perolehan',
        'kondisi',
        'lokasi',
        'unit_usaha_id',
        'metode_penyusutan', // <-- Tambahkan ini
        'masa_manfaat',      // <-- Tambahkan ini
        'nilai_residu',      // <-- Tambahkan ini
        'nilai_saat_ini',    // <-- Tambahkan ini
    ];

protected $casts = [
    'tanggal_perolehan' => 'date',
    'nilai_perolehan'   => 'integer', // Ganti dari 'float' ke 'integer'
    'nilai_residu'      => 'integer', // Ganti dari 'float' ke 'integer'
    'nilai_saat_ini'    => 'integer', // Ganti juga ini ke 'integer'
    'masa_manfaat'      => 'integer',
];

    public function unitUsaha(): BelongsTo
    {
        return $this->belongsTo(UnitUsaha::class, 'unit_usaha_id', 'unit_usaha_id');
    }
}
