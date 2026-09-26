<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisVitamin extends Model
{
    protected $table = 'jenis_vitamin';

    public $timestamps = false;

    protected $fillable = [
        'nama_vitamin',
        'deskripsi',
    ];
}
