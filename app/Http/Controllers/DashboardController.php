<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\Pengukuran;
use Carbon\Carbon;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;


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



public function exportTrendExcel()
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setTitle('Tren Prevalensi');

    // Header tabel
    $sheet->setCellValue('A1', 'Bulan');
    $sheet->setCellValue('B1', 'Prevalensi Stunting (%)');
    $sheet->setCellValue('C1', 'Target Nasional (%)');

    // Ambil data enam bulan terakhir
    $labels = [];
    $values = [];

    for ($i = 5; $i >= 0; $i--) {
        $bulan = Carbon::now('Asia/Jakarta')
            ->startOfMonth()
            ->subMonths($i);

        $awalBulan = $bulan->copy()->startOfMonth();
        $akhirBulan = $bulan->copy()->endOfMonth();

        $total = Pengukuran::whereBetween(
            'tanggal_pengukuran',
            [$awalBulan, $akhirBulan]
        )->count();

        $stunting = Pengukuran::whereBetween(
            'tanggal_pengukuran',
            [$awalBulan, $akhirBulan]
        )->whereIn('status_pertumbuhan', [
            'pendek',
            'sangat pendek',
        ])->count();

        $prevalensi = $total > 0
            ? round(($stunting / $total) * 100, 2)
            : 0;

        $labels[] = $bulan->translatedFormat('M Y');
        $values[] = $prevalensi;
    }

    // Masukkan data ke tabel Excel
    foreach ($labels as $index => $label) {
        $row = $index + 2;

        $sheet->setCellValue("A{$row}", $label);
        $sheet->setCellValue("B{$row}", $values[$index]);
        $sheet->setCellValue("C{$row}", 14);
    }

    // Atur tampilan tabel
    $sheet->getStyle('A1:C1')->getFont()->setBold(true);

    foreach (['A', 'B', 'C'] as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Nama seri grafik
    $seriesLabels = [
        new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "'Tren Prevalensi'!\$B\$1",
            null,
            1
        ),
        new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "'Tren Prevalensi'!\$C\$1",
            null,
            1
        ),
    ];

    // Label bulan pada sumbu horizontal
    $categories = [
        new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "'Tren Prevalensi'!\$A\$2:\$A\$7",
            null,
            6
        ),
    ];

    // Nilai untuk kedua garis
    $dataSeriesValues = [
        new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'Tren Prevalensi'!\$B\$2:\$B\$7",
            null,
            6
        ),
        new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'Tren Prevalensi'!\$C\$2:\$C\$7",
            null,
            6
        ),
    ];

    // Buat satu grafik garis dengan dua seri data
    $series = [
        new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            [0, 1],
            $seriesLabels,
            $categories,
            $dataSeriesValues
        ),
    ];

    $chart = new Chart(
        'trenPrevalensi',
        new Title('Tren Prevalensi Stunting'),
        new Legend(Legend::POSITION_BOTTOM),
        new PlotArea(null, $series)
    );

    $chart->setTopLeftPosition('E2');
    $chart->setBottomRightPosition('N18');

    // Tambahkan grafik asli ke worksheet
    $sheet->addChart($chart);

    // Ekspor file Excel beserta grafiknya
    $filename = 'tren_prevalensi_stunting.xlsx';

    $writer = new Xlsx($spreadsheet);
    $writer->setIncludeCharts(true);

    return response()->streamDownload(
        function () use ($writer) {
            $writer->save('php://output');
        },
        $filename,
        [
            'Content-Type' =>
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]
    );
}


}
