<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pengukuran extends Model
{
    protected $table = 'pengukuran';

    public $timestamps = false;

    protected $fillable = [
        'balita_id',
        'kader_id',
        'tanggal_pengukuran',
        'berat_badan',
        'tinggi_badan',
        'lingkar_kepala',
        'lingkar_lengan_atas',
        'z_score',
        'status_pertumbuhan',
        'foto_pertumbuhan',
    ];

    public function balita()
    {
        return $this->belongsTo(Balita::class, 'balita_id');
    }

    public function verifikasi(): HasOne
    {
        return $this->hasOne(Verifikasi::class, 'pengukuran_id')->latestOfMany();
    }
}
