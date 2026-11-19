<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrangTua extends Model
{
    protected $table = 'orang_tua';

    public $timestamps = false;

    protected $fillable = [
        'users_id',
        'nik',
        'hubungan_dengan_balita',
        'jenis_kelamin',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'users_id');
    }

    public function balita()
    {
        return $this->hasMany(Balita::class, 'orang_tua_id');
    }
}
