<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pengukuran;
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

    public function pengukuran()
    {
        return $this->hasMany(Pengukuran::class, 'balita_id');
    }
}
