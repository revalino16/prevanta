@extends('orangtua.layouts.orangtua')

@push('styles')
    @vite('resources/css/orangtua/riwayat.css')
@endpush

@section('content')


@php
    $fmt = fn ($n, $d = 1) => $n !== null
        ? number_format((float) $n, $d, ',', '.')
        : '-';

    $zLabel = fn ($z) => $z !== null
        ? (($z >= 0 ? '+' : '') . number_format((float) $z, 2, '.', '') . ' SD')
        : '-';

    $badge = function ($status) {
        $status = strtolower((string) $status);

        if (
            str_contains($status, 'sangat buruk') ||
            str_contains($status, 'sangat pendek') ||
            str_contains($status, 'buruk')
        ) {
            return 'badge-danger';
        }

        if (
            str_contains($status, 'kurang') ||
            str_contains($status, 'pendek') ||
            str_contains($status, 'waspada')
        ) {
            return 'badge-warn';
        }

        return 'badge-ok';
    };
@endphp

<div class="riwayat-page">

    {{-- HEADER HALAMAN --}}
    <div class="riwayat-heading">
        <div>
            <div class="riwayat-title-row">
                <h1>Riwayat Anak</h1>
                <span class="tag">Mode Pantau (Read-Only)</span>
            </div>

            <p>
                Pantau rekapitulasi pengukuran pertumbuhan buah hati Anda
                secara berkala.
            </p>
        </div>
    </div>


    {{-- DATA ANAK --}}
    @if($anak)

        <section class="anak-profile">

            <div class="anak-avatar">
                <img
                    src="{{ asset('images/cowo.jpg') }}"
                    alt="Foto {{ $anak->nama }}"
                >

                <span class="gender-badge">
                    {{ $anak->jenis_kelamin === 'P' ? '♀' : '♂' }}
                </span>
            </div>

            <div class="anak-info">
                <h2>{{ $anak->nama }}</h2>

                <p class="anak-nik">
                    NIK: {{ $anak->nik ?? '-' }}
                </p>

                <div class="anak-meta">

                    <span>
                        <i class="fa-solid fa-calendar"></i>

                        @if($anak->tanggal_lahir)
                            {{ $anak->tanggal_lahir->locale('id')->translatedFormat('d F Y') }}
                        @else
                            -
                        @endif
                    </span>

                    <span>
                        <i class="fa-solid fa-venus-mars"></i>

                        {{ $anak->jenis_kelamin === 'P'
                            ? 'Perempuan'
                            : 'Laki-laki'
                        }}
                    </span>

                    @if($anak->tanggal_lahir)
                        <span>
                            <i class="fa-solid fa-child"></i>

                            {{ number_format($anak->tanggal_lahir->diffInMonths(now()), 1, ',', '.') }}
                            Bulan
                        </span>
                    @endif

                </div>
            </div>

            {{-- GANTI ANAK --}}
            @if(isset($daftarAnak) && $daftarAnak->count() > 1)

                <div class="anak-selector">

                    <label for="pilihAnak">
                        Pilih Anak
                    </label>

                    <select
                        id="pilihAnak"
                        onchange="if(this.value) window.location.href=this.value"
                    >

                        @foreach($daftarAnak as $a)

                            <option
                                value="{{ route('orangtua.riwayat', ['anak' => $a->id]) }}"
                                {{ $a->id == $anak->id ? 'selected' : '' }}
                            >
                                {{ $a->nama }}
                            </option>

                        @endforeach

                    </select>

                </div>

            @endif

        </section>


        {{-- TAB --}}
        <div class="riwayat-tabs">

            <button
                type="button"
                class="tab-button active"
                onclick="showTab('pengukuran', this)"
            >
                <i class="fa-solid fa-ruler-combined"></i>
                Pengukuran Antropometri
            </button>

            <button
                type="button"
                class="tab-button"
                onclick="showTab('imunisasi', this)"
            >
                <i class="fa-solid fa-syringe"></i>
                Imunisasi & Vitamin
            </button>

        </div>


        {{-- TAB PENGUKURAN --}}
        <div id="pengukuran" class="tab-content active">

            <div class="section-heading">
                <div>
                    <h2>Riwayat Pengukuran</h2>
                    <p>
                        Data hasil pengukuran pertumbuhan {{ $anak->nama }}.
                    </p>
                </div>

                <span class="total-data">
                    {{ $riwayat->count() }} Pemeriksaan
                </span>
            </div>


            @forelse($riwayat as $p)

                @php
                    $status = $p->status_pertumbuhan ?? 'Belum ada status';

                    $statusClass = $badge($status);

                    $tanggalPengukuran = $p->tanggal_pengukuran
                        ? \Illuminate\Support\Carbon::parse($p->tanggal_pengukuran)
                        : null;

                    $usiaSaatPengukuran = null;

                    if ($tanggalPengukuran && $anak->tanggal_lahir) {
                        $usiaSaatPengukuran =
                            $anak->tanggal_lahir->diffInMonths($tanggalPengukuran);
                    }

                    $zScoreAlert = $p->z_score !== null &&
                        ((float) $p->z_score < -2 || (float) $p->z_score > 3);
                @endphp


                <article class="measurement-card">

                    {{-- CARD HEADER --}}
                    <div class="measurement-header">

                        <div>

                            <h3>
                                Pemeriksaan

                                @if($usiaSaatPengukuran !== null)
                                    Usia {{ number_format($usiaSaatPengukuran, 1, ',', '.') }} Bulan
                                @endif
                            </h3>

                            <small>
                                <i class="fa-regular fa-calendar"></i>

                                @if($tanggalPengukuran)
                                    {{ $tanggalPengukuran->locale('id')->translatedFormat('d F Y') }}
                                @else
                                    Tanggal tidak tersedia
                                @endif
                            </small>

                        </div>

                        <span class="status-badge {{ $statusClass }}">
                            {{ $status }}
                        </span>

                    </div>


                    {{-- DATA PENGUKURAN --}}
                    <div class="measurement-grid">

                        {{-- TINGGI BADAN --}}
                        <div class="measurement-tile {{ $zScoreAlert ? 'alert' : '' }}">

                            <div class="tile-icon">
                                <i class="fa-solid fa-ruler-vertical"></i>
                            </div>

                            <div class="tile-content">

                                <span class="tile-label">
                                    Tinggi Badan
                                </span>

                                <strong>
                                    {{ $fmt($p->tinggi_badan) }}
                                    <small>cm</small>
                                </strong>

                                @if($p->z_score !== null)
                                    <span class="tile-description">
                                        Z-Score:
                                        {{ $zLabel($p->z_score) }}
                                    </span>
                                @endif

                            </div>

                        </div>


                        {{-- BERAT BADAN --}}
                        <div class="measurement-tile">

                            <div class="tile-icon">
                                <i class="fa-solid fa-weight-scale"></i>
                            </div>

                            <div class="tile-content">

                                <span class="tile-label">
                                    Berat Badan
                                </span>

                                <strong>
                                    {{ $fmt($p->berat_badan, 2) }}
                                    <small>kg</small>
                                </strong>

                            </div>

                        </div>


                        {{-- LINGKAR LENGAN --}}
                        <div class="measurement-tile">

                            <div class="tile-icon">
                                <i class="fa-solid fa-arrows-left-right"></i>
                            </div>

                            <div class="tile-content">

                                <span class="tile-label">
                                    Lingkar Lengan Atas
                                </span>

                                <strong>
                                    {{ $fmt($p->lingkar_lengan_atas) }}
                                    <small>cm</small>
                                </strong>

                            </div>

                        </div>


                        {{-- LINGKAR KEPALA --}}
                        <div class="measurement-tile">

                            <div class="tile-icon">
                                <i class="fa-solid fa-circle"></i>
                            </div>

                            <div class="tile-content">

                                <span class="tile-label">
                                    Lingkar Kepala
                                </span>

                                <strong>
                                    {{ $fmt($p->lingkar_kepala) }}
                                    <small>cm</small>
                                </strong>

                            </div>

                        </div>

                    </div>


                    {{-- Z SCORE --}}
                    @if($p->z_score !== null)

                        <div class="zscore-info">

                            <div>
                                <strong>Z-Score Pertumbuhan</strong>

                                <span>
                                    Nilai berdasarkan data pengukuran
                                    yang tercatat.
                                </span>
                            </div>

                            <strong class="zscore-value {{ $zScoreAlert ? 'text-danger' : 'text-ok' }}">
                                {{ $zLabel($p->z_score) }}
                            </strong>

                        </div>

                    @endif


                    {{-- POSISI PENGUKURAN --}}
                    @if($p->posisi_pengukuran)

                        <div class="measurement-note">

                            <i class="fa-solid fa-circle-info"></i>

                            <span>
                                Posisi pengukuran:
                                <strong>
                                    {{ $p->posisi_pengukuran }}
                                </strong>
                            </span>

                        </div>

                    @endif

                </article>

            @empty

                <div class="empty-state">

                    <div class="empty-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>

                    <h3>Belum Ada Riwayat Pengukuran</h3>

                    <p>
                        Belum terdapat data pengukuran untuk
                        {{ $anak->nama }}.
                    </p>

                </div>

            @endforelse

        </div>


        {{-- TAB IMUNISASI --}}
        <div id="imunisasi" class="tab-content">

            <div class="section-heading">
                <div>
                    <h2>Imunisasi & Vitamin</h2>
                    <p>
                        Riwayat pemberian imunisasi dan vitamin anak.
                    </p>
                </div>
            </div>

            <div class="empty-state">

                <div class="empty-icon">
                    <i class="fa-solid fa-syringe"></i>
                </div>

                <h3>Data Belum Tersedia</h3>

                <p>
                    Data imunisasi dan vitamin akan ditampilkan
                    pada halaman ini setelah tersedia.
                </p>

            </div>

        </div>


    @else

        {{-- TIDAK ADA ANAK --}}
        <div class="empty-state">

            <div class="empty-icon">
                <i class="fa-solid fa-child"></i>
            </div>

            <h3>Data Anak Belum Tersedia</h3>

            <p>
                Belum terdapat data anak yang terhubung dengan akun orang tua.
            </p>

        </div>

    @endif

</div>


<script>
    function showTab(tabId, button) {

        document.querySelectorAll('.tab-content')
            .forEach(function (content) {
                content.classList.remove('active');
            });

        document.querySelectorAll('.tab-button')
            .forEach(function (btn) {
                btn.classList.remove('active');
            });

        const selectedTab = document.getElementById(tabId);

        if (selectedTab) {
            selectedTab.classList.add('active');
        }

        if (button) {
            button.classList.add('active');
        }
    }
</script>

@endsection

