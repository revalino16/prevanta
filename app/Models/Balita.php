<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Balita extends Model
{
    protected $table = 'balita';

    public $timestamps = false;

    protected $fillable = [
        'orang_tua_id',
        'nama',
        'nik',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
    ];

    public function orangTua()
    {
        return $this->belongsTo(
            OrangTua::class,
            'orang_tua_id'
        );
    }

    public function pengukuran()
    {
        return $this->hasMany(
            Pengukuran::class,
            'balita_id'
        );
    }

    public function imunisasi(): HasMany
    {
        return $this->hasMany(ImunisasiBalita::class, 'balita_id');
    }

    public function vitamin(): HasMany
    {
        return $this->hasMany(VitaminBalita::class, 'balita_id');
    }
}
