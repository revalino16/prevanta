<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisImunisasi extends Model
{
    protected $table = 'jenis_imunisasi';

    public $timestamps = false;

    protected $fillable = [
        'nama_imunisasi',
        'deskripsi',
    ];
}
