<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'jadwal';

    public $timestamps = false;

    protected $fillable = [
        'users_id',
        'jenis_kegiatan',
        'tanggal',
        'lokasi',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'users_id');
    }
}
