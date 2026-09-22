<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
