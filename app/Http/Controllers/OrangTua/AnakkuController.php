<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AnakkuController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil data orang tua berdasarkan user yang sedang login
        $orangTua = $user->orangTua;

        // Ambil semua balita milik orang tua tersebut
        $anakList = $orangTua
            ? $orangTua->balita()->with([
                'pengukuran' => function ($query) {
                    $query->whereHas('verifikasi', function ($q) {
                        $q->where('status', 'terverifikasi');
                    })->latest('tanggal_pengukuran');
                },
            ])->get()
            : collect();

        // Ambil anak pertama sebagai anak utama
        $anakUtama = $anakList->first();

        // Ambil pengukuran terakhir masing-masing anak
        foreach ($anakList as $anak) {
            $anak->pengukuranTerakhir = $anak->pengukuran->first();
        }

        $posyandu = null;
        $notifCount = 0;

        $tanggalHariIni = Carbon::now()
            ->translatedFormat('l, d F Y');

        $periodeSiklus = null;

        $sapaanKeluarga = $user->nama ?? 'Bunda & Ayah';

        // Untuk sementara agenda masih kosong
        $agendaList = collect();

        return view('orangtua.anakku', compact(
            'orangTua',
            'posyandu',
            'notifCount',
            'tanggalHariIni',
            'periodeSiklus',
            'agendaList',
            'anakList',
            'anakUtama',
            'sapaanKeluarga'
        ));
    }

    public function riwayat()
    {
        return view('orangtua.riwayat');
    }

    public function tindakLanjut($anak)
    {
        return view('orangtua.tindak-lanjut', compact('anak'));
    }
}
