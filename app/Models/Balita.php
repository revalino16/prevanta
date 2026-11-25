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
        'foto_profil',
    ];

    protected $appends = [
        'foto_profil_url',
        'inisial',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function getFotoProfilUrlAttribute(): string
    {
        if ($this->foto_profil) {
            if (str_starts_with($this->foto_profil, 'http://') || str_starts_with($this->foto_profil, 'https://')) {
                return $this->foto_profil;
            }
            return asset($this->foto_profil);
        }

        // Default avatar inisial Prevanta
        $initials = urlencode($this->inisial);
        return "https://ui-avatars.com/api/?name={$initials}&background=B02A5A&color=fff&size=128&bold=true";
    }

    public function getInisialAttribute(): string
    {
        $words = explode(' ', trim($this->nama ?? ''));
        $inisial = '';
        foreach ($words as $w) {
            if (!empty($w)) {
                $inisial .= mb_substr($w, 0, 1);
            }
            if (mb_strlen($inisial) >= 2) {
                break;
            }
        }
        return strtoupper($inisial ?: 'B');
    }

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
