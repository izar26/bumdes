<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsetBUMDes extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'aset_bumdes';
    
    // Primary key custom
    protected $primaryKey = 'aset_id';

    // Kolom yang bisa diisi secara massal
    protected $fillable = [
        'nomor_inventaris',
        'nama_aset',
        'jenis_aset',
        'nilai_perolehan',
        'tanggal_perolehan',
        'kondisi',
        'lokasi',
        'unit_usaha_id'
    ];

    // Konversi kolom tanggal_perolehan menjadi objek Carbon
    protected $dates = ['tanggal_perolehan'];

    /**
     * Mendefinisikan relasi "many-to-one" dengan UnitUsaha.
     * Satu Aset BUMDes hanya dimiliki oleh satu Unit Usaha.
     */
    public function unitUsaha(): BelongsTo
    {
        return $this->belongsTo(UnitUsaha::class, 'unit_usaha_id');
    }
}
