<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AngsuranPinjaman extends Model
{
    use HasFactory;

    protected $primaryKey = 'angsuran_id';
    protected $guarded = [];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar' => 'date',
        'jumlah_bayar' => 'integer',
    ];

    /**
     * Angsuran ini bagian dari pinjaman mana.
     */
    public function pinjaman()
    {
        return $this->belongsTo(PengajuanPinjaman::class, 'pinjaman_id', 'pinjaman_id');
    }

    /**
     * Pembayaran angsuran ini diterima admin siapa.
     */
    public function adminTerima()
    {
        return $this->belongsTo(User::class, 'user_id_admin_terima', 'user_id');
    }
}
