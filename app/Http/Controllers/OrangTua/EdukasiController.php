<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\Edukasi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EdukasiController extends Controller
{
    public function index(Request $request): View
    {
        $kategoriList = [
            'Semua Topik',
            'Gizi & MP-ASI',
            'Imunisasi & Pencegahan',
            'Stimulasi & Tumbuh Kembang',
            'Kebutuhan Khusus / GTM',
            'Kesehatan & Sanitasi',
        ];

        $query = Edukasi::query();

        $selectedKategori = $request->get('kategori', 'Semua Topik');

        if ($selectedKategori !== 'Semua Topik') {
            $query->where('kategori', $selectedKategori);
        }

        if ($request->filled('q')) {
            $q = $request->q;

            $query->where(function ($sub) use ($q) {
                $sub->where('judul', 'like', "%{$q}%")
                    ->orWhere('konten', 'like', "%{$q}%")
                    ->orWhere('kategori', 'like', "%{$q}%");
            });
        }

        $edukasiList = $query->orderByDesc('id')->get();

        return view('orangtua.edukasi', compact(
            'edukasiList',
            'kategoriList',
            'selectedKategori'
        ));
    }
}
