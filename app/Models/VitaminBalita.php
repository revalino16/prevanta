<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitaminBalita extends Model
{
    protected $table = 'vitamin_balita';

    public $timestamps = false;

    protected $fillable = [
        'balita_id',
        'jenis_vitamin_id',
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

    public function jenisVitamin(): BelongsTo
    {
        return $this->belongsTo(JenisVitamin::class, 'jenis_vitamin_id');
    }

    public function kader(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'kader_id');
    }
}
