<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\Pengukuran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KaderController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $namaKader = $user->nama ?? 'Bu Siti Rahayu';
        $roleName = $user->role === 'kader' ? 'Kader Utama' : ucfirst($user->role ?? 'Kader');

        $posyanduName = 'Posyandu Jambu 77';
        $poskoName = 'Posko Jambu 77';

        // Check if database has balita and pengukuran records
        $totalBalitaCount = Balita::count();
        $pengukuranRecords = Pengukuran::with('balita')->get();

        if ($totalBalitaCount > 0 && $pengukuranRecords->count() > 0) {
            $totalBalita = $totalBalitaCount;
            $hadirDitimbang = $pengukuranRecords->pluck('balita_id')->unique()->count();

            $giziBaik = $pengukuranRecords->filter(function ($item) {
                return str_contains(strtolower($item->status_pertumbuhan ?? ''), 'baik')
                    || str_contains(strtolower($item->status_pertumbuhan ?? ''), 'normal');
            })->count();

            $kondisiPendek = $pengukuranRecords->filter(function ($item) {
                $status = strtolower($item->status_pertumbuhan ?? '');
                return str_contains($status, 'pendek') && !str_contains($status, 'sangat');
            })->count();

            $sangatPendek = $pengukuranRecords->filter(function ($item) {
                return str_contains(strtolower($item->status_pertumbuhan ?? ''), 'sangat pendek');
            })->count();

            $totalStunting = $kondisiPendek + $sangatPendek;
            $prevalensiStunting = $hadirDitimbang > 0 ? round(($totalStunting / $hadirDitimbang) * 100, 1) : 0;

            // Group by age
            $balita0_23 = $pengukuranRecords->filter(function ($item) {
                if (!$item->balita || !$item->balita->tanggal_lahir) return false;
                $months = Carbon::parse($item->balita->tanggal_lahir)->diffInMonths(Carbon::now());
                return $months <= 23;
            });

            $balita24_59 = $pengukuranRecords->filter(function ($item) {
                if (!$item->balita || !$item->balita->tanggal_lahir) return false;
                $months = Carbon::parse($item->balita->tanggal_lahir)->diffInMonths(Carbon::now());
                return $months >= 24 && $months <= 59;
            });

            $group0_23_normal = $balita0_23->filter(fn($i) => str_contains(strtolower($i->status_pertumbuhan ?? ''), 'baik') || str_contains(strtolower($i->status_pertumbuhan ?? ''), 'normal'))->count();
            $group0_23_pendek = $balita0_23->filter(fn($i) => str_contains(strtolower($i->status_pertumbuhan ?? ''), 'pendek') && !str_contains(strtolower($i->status_pertumbuhan ?? ''), 'sangat'))->count();
            $group0_23_sangat_pendek = $balita0_23->filter(fn($i) => str_contains(strtolower($i->status_pertumbuhan ?? ''), 'sangat pendek'))->count();

            $group24_59_normal = $balita24_59->filter(fn($i) => str_contains(strtolower($i->status_pertumbuhan ?? ''), 'baik') || str_contains(strtolower($i->status_pertumbuhan ?? ''), 'normal'))->count();
            $group24_59_pendek = $balita24_59->filter(fn($i) => str_contains(strtolower($i->status_pertumbuhan ?? ''), 'pendek') && !str_contains(strtolower($i->status_pertumbuhan ?? ''), 'sangat'))->count();
            $group24_59_sangat_pendek = $balita24_59->filter(fn($i) => str_contains(strtolower($i->status_pertumbuhan ?? ''), 'sangat pendek'))->count();

            $ageStats = [
                'group_0_23' => [
                    'label' => '0 – 23 Bulan',
                    'badge' => '1000 HPK',
                    'normal' => $group0_23_normal,
                    'pendek' => $group0_23_pendek,
                    'sangat_pendek' => $group0_23_sangat_pendek,
                    'total' => $balita0_23->count(),
                ],
                'group_24_59' => [
                    'label' => '24 – 59 Bulan',
                    'badge' => null,
                    'normal' => $group24_59_normal,
                    'pendek' => $group24_59_pendek,
                    'sangat_pendek' => $group24_59_sangat_pendek,
                    'total' => $balita24_59->count(),
                ],
                'totals' => [
                    'normal' => $giziBaik,
                    'pendek' => $kondisiPendek,
                    'sangat_pendek' => $sangatPendek,
                    'total_diukur' => $hadirDitimbang,
                    'total_semua' => $totalBalita,
                ]
            ];
        } else {
            // Default design data matching screenshot mockup
            $totalBalita = 68;
            $hadirDitimbang = 42;
            $prevalensiStunting = 21.9;
            $giziBaik = 30;
            $kondisiPendek = 7;
            $sangatPendek = 5;

            $ageStats = [
                'group_0_23' => [
                    'label' => '0 – 23 Bulan',
                    'badge' => '1000 HPK',
                    'normal' => 18,
                    'pendek' => 4,
                    'sangat_pendek' => 2,
                    'total' => 24,
                ],
                'group_24_59' => [
                    'label' => '24 – 59 Bulan',
                    'badge' => null,
                    'normal' => 12,
                    'pendek' => 3,
                    'sangat_pendek' => 3,
                    'total' => 18,
                ],
                'totals' => [
                    'normal' => 30,
                    'pendek' => 7,
                    'sangat_pendek' => 5,
                    'total_diukur' => 42,
                    'total_semua' => 68,
                ]
            ];
        }

        // Longitudinal trend data (Mei - Oktober 2025)
        $trends = [
            ['bulan' => 'Mei 2025', 'rate' => 26.5],
            ['bulan' => 'Jun 2025', 'rate' => 25.2],
            ['bulan' => 'Jul 2025', 'rate' => 24.0],
            ['bulan' => 'Ags 2025', 'rate' => 23.1],
            ['bulan' => 'Sep 2025', 'rate' => 22.4],
            ['bulan' => 'Okt 2025 (Kini)', 'rate' => $prevalensiStunting, 'is_current' => true],
        ];

        return view('kader.dashboard', compact(
            'namaKader',
            'roleName',
            'posyanduName',
            'poskoName',
            'totalBalita',
            'hadirDitimbang',
            'prevalensiStunting',
            'giziBaik',
            'kondisiPendek',
            'sangatPendek',
            'ageStats',
            'trends'
        ));
    }
}
