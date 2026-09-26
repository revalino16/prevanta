<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImunisasiBalita extends Model
{
    protected $table = 'imunisasi_balita';

    public $timestamps = false;

    protected $fillable = [
        'balita_id',
        'jenis_imunisasi_id',
        'kader_id',
        'tanggal_pemberian',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pemberian' => 'date',
        ];
    }

    public function jenisImunisasi(): BelongsTo
    {
        return $this->belongsTo(JenisImunisasi::class, 'jenis_imunisasi_id');
    }

    public function kader(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'kader_id');
    }
}
