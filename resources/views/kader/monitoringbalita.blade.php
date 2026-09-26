@extends('kader.layouts.kader')

@section('title', 'Monitoring Balita — Prevanta')

@section('content')

<main class="content">

    {{-- PAGE HEADER --}}
    <div class="page-head">

        <div>
            <div class="eyebrow">E-POSYANDU TERINTEGRASI</div>

            <h1>Monitoring Balita</h1>

            <p>
                Kelola data antropometri, pantau kurva pertumbuhan balita, dan lakukan
                tindakan intervensi gizi terpadu.
            </p>
        </div>

        <div class="page-head-actions">
            <a
                href="{{ route('kader.balita.tambah') }}"
                class="btn btn-primary"
            >
                <i class="fa-solid fa-user-plus"></i>
                + Tambah Balita
            </a>
            <button type="button" class="btn btn-outline">
                <i class="fa-solid fa-download"></i>
                Unduh Rekap Laporan
            </button>
        </div>

    </div>


    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))
        <div class="success-message">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif


    {{-- FILTER PANEL --}}
    <div class="filter-panel">

        <div class="filter-row">

            <div class="tabs" id="tabs">

                <button
                    type="button"
                    class="tab active"
                    data-status=""
                >
                    Semua Status ({{ $balita->count() }})
                </button>

                <button
                    type="button"
                    class="tab"
                    data-status="success"
                >
                    Normal ({{ $countNormal }})
                </button>

                <button
                    type="button"
                    class="tab"
                    data-status="warning"
                >
                    Pendek ({{ $countPendek }})
                </button>

                <button
                    type="button"
                    class="tab"
                    data-status="danger"
                >
                    Sangat Pendek ({{ $countSangatPendek }})
                </button>

            </div>

            <button
                type="button"
                class="reset-link"
                id="resetFilter"
            >
                <i class="fa-solid fa-rotate-left"></i>
                Reset Filter
            </button>

        </div>


        <div class="search-row">

            <div class="search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="searchInput"
                    placeholder="Cari nama balita atau NIK..."
                >

            </div>


            <select id="genderFilter">

                <option value="">
                    Semua Gender
                </option>

                <option value="L">
                    Laki-laki
                </option>

                <option value="P">
                    Perempuan
                </option>

            </select>

        </div>

    </div>


    {{-- MONITORING LIST --}}
    <div class="monitoring-panel">

        <div class="panel-head">

            <div class="panel-title">
                <i class="fa-regular fa-face-smile"></i>
                Daftar Hasil Pengukuran Antropometri
            </div>

            <span
                class="panel-count"
                id="visibleCount"
            >
                Menampilkan {{ $balita->count() }} Balita
            </span>

        </div>


        <div class="balita-list">

            @forelse ($balita as $item)

                @php
                    $pengukuran = $item->latest_pengukuran;
                @endphp

                <div
                    class="balita-row"
                    data-name="{{ strtolower($item->nama) }}"
                    data-nik="{{ $item->nik }}"
                    data-gender="{{ $item->jenis_kelamin }}"
                    data-status="{{ $item->status_key }}"
                >

                    {{-- STATUS BAR --}}
                    <span
                        class="row-bar {{ $item->status_key ? 'bar-' . $item->status_key : '' }}"
                    ></span>


                    <div class="row-body">

                        {{-- IDENTITAS BALITA --}}
                        <div class="who">

                            <div class="avatar-wrap">

                                <img
                                    src="{{ asset($item->jenis_kelamin === 'L' ? 'images/cowo.jpg' : 'images/cewe.jpg') }}"
                                    alt="Avatar Balita"
                                    class="avatar"
                                    style="object-fit: cover;"
                                >

                                @if (in_array($item->jenis_kelamin, ['L', 'P']))
                                    <span
                                        class="gender-flag flag-{{ $item->jenis_kelamin }}"
                                    >
                                        {{ $item->jenis_kelamin }}
                                    </span>
                                @endif

                            </div>


                            <div class="who-detail">

                                <div class="who-name">
                                    {{ $item->nama }}
                                </div>

                                <div class="who-nik">
                                    NIK: {{ $item->nik }}
                                </div>

                                <div class="who-meta">

                                    @php
                                        $lahir = \Carbon\Carbon::parse($item->tanggal_lahir);
                                        $now = \Carbon\Carbon::now('Asia/Jakarta');

                                        $diff = $lahir->diff($now);
                                        $tahun = $diff->y;
                                        $bulan = $diff->m;

                                        $usiaTeks = '';
                                        if ($tahun > 0) {
                                            $usiaTeks .= $tahun . ' Tahun ';
                                        }
                                        $usiaTeks .= $bulan . ' Bulan';
                                    @endphp

                                    <span class="age-chip">
                                        <i class="fa-solid fa-cake-candles" style="margin-right: 4px;"></i>
                                        {{ $usiaTeks }}
                                    </span>

                                    <span class="dot-sep">•</span>

                                    <span class="birth-info">
                                        Lahir: {{ $lahir->isoFormat('D MMM YYYY') }}
                                    </span>

                                </div>




                            </div>

                        </div>


                        {{-- HASIL PENGUKURAN --}}
                        <div class="measure">

                            <div class="m-top">

                                <i class="fa-regular fa-calendar"></i>

                                @if ($pengukuran)

                                    {{ $pengukuran->tanggal_pengukuran }}

                                    <span
                                        class="highlight highlight-{{ $item->status_key }}"
                                    >
                                        {{ $item->highlight_label }}
                                    </span>

                                @else

                                    Belum ada pengukuran

                                @endif

                            </div>


                            <div class="m-vals">

                                {{-- TB / PB --}}
                                <div class="val-item">

                                    <div class="val-label">PB/TB</div>

                                    <div class="val-num {{ $item->status_key ? 'val-' . $item->status_key : '' }}">

                                        {{ $pengukuran->tinggi_badan ?? '-' }}

                                        @if ($pengukuran && $pengukuran->tinggi_badan !== null)
                                            <span class="val-unit">cm</span>
                                        @endif

                                    </div>

                                </div>


                                {{-- BB --}}
                                <div class="val-item">

                                    <div class="val-label">BB</div>

                                    <div class="val-num">

                                        {{ $pengukuran->berat_badan ?? '-' }}

                                        @if ($pengukuran && $pengukuran->berat_badan !== null)
                                            <span class="val-unit">kg</span>
                                        @endif

                                    </div>

                                </div>


                                {{-- LILA --}}
                                <div class="val-item">

                                    <div class="val-label">LILA</div>

                                    <div class="val-num">

                                        {{ $pengukuran->lingkar_lengan_atas ?? '-' }}

                                        @if ($pengukuran && $pengukuran->lingkar_lengan_atas !== null)
                                            <span class="val-unit">cm</span>
                                        @endif

                                    </div>

                                </div>




                            </div>

                        </div>


                        {{-- STATUS --}}
                        <div class="status-col">

                            @if ($pengukuran)

                                <span
                                    class="status-badge status-{{ $item->status_key }}"
                                >

                                    @if ($item->status_key === 'danger')
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    @elseif ($item->status_key === 'success')
                                        <i class="fa-solid fa-circle-check"></i>
                                    @elseif ($item->status_key === 'warning')
                                        <i class="fa-regular fa-circle-exclamation"></i>
                                    @else
                                        <i class="fa-regular fa-circle-question"></i>
                                    @endif

                                    {{ $pengukuran->status_pertumbuhan ?? '-' }}

                                </span>


                                

                                @if ($pengukuran->z_score ?? null)
                                    <div class="zscore-text">
                                        Z-Score: {{ $pengukuran->z_score }}
                                    </div>
                                @endif

                            @else

                                <span class="status-badge status-empty">
                                    Belum diukur
                                </span>

                            @endif

                        </div>


                        {{-- ACTION --}}
                        <div class="row-actions">

                            <a
                                href="#"
                                class="btn-kms"
                            >
                                <i class="fa-solid fa-chart-line"></i>
                                Lihat KMS
                            </a>


                            <a
                                href="{{ route('kader.balita.edit', $item->id) }}"
                                class="btn-edit"
                                title="Edit"
                            >
                                <i
                                    class="fa-solid fa-pen"
                                    style="font-size: 11px;"
                                ></i>
                            </a>

                        </div>

                    </div>

                </div>

            @empty

                <div class="empty-state">

                    <i class="fa-solid fa-child"></i>

                    <h3>
                        Belum ada data balita
                    </h3>

                    <p>
                        Data balita yang terdaftar akan ditampilkan di sini.
                    </p>

                </div>

            @endforelse

        </div>

    </div>

</main>

@endsection


@push('styles')
    @vite('resources/css/kader/monitoringbalita.css')
@endpush


@push('scripts')
    @vite('resources/js/kader/monitoringbalita.js')
@endpush
