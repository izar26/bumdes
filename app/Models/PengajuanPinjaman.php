<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanPinjaman extends Model
{
    use HasFactory;

    protected $primaryKey = 'pinjaman_id';
    protected $table = 'pengajuan_pinjamans';
    protected $guarded = [];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_approval' => 'date',
        'tanggal_pencairan' => 'date',
        'jumlah_pinjaman' => 'integer',
        'jumlah_angsuran_per_bulan' => 'integer',
    ];

    /**
     * Pinjaman ini milik anggota siapa.
     */
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'anggota_id', 'anggota_id');
    }

    /**
     * Pinjaman ini disetujui admin siapa.
     */
    public function adminApprove()
    {
        return $this->belongsTo(User::class, 'user_id_admin_approve', 'user_id');
    }

    /**
     * Satu pinjaman punya banyak jadwal angsuran.
     */
    public function angsuran()
    {
        return $this->hasMany(AngsuranPinjaman::class, 'pinjaman_id', 'pinjaman_id');
    }
}
