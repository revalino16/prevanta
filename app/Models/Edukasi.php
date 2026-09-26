<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Edukasi extends Model
{
    protected $table = 'edukasi';

    public $timestamps = false;

    protected $fillable = [
        'users_id',
        'judul',
        'konten',
        'gambar',
        'kategori',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'users_id');
    }
}
