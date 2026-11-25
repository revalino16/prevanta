<div
    class="growth-chart-panel"
    data-chart-panel="{{ $chartKey }}"
    @if ($chartKey !== 'tb-u') hidden @endif
>
    @if ($chartKey === 'tb-u' && !empty($chart['chartTitle']))
        {{-- Header Khas Buku KIA / WHO --}}
        <div class="who-chart-header who-theme-{{ strtolower($chart['gender'] ?? 'l') }}">
            <div class="who-header-left">
                <span class="who-header-badge">{{ $chart['standardTag'] ?? 'WHO Child Growth Standards' }}</span>
                <h3 class="who-header-title">{{ $chart['chartTitle'] }}</h3>
            </div>
            <div class="who-header-right">
                <span class="who-gender-pill who-gender-{{ strtolower($chart['gender'] ?? 'l') }}">
                    <i class="fa-solid {{ ($chart['gender'] ?? 'L') === 'P' ? 'fa-venus' : 'fa-mars' }}"></i>
                    {{ $chart['genderLabel'] }}
                </span>
            </div>
        </div>
    @else
        <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px;">
            <p class="chart-description">{{ $chart['description'] }}</p>
        </div>
    @endif

    <div class="chart-shell who-chart-wrapper">
        <svg
            class="growth-chart who-growth-svg"
            viewBox="0 0 960 410"
            role="img"
            aria-labelledby="growth-chart-title-{{ $chartKey }} growth-chart-description-{{ $chartKey }}"
        >
            <title id="growth-chart-title-{{ $chartKey }}">
                Grafik {{ $chart['label'] }} {{ $balita->nama }}
            </title>
            <desc id="growth-chart-description-{{ $chartKey }}">{{ $chart['description'] }}</desc>

            <defs>
                {{-- Pattern / shadow bila diperlukan --}}
            </defs>

            {{-- Background Bidang Grafik --}}
            <rect
                x="68"
                y="28"
                width="792"
                height="290"
                class="who-chart-canvas-bg"
            ></rect>

            {{-- 1. Zona Warna Melengkung Standar WHO (Polygon Shading) --}}
            @if (!empty($chart['curvedZones']))
                @foreach ($chart['curvedZones'] as $zone)
                    <polygon points="{{ $zone['points'] }}" class="chart-zone chart-zone-{{ $zone['type'] }}"></polygon>
                @endforeach
            @elseif (!empty($chart['showsZones']) && $chart['showsZones'])
                <rect x="68" y="69.43" width="792" height="165.71" class="chart-zone chart-zone-normal"></rect>
                <rect x="68" y="235.14" width="792" height="41.43" class="chart-zone chart-zone-warning"></rect>
                <rect x="68" y="276.57" width="792" height="41.43" class="chart-zone chart-zone-danger"></rect>
            @endif

            {{-- 2. Grid Milimeter Blok Rapat: Vertikal (Sumbu X) --}}
            @if (!empty($chart['xMinorGrid']))
                @foreach ($chart['xMinorGrid'] as $xGrid)
                    <line
                        x1="{{ $xGrid['x'] }}"
                        y1="28"
                        x2="{{ $xGrid['x'] }}"
                        y2="318"
                        class="{{ $xGrid['isYear'] ? 'who-grid-year' : ($xGrid['isStep'] ? 'who-grid-step' : 'who-grid-minor') }}"
                    ></line>
                @endforeach
            @else
                @foreach ($chart['xTicks'] as $tick)
                    <line
                        x1="{{ $tick['x'] }}"
                        y1="28"
                        x2="{{ $tick['x'] }}"
                        y2="318"
                        class="chart-grid-line"
                    ></line>
                @endforeach
            @endif

            {{-- 3. Grid Milimeter Blok Rapat: Horizontal (Sumbu Y) --}}
            @if (!empty($chart['yMinorGrid']))
                @foreach ($chart['yMinorGrid'] as $yGrid)
                    <line
                        x1="68"
                        y1="{{ $yGrid['y'] }}"
                        x2="860"
                        y2="{{ $yGrid['y'] }}"
                        class="{{ $yGrid['val'] % 10 === 0 ? 'who-grid-ten' : ($yGrid['isMajor'] ? 'who-grid-five' : 'who-grid-minor') }}"
                    ></line>
                @endforeach
            @else
                @foreach ($chart['yTicks'] as $tick)
                    <line
                        x1="68"
                        y1="{{ $tick['y'] }}"
                        x2="860"
                        y2="{{ $tick['y'] }}"
                        class="chart-grid-line"
                    ></line>
                @endforeach
            @endif

            {{-- 4. Label Angka Sumbu Y di SISI KIRI SAJA (Sisi kanan khusus badge Z-Score agar tidak bertumpuk) --}}
            @foreach ($chart['yTicks'] as $tick)
                <text x="60" y="{{ $tick['y'] + 3.5 }}" class="who-axis-num who-num-left">
                    {{ $tick['label'] }}
                </text>
            @endforeach

            {{-- 5. Label Angka Sumbu X (Umur Balita Bulan/Tahun) --}}
            @foreach ($chart['xTicks'] as $tick)
                <text x="{{ $tick['x'] }}" y="334" class="who-x-label">
                    {{ $tick['label'] }}
                </text>
            @endforeach

            {{-- 6. Kurva Baku WHO (3, 2, 0, -2, -3 SD) dengan Label di Luar Sisi Kanan Frame --}}
            @if (!empty($chart['whoCurves']))
                @foreach ($chart['whoCurves'] as $curve)
                    <polyline
                        points="{{ $curve['points'] }}"
                        class="who-curve who-curve-{{ $curve['tone'] }}"
                    ></polyline>
                    {{-- Teks Angka Z-score Sepenuhnya di Luar Grafik (x = 868, sejajar dengan angka sumbu) --}}
                    <text
                        x="868"
                        y="{{ $curve['end']['y'] + 3.5 }}"
                        class="who-badge-text who-badge-text-{{ $curve['tone'] }}"
                    >{{ $curve['short'] }}</text>
                @endforeach
            @endif

            {{-- 7. Garis Hubung & Titik Plotting Aktual Balita --}}
            @if (count($chart['points']) > 1)
                <polyline points="{{ $chart['polyline'] }}" class="who-child-line"></polyline>
            @endif

            @foreach ($chart['points'] as $point)
                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5.5" class="who-child-point">
                    <title>{{ $point['tooltip'] }}</title>
                </circle>
            @endforeach

            {{-- 8. Frame Border Grafik --}}
            <rect
                x="68"
                y="28"
                width="792"
                height="290"
                class="who-chart-frame"
            ></rect>

            {{-- 9. Keterangan Judul Sumbu Bahasa Indonesia --}}
            @if (!empty($chart['xAxisTitle']))
                <text x="464" y="365" class="who-title-axis-x">{{ $chart['xAxisTitle'] }}</text>
            @endif

            @if (!empty($chart['yAxisTitle']))
                {{-- Sumbu Y Kiri --}}
                <text
                    x="-173"
                    y="18"
                    transform="rotate(-90)"
                    class="who-title-axis-y"
                    text-anchor="middle"
                >{{ $chart['yAxisTitle'] }}</text>
            @endif

            {{-- Tampilan Kosong Bila Belum Ada Data --}}
            @if (empty($chart['points']))
                <rect x="280" y="140" width="368" height="65" rx="8" fill="rgba(255,255,255,0.9)" stroke="#e2d9cf"></rect>
                <text x="464" y="166" class="chart-empty-title">{{ $chart['emptyTitle'] }}</text>
                <text x="464" y="188" class="chart-empty-copy">{{ $chart['emptyCopy'] }}</text>
            @endif
        </svg>
    </div>

    <div class="chart-legend who-chart-legend">
        @foreach ($chart['legends'] as $legend)
            <span>
                <i class="legend-dot legend-{{ $legend['tone'] }}"></i>
                {{ $legend['label'] }}
            </span>
        @endforeach
    </div>

    <p class="chart-note">* {{ $chart['note'] }}</p>
</div>
