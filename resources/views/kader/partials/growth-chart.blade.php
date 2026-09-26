<div
    class="growth-chart-panel"
    data-chart-panel="{{ $chartKey }}"
    @if ($chartKey !== 'tb-u') hidden @endif
>
    <p class="chart-description">{{ $chart['description'] }}</p>

    <div class="chart-shell">
        <svg
            class="growth-chart"
            viewBox="0 0 960 370"
            role="img"
            aria-labelledby="growth-chart-title-{{ $chartKey }} growth-chart-description-{{ $chartKey }}"
        >
            <title id="growth-chart-title-{{ $chartKey }}">
                Grafik {{ $chart['label'] }} {{ $balita->nama }}
            </title>
            <desc id="growth-chart-description-{{ $chartKey }}">{{ $chart['description'] }}</desc>

            @if ($chart['showsZones'])
                <rect x="68" y="69.43" width="824" height="165.71" class="chart-zone chart-zone-normal"></rect>
                <rect x="68" y="235.14" width="824" height="41.43" class="chart-zone chart-zone-warning"></rect>
                <rect x="68" y="276.57" width="824" height="41.43" class="chart-zone chart-zone-danger"></rect>
            @endif

            @foreach ($chart['xTicks'] as $tick)
                <line
                    x1="{{ $tick['x'] }}"
                    y1="28"
                    x2="{{ $tick['x'] }}"
                    y2="318"
                    class="chart-grid-line"
                ></line>
                <text x="{{ $tick['x'] }}" y="348" class="chart-x-label">{{ $tick['label'] }}</text>
            @endforeach

            @foreach ($chart['yTicks'] as $tick)
                <line
                    x1="68"
                    y1="{{ $tick['y'] }}"
                    x2="892"
                    y2="{{ $tick['y'] }}"
                    class="chart-threshold chart-threshold-{{ $tick['tone'] }}"
                ></line>
                <text x="58" y="{{ $tick['y'] + 4 }}" class="chart-y-label chart-y-label-{{ $tick['tone'] }}">
                    {{ $tick['label'] }}
                </text>
            @endforeach

            @if (count($chart['points']) > 1)
                <polyline points="{{ $chart['polyline'] }}" class="chart-line"></polyline>
            @endif

            @foreach ($chart['points'] as $point)
                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="6" class="chart-point">
                    <title>{{ $point['tooltip'] }}</title>
                </circle>
            @endforeach

            @if (empty($chart['points']))
                <text x="480" y="188" class="chart-empty-title">{{ $chart['emptyTitle'] }}</text>
                <text x="480" y="210" class="chart-empty-copy">{{ $chart['emptyCopy'] }}</text>
            @endif
        </svg>
    </div>

    <div class="chart-legend">
        @foreach ($chart['legends'] as $legend)
            <span>
                <i class="legend-dot legend-{{ $legend['tone'] }}"></i>
                {{ $legend['label'] }}
            </span>
        @endforeach
    </div>

    <p class="chart-note">* {{ $chart['note'] }}</p>
</div>
