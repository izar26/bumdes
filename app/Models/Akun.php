<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    use HasFactory;

    protected $primaryKey = 'akun_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe_akun',
        'is_header',
        'parent_id',
    ];

    protected $casts = [
        'is_header' => 'boolean',
    ];

    // Relasi ke parent (akun induk)
    public function parent()
    {
        return $this->belongsTo(Akun::class, 'parent_id', 'akun_id');
    }

    // Relasi ke children (akun anak/sub-akun)
    public function children()
    {
        return $this->hasMany(Akun::class, 'parent_id', 'akun_id');
    }

    public static function topLevelAccounts()
    {
        return static::whereNull('parent_id')->orderBy('kode_akun')->get();
    }
 public static function getTipeAkunOptions()
    {
        return [
            'assets' => 'Aset',
            'liabilities' => 'Liabilitas',
            'equity' => 'Ekuitas',
            'revenue' => 'Pendapatan',
            'expenses' => 'Beban',
        ];
    }
}
