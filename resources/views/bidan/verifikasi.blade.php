@extends('kader.layouts.kader')

@section('title', 'Verifikasi Pengukuran — Prevanta')

@section('content')

<main class="content">

    {{-- PAGE HEADER --}}
    <div class="page-head">

        <div>
            <div class="eyebrow">PANEL EVALUASI MEDIS BIDAN</div>

            <h1>Verifikasi Pengukuran Balita</h1>

            <p>
                Tinjau dan lakukan verifikasi data antropometri serta status gizi balita hasil pencatatan Kader Posyandu.
            </p>
        </div>

        <div class="page-head-actions">
            <button type="button" class="btn btn-outline">
                <i class="fa-solid fa-download"></i>
                Unduh Rekap Verifikasi
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




    {{-- MONITORING / VERIFIKASI LIST --}}
    <div class="monitoring-panel">

        <div class="panel-head">

            <div class="panel-title">
                <i class="fa-solid fa-clipboard-check"></i>
                Daftar Hasil Pengukuran untuk Diverifikasi
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
                        <div class="row-actions" style="display: flex; gap: 8px; align-items: center;">

                            @if ($pengukuran)
                                <form method="POST" action="{{ route('bidan.verifikasi.store', $pengukuran->id) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        style="background: linear-gradient(135deg, #2FA36B 0%, #1E7E4D 100%); color: #fff; border: none; padding: 8px 14px; border-radius: 9px; font-size: 12.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px -3px rgba(47, 163, 107, 0.35); transition: all 0.2s;"
                                    >
                                        <i class="fa-solid fa-circle-check"></i>
                                        Verifikasi
                                    </button>
                                </form>
                            @endif

                        </div>

                    </div>

                </div>

            @empty

                <div class="empty-state">

                    <i class="fa-solid fa-child"></i>

                    <h3>
                        Belum ada data balita untuk diverifikasi
                    </h3>

                    <p>
                        Data hasil pengukuran yang perlu diverifikasi akan muncul di sini.
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
