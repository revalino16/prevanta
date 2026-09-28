<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AnakkuController extends Controller
{
    public function index(): View
    {
        return view('orangtua.anakku');
    }
}
