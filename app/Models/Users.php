<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Users extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'email',
        'password',
        'no_hp',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function orangTua()
    {
        return $this->hasOne(OrangTua::class, 'users_id');
    }
}
