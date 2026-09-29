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

    // Ambil data orang tua dari user yang sedang login
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
    if (!$orangTua) {
        abort(404, 'Data orang tua tidak ditemukan.');
    }

    // Ambil semua anak milik orang tua yang sedang login
    $anakList = $orangTua->balita;

    $posyandu = $orangTua->posyandu ?? null;

    $notifCount = 0;

    $tanggalHariIni = Carbon::now()->translatedFormat('l, d F Y');

    $periodeSiklus = null;

    $agendaList = [];

    // Ambil anak pertama sebagai anak utama
    $anakUtama = $anakList->first();

    $sapaanKeluarga = $orangTua->nama ?? 'Bunda & Ayah';

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

    public function riwayat($anak = null)
{
    $user = Auth::user();

    // Ambil data orang tua berdasarkan user yang sedang login
    $orangTua = $user->orangTua;

    if (!$orangTua) {
        abort(404, 'Data orang tua tidak ditemukan.');
    }

    // Ambil semua anak milik orang tua yang sedang login
    $daftarAnak = $orangTua->balita;

    // Kalau tidak memiliki anak
    if ($daftarAnak->isEmpty()) {
        return view('orangtua.riwayat', [
            'anak' => null,
            'daftarAnak' => $daftarAnak,
            'riwayat' => collect(),
            'imunisasi' => collect(),
            'notifCount' => 0,
        ]);
    }

    // Kalau ada ID anak dari URL, pilih anak tersebut
    // Kalau tidak ada, pilih anak pertama
    $anakTerpilih = $anak
        ? $daftarAnak->firstWhere('id', $anak)
        : $daftarAnak->first();

    // Pastikan anak memang milik orang tua yang sedang login
    if (!$anakTerpilih) {
        abort(404, 'Data anak tidak ditemukan.');
    }

    // Ambil riwayat pengukuran
    $riwayat = $anakTerpilih->pengukuran()
        ->orderByDesc('tanggal_pengukuran')
        ->get();

    // Sementara imunisasi kosong
    $imunisasi = collect();

    $notifCount = 0;

    return view('orangtua.riwayat', [
        'anak' => $anakTerpilih,
        'daftarAnak' => $daftarAnak,
        'riwayat' => $riwayat,
        'imunisasi' => $imunisasi,
        'notifCount' => $notifCount,
    ]);
}

    public function tindakLanjut($anak)
    {
        return view('orangtua.tindak-lanjut', compact('anak'));
    }
}
