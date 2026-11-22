<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrangTua extends Model
{
    protected $table = 'orang_tua';

    public $timestamps = false;

    protected $fillable = [
        'users_id',
        'nik',
        'hubungan_dengan_balita',
        'jenis_kelamin',
        'foto_profil',
    ];

    protected $appends = [
        'foto_profil_url',
    ];

    /**
     * Mendapatkan URL publik foto profil orang tua jika tersedia.
     */
    protected function fotoProfilUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (empty($this->foto_profil)) {
                    return null;
                }

                $filename = basename(ltrim($this->foto_profil, '/'));

                if ($filename === '') {
                    return null;
                }

                return route('api.media.profil-orangtua', [
                    'filename' => $filename,
                ]);
            },
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'users_id');
    }

    public function balita(): HasMany
    {
        return $this->hasMany(Balita::class, 'orang_tua_id');
    }
}
