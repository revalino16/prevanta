<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KaderController extends Controller
{
    public function dashboard()
    {
        return view('kader.dashboard');
    }
}
