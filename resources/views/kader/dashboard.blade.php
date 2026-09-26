@extends('kader.layouts.kader')

@section('title', 'Dashboard Kader — Prevanta')

@push('styles')
    @vite('resources/css/kader/dashboard.css')
@endpush

@section('content')

<main class="content">

    {{-- ══ HERO BANNER ══════════════════════════════════════════ --}}
    <div class="hero-banner">

        <div class="hero-tag">
            <i class="fa-solid fa-circle" style="color:rgba(255,255,255,.6); font-size:7px;"></i>
            POSYANDU MAWAR MELATI &bull; RW 03
        </div>

        <div class="hero-title">
            Selamat Datang, Kader
            @auth
                Ibu {{ Auth::user()->nama }}!
            @else
                Kader Posyandu!
            @endauth
        </div>

        <div class="hero-subtitle">
            Pantau dan kelola pencatatan antropometri serta status gizi balita hari ini
            secara terpadu, presisi, dan tepat sasaran.
        </div>

    </div>


    {{-- ══ STAT CARDS ═══════════════════════════════════════════ --}}
    <div class="stat-grid">

        {{-- Total Balita --}}
        <div class="stat-card">
            <div class="stat-head">
                <div class="stat-label">Total Balita</div>
                <div class="stat-icon wine">
                    <i class="fa-solid fa-children"></i>
                </div>
            </div>
            <div>
                <div class="stat-value">{{ $totalBalita }}</div>
                <div class="stat-sub">Jiwa terdaftar</div>
            </div>
        </div>

        {{-- Hadir Ditimbang --}}
        <div class="stat-card">
            <div class="stat-head">
                <div class="stat-label">Hadir Ditimbang</div>
                <div class="stat-icon blue">
                    <i class="fa-solid fa-weight-scale"></i>
                </div>
            </div>
            <div>
                <div class="stat-value">{{ $hadirDitimbang }}</div>
                <div class="stat-sub">/ {{ $totalBalita }} Balita bulan ini</div>
            </div>
        </div>

        {{-- Prevalensi Stunting --}}
        <div class="stat-card">
            <div class="stat-head">
                <div class="stat-label">Prevalensi Stunting</div>
                <div class="stat-icon red">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <div>
                <div class="stat-value highlight">{{ $prevalensi }}%</div>
                <div class="stat-sub">dari balita terukur</div>
            </div>
        </div>

        {{-- Gizi Baik --}}
        <div class="stat-card">
            <div class="stat-head">
                <div class="stat-label">Gizi Baik (Normal)</div>
                <div class="stat-icon green">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div>
                <div class="stat-value">{{ $giziNormal }}</div>
                <div class="stat-sub">Balita</div>
            </div>
        </div>

        {{-- Kondisi Pendek --}}
        <div class="stat-card">
            <div class="stat-head">
                <div class="stat-label">Kondisi Pendek</div>
                <div class="stat-icon amber">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div>
                <div class="stat-value">{{ $pendek }}</div>
                <div class="stat-sub">Balita</div>
            </div>
        </div>

        {{-- Sangat Pendek --}}
        <div class="stat-card">
            <div class="stat-head">
                <div class="stat-label">Sangat Pendek</div>
                <div class="stat-icon red">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
            </div>
            <div>
                <div class="stat-value">{{ $sangatPendek }}</div>
                <div class="stat-sub">Balita</div>
            </div>
        </div>

    </div>


    {{-- ══ TREN CHART ════════════════════════════════════════════ --}}
    <div class="dash-card">

        <div class="dash-card-head">
            <div>
                <div class="dash-card-eyebrow">
                    <i class="fa-solid fa-shield-halved"></i>
                    ANALISIS LONGITUDINAL POSYANDU
                </div>
                <div class="dash-card-title">Tren Prevalensi Stunting Bulanan</div>
                <div class="dash-card-sub">
                    Monitoring pergerakan persentase balita kategori stunting (TB/U &lt; &minus;2 SD) di Posyandu Mawar Melati
                </div>
            </div>
            <button class="btn-export" type="button">
                <i class="fa-solid fa-download"></i>
                Ekspor Data
            </button>
        </div>

        <div class="chart-body">

            {{-- Evaluasi kiri --}}
            <div class="chart-eval">

                <div class="chart-eval-title">
                    <i class="fa-solid fa-shield-halved"></i>
                    EVALUASI KINERJA 6 BULAN
                </div>

                @php
                    $firstVal = $trenValues[0] ?? 0;
                    $lastVal  = $trenValues[count($trenValues) - 1] ?? 0;
                    $trendDiff = round($firstVal - $lastVal, 1);
                @endphp

                @if ($trendDiff > 0)
                    <div class="trend-badge good">
                        <i class="fa-solid fa-arrow-trend-down"></i>
                        Tren Membaik
                    </div>
                @elseif ($trendDiff < 0)
                    <div class="trend-badge bad">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        Tren Memburuk
                    </div>
                @else
                    <div class="trend-badge neutral">
                        <i class="fa-solid fa-minus"></i>
                        Tren Stabil
                    </div>
                @endif

                <div class="chart-eval-desc">
                    @if ($trendDiff > 0)
                        Prevalensi turun konsisten dari
                        <strong>{{ $firstVal }}%</strong>
                        ({{ $trenLabels[0] ?? '-' }}) menjadi
                        <strong>{{ $lastVal }}%</strong>
                        ({{ $trenLabels[count($trenLabels)-1] ?? '-' }}).
                    @elseif ($trendDiff < 0)
                        Prevalensi naik dari
                        <strong>{{ $firstVal }}%</strong>
                        ({{ $trenLabels[0] ?? '-' }}) menjadi
                        <strong>{{ $lastVal }}%</strong>
                        ({{ $trenLabels[count($trenLabels)-1] ?? '-' }}).
                        Perlu tindakan segera.
                    @else
                        Prevalensi tetap stabil di angka
                        <strong>{{ $lastVal }}%</strong>
                        selama 6 bulan terakhir.
                    @endif
                </div>

                <div class="chart-verified">
                    <i class="fa-solid fa-circle-check"></i>
                    Data terverifikasi per {{ now()->isoFormat('D MMM YYYY') }}
                </div>

            </div>

            {{-- Chart kanan --}}
            <div class="chart-wrap">
                <canvas id="trendChart"></canvas>
            </div>

        </div>

    </div>


    {{-- ══ TABEL KELOMPOK USIA ═══════════════════════════════════ --}}
    <div class="dash-card">

        <div class="dash-card-head">
            <div>
                <div class="dash-card-title" style="display:flex;align-items:center;gap:10px;">
                    <i class="fa-solid fa-people-group" style="color:var(--wine);"></i>
                    Statistik Berdasarkan Kelompok Usia
                </div>
                <div class="dash-card-sub">
                    Klasifikasi status gizi antropometri menurut batas baku TB/U WHO &amp; Permenkes
                </div>
            </div>
            <span style="font-size:12px;font-weight:800;color:var(--wine-deep);background:var(--wine-tint);border:1px solid var(--wine-tint-line);padding:6px 14px;border-radius:999px;">
                Posko RW 03
            </span>
        </div>

        @php
            $totalNormal = $stat023['normal'] + $stat2459['normal'];
            $totalPendek = $stat023['pendek'] + $stat2459['pendek'];
            $totalSangat = $stat023['sangat']  + $stat2459['sangat'];
            $totalAll    = $stat023['total']   + $stat2459['total'];
        @endphp

        <div class="usia-table-wrap">
            <table class="usia-table">
                <thead>
                    <tr>
                        <th>KELOMPOK USIA</th>
                        <th class="col-normal">NORMAL (GIZI BAIK)</th>
                        <th class="col-pendek">PENDEK</th>
                        <th class="col-sangat">SANGAT PENDEK</th>
                        <th class="col-total">TOTAL BALITA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="row-label">
                                <span class="row-dot wine"></span>
                                0 – 23 Bulan
                                <span class="hpk-chip">1000 HPK</span>
                            </div>
                        </td>
                        <td class="td-normal">{{ $stat023['normal'] }}</td>
                        <td class="td-pendek">{{ $stat023['pendek'] }}</td>
                        <td class="td-sangat">{{ $stat023['sangat'] }}</td>
                        <td class="td-total">{{ $stat023['total'] }}</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="row-label">
                                <span class="row-dot green"></span>
                                24 – 59 Bulan
                            </div>
                        </td>
                        <td class="td-normal">{{ $stat2459['normal'] }}</td>
                        <td class="td-pendek">{{ $stat2459['pendek'] }}</td>
                        <td class="td-sangat">{{ $stat2459['sangat'] }}</td>
                        <td class="td-total">{{ $stat2459['total'] }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td><strong>TOTAL DIUKUR HARI INI</strong></td>
                        <td class="td-normal">{{ $totalNormal }} Anak</td>
                        <td class="td-pendek">{{ $totalPendek }} Anak</td>
                        <td class="td-sangat">{{ $totalSangat }} Anak</td>
                        <td class="td-total">{{ $totalDiukurHariIni }} / {{ $totalBalita }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($stat023['sangat'] > 0 || $stat023['pendek'] > 0)
            <div class="alert-hpk">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>
                    <strong>Fokus Periode Emas (1000 HPK):</strong>
                    Terdapat {{ $stat023['sangat'] + $stat023['pendek'] }} balita usia di bawah 2 tahun
                    dengan status indikasi stunting yang membutuhkan pendampingan konseling
                    ASI eksklusif &amp; PMT Protein Hewani harian.
                </span>
            </div>
        @endif

    </div>


    {{-- ══ FOOTER ════════════════════════════════════════════════ --}}
    <div class="dash-footer">
        <span>
            &copy; {{ date('Y') }} Prevanta &mdash; Sistem Integrasi Pencatatan Stunting Posyandu Generasi Emas Indonesia.
        </span>
        <span>Versi Sistem Kader 2.4 &bull; Desa Sukamaju</span>
    </div>

</main>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = @json($trenLabels);
    const data   = @json($trenValues);

    const ctx = document.getElementById('trendChart');
    if (!ctx) return;

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(200, 90, 122, 0.18)');
    gradient.addColorStop(1, 'rgba(200, 90, 122, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Prevalensi Stunting (%)',
                    data,
                    borderColor: '#C85A7A',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    pointRadius: 5,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#C85A7A',
                    pointBorderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                },
                {
                    label: 'Target Nasional (14%)',
                    data: Array(labels.length).fill(14),
                    borderColor: '#2FA36B',
                    borderWidth: 1.5,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { family: 'Inter', size: 11, weight: '700' },
                        color: '#6B6259',
                        boxWidth: 14,
                        padding: 16,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y}%`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Inter', size: 11, weight: '700' },
                        color: '#9C9289',
                    },
                },
                y: {
                    min: 0,
                    ticks: {
                        callback: v => v + '%',
                        font: { family: 'Inter', size: 11 },
                        color: '#9C9289',
                        stepSize: 5,
                    },
                    grid: { color: '#ECE3D8' },
                },
            },
        },
    });
})();
</script>
@endpush
