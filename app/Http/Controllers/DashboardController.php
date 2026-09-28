<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\Pengukuran;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now('Asia/Jakarta');

        // Total balita terdaftar
        $totalBalita = Balita::count();

        // Balita diukur bulan ini (unique per balita)
        $hadirDitimbang = Pengukuran::whereMonth('tanggal_pengukuran', $now->month)
            ->whereYear('tanggal_pengukuran', $now->year)
            ->distinct('balita_id')
            ->count('balita_id');

        // Status terbaru per balita (DI BULAN INI)
        $latestIds = Pengukuran::whereMonth('tanggal_pengukuran', $now->month)
            ->whereYear('tanggal_pengukuran', $now->year)
            ->selectRaw('MAX(id) as id')
            ->groupBy('balita_id')
            ->pluck('id');

        $statusGroups = Pengukuran::whereIn('id', $latestIds)
            ->get()
            ->groupBy(fn ($p) => strtolower(trim($p->status_pertumbuhan ?? '')));

        $giziNormal = ($statusGroups->get('normal') ?? collect())->count();
        $pendek = ($statusGroups->get('pendek') ?? collect())->count();
        $sangatPendek = ($statusGroups->get('sangat pendek') ?? collect())->count();
        $giziKurang = ($statusGroups->get('gizi kurang') ?? collect())->count();

        // Prevalensi stunting (pendek + sangat pendek)
        $totalDiukur = $latestIds->count();
        $stuntingCount = $pendek + $sangatPendek;
        $prevalensi = $totalDiukur > 0
            ? round(($stuntingCount / $totalDiukur) * 100, 1)
            : 0;

        // Tren prevalensi stunting 6 bulan terakhir
        $trenLabels = [];
        $trenValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $tgl = $now->copy()->subMonths($i);
            $records = Pengukuran::whereMonth('tanggal_pengukuran', $tgl->month)
                ->whereYear('tanggal_pengukuran', $tgl->year)
                ->get();

            $total = $records->count();
            $stunting = $records->filter(
                fn ($p) => in_array(strtolower(trim($p->status_pertumbuhan ?? '')), ['pendek', 'sangat pendek'])
            )->count();

            $trenLabels[] = $tgl->isoFormat('MMM YYYY');
            $trenValues[] = $total > 0 ? round(($stunting / $total) * 100, 1) : 0;
        }

        // Statistik kelompok usia
        $semuaBalita = Balita::all();

        // Kelompok 0-23 bulan
        $group023 = $semuaBalita->filter(function ($b) use ($now) {
            return Carbon::parse($b->tanggal_lahir)->diffInMonths($now) < 24;
        });

        // Kelompok 24-59 bulan
        $group2459 = $semuaBalita->filter(function ($b) use ($now) {
            $age = Carbon::parse($b->tanggal_lahir)->diffInMonths($now);

            return $age >= 24 && $age <= 59;
        });

        // Helper: hitung status untuk satu kelompok balita
        $hitungStatus = function ($kelompok) use ($latestIds) {
            $ids = $kelompok->pluck('id');

            $normal = 0;
            $pendek = 0;
            $sangat = 0;

            $latestPeng = Pengukuran::whereIn('id', $latestIds)
                ->whereIn('balita_id', $ids)
                ->get();

            foreach ($latestPeng as $p) {
                $s = strtolower(trim($p->status_pertumbuhan ?? ''));
                if ($s === 'normal') {
                    $normal++;
                } elseif ($s === 'pendek') {
                    $pendek++;
                } elseif ($s === 'sangat pendek') {
                    $sangat++;
                }
            }

            return [
                'total' => $ids->count(),
                'normal' => $normal,
                'pendek' => $pendek,
                'sangat' => $sangat,
            ];
        };

        $stat023 = $hitungStatus($group023);
        $stat2459 = $hitungStatus($group2459);

        // Total diukur hari ini
        $totalDiukurHariIni = Pengukuran::whereDate('tanggal_pengukuran', $now->toDateString())
            ->distinct('balita_id')
            ->count('balita_id');

        return view('kader.dashboard', compact(
            'totalBalita',
            'hadirDitimbang',
            'giziNormal',
            'pendek',
            'sangatPendek',
            'giziKurang',
            'prevalensi',
            'stuntingCount',
            'totalDiukur',
            'trenLabels',
            'trenValues',
            'stat023',
            'stat2459',
            'totalDiukurHariIni',
        ));
    }
}
