<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verifikasi extends Model
{
    protected $table = 'verifikasi';

    public $timestamps = false;

    protected $fillable = [
        'pengukuran_id',
        'bidan_id',
        'tanggal_verifikasi',
        'status',
        'catatan_penyuluhan',
        'tindak_lanjut',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_verifikasi' => 'date',
        ];
    }

    public function pengukuran(): BelongsTo
    {
        return $this->belongsTo(Pengukuran::class, 'pengukuran_id');
    }

    public function bidan(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'bidan_id');
    }
}
