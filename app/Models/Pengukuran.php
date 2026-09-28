<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'posisi_pengukuran',
        'lingkar_kepala',
        'lingkar_lengan_atas',
        'z_score',
        'status_pertumbuhan',
        'foto_pertumbuhan',
    ];

    protected function casts(): array
    {
        return [
            'berat_badan' => 'decimal:2',
            'tinggi_badan' => 'decimal:2',
            'lingkar_kepala' => 'decimal:2',
            'lingkar_lengan_atas' => 'decimal:2',
            'z_score' => 'decimal:2',
        ];
    }

    public function balita(): BelongsTo
    {
        return $this->belongsTo(Balita::class, 'balita_id');
    }

    public function verifikasi(): HasOne
    {
        return $this->hasOne(Verifikasi::class, 'pengukuran_id')->latestOfMany();
    }
}
