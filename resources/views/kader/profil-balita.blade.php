@extends('kader.layouts.kader')

@section('title', 'Profil ' . $balita->nama . ' — Prevanta')

@section('content')

<main class="content kms-page">
    @if (session('success'))
        <div class="kms-success-alert" role="status">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="kms-heading">
        <div>
            <a href="{{ route('kader.monitoringbalita') }}" class="kms-back-link">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Monitoring Balita
            </a>

            <h1>Profil Anak</h1>
            <p>Informasi dan perkembangan tumbuh kembang anak</p>
        </div>

        @if (Auth::user()->role === 'kader')
            <a
                href="{{ route('kader.balita.pengukuran.create', $balita) }}"
                class="kms-record-button"
            >
                <i class="fa-regular fa-circle-plus"></i>
                Catat Pengukuran Hari Ini
            </a>
        @endif
    </div>

    <section class="child-profile-card" aria-labelledby="child-name">
        <div class="child-photo-wrap">
            <img
                src="{{ asset($balita->jenis_kelamin === 'L' ? 'images/cowo.jpg' : 'images/cewe.jpg') }}"
                alt="Foto {{ $balita->nama }}"
                class="child-photo"
            >
            <span class="child-gender child-gender-{{ strtolower($balita->jenis_kelamin) }}">
                <i class="fa-solid {{ $balita->jenis_kelamin === 'L' ? 'fa-mars' : 'fa-venus' }}"></i>
                {{ $balita->jenis_kelamin }}
            </span>
        </div>

        <div class="child-profile-content">
            <h2 id="child-name">{{ $balita->nama }}</h2>

            <div class="child-details-grid">
                <div class="child-detail">
                    <span class="child-detail-label">Nomor Induk Kependudukan (NIK)</span>
                    <strong>{{ $balita->nik }}</strong>
                </div>

                <div class="child-detail">
                    <span class="child-detail-label">Tanggal Lahir &amp; Usia</span>
                    <strong>{{ $birthDateLabel }} <span>({{ $currentAgeLabel }})</span></strong>
                </div>

                <div class="child-detail child-detail-address">
                    <span class="child-detail-label">Wilayah Domisili</span>
                    <strong>{{ $balita->alamat ?: 'Belum tersedia' }}</strong>
                </div>

                <div class="child-detail">
                    <span class="child-detail-label">ID Balita</span>
                    <strong>{{ $childCode }}</strong>
                </div>

                <div class="child-detail">
                    <span class="child-detail-label">Orang Tua / Wali Balita</span>
                    <strong>{{ $balita->orangTua?->user?->nama ?? 'Belum tersedia' }}</strong>
                </div>

                <div class="child-detail child-status-detail">
                    @if ($latestMeasurement)
                        <span class="growth-status growth-status-{{ $latestMeasurement['tone'] }}">
                            <i class="fa-solid {{ $latestMeasurement['tone'] === 'danger' ? 'fa-triangle-exclamation' : 'fa-chart-line' }}"></i>
                            {{ $latestMeasurement['status'] }}
                            @if ($latestMeasurement['zScore'] !== '—')
                                ({{ $latestMeasurement['zScore'] }} SD)
                            @endif
                        </span>
                    @else
                        <span class="growth-status growth-status-neutral">
                            <i class="fa-regular fa-clock"></i>
                            Belum ada pengukuran
                        </span>
                    @endif
                </div>
            </div>

            <div class="follow-up follow-up-{{ $latestMeasurement['tone'] ?? 'neutral' }}">
                <i class="fa-regular fa-circle-exclamation"></i>
                <strong>Status Tindak Lanjut:</strong>
                <span>{{ $latestMeasurement['followUp'] ?? 'Lakukan pengukuran pertama untuk memulai pemantauan' }}</span>
            </div>
        </div>
    </section>

    <section class="counseling-card" aria-labelledby="counseling-title" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <div class="counseling-card-head" style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 16px; border-bottom: 1px solid #e5e7eb; padding-bottom: 16px;">
            <span class="counseling-icon" style="color: #be185d; font-size: 1.25rem;">
                <i class="fa-solid fa-user-nurse"></i>
            </span>
            <div>
                <h2 id="counseling-title" style="margin: 0; font-size: 1.125rem; font-weight: 700; color: #111827;">Catatan Penyuluhan &amp; Pendampingan Balita</h2>
                <p style="margin: 4px 0 0; font-size: 0.875rem; color: #6b7280;">Log konseling gizi, kepatuhan PMT, dan catatan pemantauan berkala oleh Bidan Desa dan Kader Posyandu.</p>
            </div>
        </div>

        <div class="counseling-content" style="background: #f9fafb; border-radius: 8px; padding: 16px; border: 1px solid #e5e7eb;">
            @if (!empty($latestMeasurement['counselingNote']))
                <p class="counseling-note-text" style="margin: 0 0 16px; font-size: 0.95rem; color: #374151; line-height: 1.6;">
                    {{ $latestMeasurement['counselingNote'] }}
                </p>
                
                <div class="counseling-meta" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #d1d5db; padding-top: 12px; font-size: 0.8125rem; color: #4b5563;">
                    <div class="counseling-authors">
                        <span>Konselor: <strong>{{ $latestMeasurement['counselor'] ?? 'Bidan Desa' }}</strong></span>
                        <span style="margin: 0 8px;">&bull;</span>
                        <span>Pendamping: <strong>{{ $latestMeasurement['companion'] ?? 'Kader Posyandu' }}</strong></span>
                    </div>
                    
                    <span class="counseling-badge" style="color: #059669; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-check"></i> Tercatat di Kohort Posyandu
                    </span>
                </div>
            @else
                <p style="margin: 0; font-size: 0.95rem; color: #9ca3af; font-style: italic;">
                    Belum ada catatan penyuluhan atau pendampingan yang dicatat pada pemeriksaan terakhir.
                </p>
            @endif
        </div>
    </section>

    <section class="growth-card" aria-labelledby="growth-title">
        <div class="growth-card-head">
            <div>
                <h2 id="growth-title">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    Grafik Pertumbuhan Balita (KMS Digital)
                </h2>
                <p>Bandingkan tren tinggi, berat, dan proporsi tubuh berdasarkan data pengukuran yang tersimpan.</p>
            </div>

            <div class="metric-tabs" role="tablist" aria-label="Indikator pertumbuhan">
                @foreach ($growthCharts as $chartKey => $chart)
                    <button
                        type="button"
                        class="metric-tab {{ $chartKey === 'tb-u' ? 'is-active' : '' }}"
                        role="tab"
                        aria-selected="{{ $chartKey === 'tb-u' ? 'true' : 'false' }}"
                        data-chart-target="{{ $chartKey }}"
                    >
                        {{ $chart['label'] }}
                        <span>({{ $chart['subLabel'] }})</span>
                    </button>
                @endforeach
            </div>
        </div>

        @foreach ($growthCharts as $chartKey => $chart)
            @include('kader.partials.growth-chart', [
                'chartKey' => $chartKey,
                'chart' => $chart,
            ])
        @endforeach
    </section>

    <div class="history-tabs" role="tablist" aria-label="Jenis riwayat pemeriksaan">
        <button
            type="button"
            class="history-tab is-active"
            role="tab"
            aria-selected="true"
            data-history-target="anthropometry"
        >
            <i class="fa-solid fa-chart-column"></i>
            Pengukuran Antropometri
        </button>
        <button
            type="button"
            class="history-tab"
            role="tab"
            aria-selected="false"
            data-history-target="immunization-vitamin"
        >
            <i class="fa-solid fa-syringe"></i>
            Imunisasi &amp; Vitamin
        </button>
    </div>

    <section
        id="riwayat-pengukuran"
        class="measurement-history history-panel"
        data-history-panel="anthropometry"
        aria-label="Riwayat pengukuran antropometri"
    >
        @forelse ($measurementHistory as $measurement)
            <article class="measurement-card measurement-card-{{ $measurement['tone'] }}">
                <div class="measurement-head">
                    <div class="measurement-title-wrap">
                        <span class="measurement-icon"><i class="fa-solid fa-ruler-horizontal"></i></span>
                        <div>
                            <h2>Pemeriksaan Usia {{ $measurement['age'] }}</h2>
                            <p>Tanggal pemeriksaan: {{ $measurement['date'] }}</p>
                        </div>
                    </div>

                    <span class="measurement-verification measurement-verification-{{ $measurement['verificationTone'] }}">
                        <i class="fa-solid {{ $measurement['verificationTone'] === 'verified' ? 'fa-circle-check' : 'fa-clock' }}"></i>
                        {{ $measurement['verificationLabel'] }}
                    </span>
                </div>

                <div class="measurement-grid">
                    <div class="measurement-metric metric-highlight metric-highlight-{{ $measurement['tone'] }}">
                        <div class="metric-label-row">
                            <span>TINGGI BADAN (TB)</span>
                            <i class="fa-solid fa-arrows-up-down"></i>
                        </div>
                        <div class="metric-value">{{ $measurement['height'] }} <small>cm</small></div>
                        <div class="metric-meta">
                            <span>Z-Score TB/U</span>
                            <strong>{{ $measurement['zScore'] }}{{ $measurement['zScore'] !== '—' ? ' SD' : '' }}</strong>
                        </div>
                        <span class="metric-badge metric-badge-{{ $measurement['tone'] }}">{{ $measurement['status'] }}</span>
                    </div>

                    <div class="measurement-metric">
                        <div class="metric-label-row">
                            <span>BERAT BADAN (BB)</span>
                            <i class="fa-solid fa-scale-balanced metric-icon-green"></i>
                        </div>
                        <div class="metric-value">{{ $measurement['weight'] }} <small>kg</small></div>
                        <div class="metric-meta metric-meta-stack">
                            <span>Hasil pengukuran</span>
                            <strong>{{ $measurement['weight'] === '—' ? 'Belum dicatat' : 'Tercatat' }}</strong>
                        </div>
                    </div>

                    <div class="measurement-metric">
                        <div class="metric-label-row">
                            <span>LINGKAR LENGAN (LiLA)</span>
                            <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                        </div>
                        <div class="metric-value">{{ $measurement['armCircumference'] }} <small>cm</small></div>
                        <div class="metric-meta metric-meta-stack">
                            <span>Hasil pengukuran</span>
                            <strong>{{ $measurement['armCircumference'] === '—' ? 'Belum dicatat' : 'Tercatat' }}</strong>
                        </div>
                    </div>

                    <div class="measurement-metric">
                        <div class="metric-label-row">
                            <span>LINGKAR KEPALA (LK)</span>
                            <i class="fa-regular fa-face-smile metric-icon-green"></i>
                        </div>
                        <div class="metric-value">{{ $measurement['headCircumference'] }} <small>cm</small></div>
                        <div class="metric-meta metric-meta-stack">
                            <span>Tindak lanjut</span>
                            <strong>{{ $measurement['followUp'] }}</strong>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="measurement-empty">
                <span><i class="fa-solid fa-chart-line"></i></span>
                <h2>Belum ada riwayat pengukuran</h2>
                <p>Data antropometri anak akan tampil di sini setelah pemeriksaan pertama dicatat.</p>
            </div>
        @endforelse
    </section>

    <section
        class="health-history history-panel"
        data-history-panel="immunization-vitamin"
        aria-label="Riwayat imunisasi dan vitamin"
        hidden
    >
        @include('kader.partials.health-history', [
            'healthHistory' => $healthHistory,
            'healthLastUpdated' => $healthLastUpdated,
        ])
    </section>
</main>

@endsection

@push('styles')
    @vite('resources/css/kader/profil-balita.css')
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const page = document.querySelector('.kms-page');

    if (!page) {
        return;
    }

    const activateTab = function (buttons, panels, targetAttribute, panelAttribute, target) {
        buttons.forEach(function (button) {
            const isActive = button.dataset[targetAttribute] === target;

            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach(function (panel) {
            panel.hidden = panel.dataset[panelAttribute] !== target;
        });
    };

    const chartButtons = Array.from(page.querySelectorAll('[data-chart-target]'));
    const chartPanels = Array.from(page.querySelectorAll('[data-chart-panel]'));

    chartButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activateTab(chartButtons, chartPanels, 'chartTarget', 'chartPanel', button.dataset.chartTarget);
        });
    });

    const historyButtons = Array.from(page.querySelectorAll('[data-history-target]'));
    const historyPanels = Array.from(page.querySelectorAll('[data-history-panel]'));

    historyButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activateTab(historyButtons, historyPanels, 'historyTarget', 'historyPanel', button.dataset.historyTarget);
        });
    });
});
</script>
@endpush
