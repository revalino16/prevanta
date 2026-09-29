@extends('orangtua.layouts.orangtua')

@section('title', 'Anakku - Prevanta')

@push('styles')
    @vite('resources/css/orangtua/anakku.css')
@endpush

@section('content')

    @php
        // Data orang tua yang sedang login
        $orangTua = $orangTua ?? Auth::user()?->orangTua;

        // Data pendukung halaman
        $posyandu = $posyandu ?? null;
        $notifCount = $notifCount ?? 0;
        $tanggalHariIni = $tanggalHariIni ?? now()->translatedFormat('l, d F Y');
        $periodeSiklus = $periodeSiklus ?? null;
        $agendaList = $agendaList ?? collect();

        // Sapaan menggunakan nama user
        $sapaanKeluarga = $sapaanKeluarga ?? (Auth::user()?->nama ?? 'Bunda & Ayah');

        // Data anak dari database
        $anakList = $anakList ?? collect();

        // Anak pertama sebagai anak utama
        $anakUtama = $anakUtama ?? $anakList->first();
    @endphp


    <main class="content anakku-page">

        {{-- ══ HERO BANNER ══════════════════════════════════════════ --}}
        <div class="anakku-hero">
            <div class="anakku-hero-left">

                <div class="anakku-hero-badge">
                    <i class="fa-solid fa-people-group"></i>
                    Portal Mandiri Keluarga Sehat
                </div>

                <h1 class="anakku-hero-title">
                    Selamat Datang, {{ $sapaanKeluarga }}!
                </h1>

                <p class="anakku-hero-desc">
                    Pantau nutrisi, grafik KMS WHO balita tercinta, serta jadwal posyandu dan
                    imunisasi terjadwal langsung dari genggaman Anda.
                </p>

                <div class="anakku-hero-meta">
                    <span>
                        <i class="fa-solid fa-calendar-days"></i>
                        {{ $tanggalHariIni }}
                    </span>

                    <span class="dot">&bull;</span>

                    <span>
                        <i class="fa-solid fa-building"></i>
                        {{ $posyandu->nama ?? 'Posyandu Jambu 77' }}
                    </span>
                </div>

            </div>

            <div class="anakku-hero-right">
                <div class="anakku-hero-child">

                    <img src="{{ asset('images/bayi.png') }}" alt="Ilustrasi bayi" class="anakku-hero-img">

                </div>
            </div>
        </div>


        {{-- ══ AGENDA & JADWAL TERDEKAT ═════════════════════════════ --}}
        <div class="anakku-section-head">

            <div class="anakku-section-title">
                <i class="fa-solid fa-arrows-rotate"></i>
                <span>Agenda &amp; Jadwal Terdekat</span>
            </div>

            @if ($periodeSiklus)
                <span class="anakku-periode-pill">
                    {{ $periodeSiklus }}
                </span>
            @endif

        </div>

        <p class="anakku-section-sub">
            Pastikan hadir tepat waktu untuk menjaga kesinambungan tumbuh kembang optimal balita.
        </p>

        <div class="agenda-grid">

            @forelse($agendaList as $agenda)
                <div class="agenda-card">

                    <div class="agenda-card-top">
                        <span class="agenda-tag">
                            {{ $agenda['tag'] ?? 'Agenda' }}
                        </span>

                        <span class="agenda-countdown">
                            {{ $agenda['hari_lagi'] ?? '-' }} Hari Lagi
                        </span>
                    </div>

                    <h3 class="agenda-title">
                        {{ $agenda['judul'] ?? '-' }}
                    </h3>

                    <p class="agenda-desc">
                        {{ $agenda['deskripsi'] ?? '-' }}
                    </p>

                    <div class="agenda-schedule">

                        <div class="agenda-date-box">
                            <span class="agenda-date-month">
                                {{ $agenda['tanggal_bulan'] ?? '-' }}
                            </span>

                            <span class="agenda-date-day">
                                {{ $agenda['tanggal_hari'] ?? '-' }}
                            </span>
                        </div>

                        <div class="agenda-schedule-text">

                            <p class="agenda-time">
                                {{ $agenda['waktu_label'] ?? '-' }}
                                &bull;
                                {{ $agenda['jam_mulai'] ?? '-' }}
                                -
                                {{ $agenda['jam_selesai'] ?? '-' }}
                                WIB
                            </p>

                            <p>
                                {{ $agenda['lokasi'] ?? '-' }}
                            </p>

                            @if (!empty($agenda['catatan_lokasi']))
                                <p class="agenda-note">
                                    <i class="fa-solid fa-location-dot"></i>
                                    {{ $agenda['catatan_lokasi'] }}
                                </p>
                            @endif

                        </div>

                    </div>

                </div>

            @empty

                <div class="empty-state">
                    <i class="fa-solid fa-calendar-xmark"></i>

                    <h3>Belum Ada Agenda</h3>

                    <p>
                        Jadwal posyandu dan kegiatan lainnya akan muncul di sini.
                    </p>
                </div>
            @endforelse

        </div>


        {{-- ══ DAFTAR ANAK SAYA YANG TERDAFTAR ══════════════════════ --}}
        <div class="anakku-section-head" style="margin-top: 40px;">

            <div class="anakku-section-title">
                <i class="fa-solid fa-baby"></i>
                <span>Daftar Anak Saya yang Terdaftar</span>
            </div>

        </div>

        <p class="anakku-section-sub">
            Pilih profil anak untuk melihat buku KMS interaktif, kurva Z-score WHO,
            serta riwayat intervensi.
        </p>


        <div class="anak-list">

            @forelse($anakList as $anak)

                @php
                    // Ambil pengukuran terbaru
                    $p = $anak->pengukuranTerakhir ?? $anak->pengukuran->first();

                    // Hitung usia dalam bulan dari tanggal lahir
                    $usiaBulan = $anak->tanggal_lahir ? $anak->tanggal_lahir->diffInMonths(now()) : null;

                    // Nama orang tua
                    $namaOrangTua = $anak->orangTua?->user?->nama ?? ($orangTua?->user?->nama ?? '-');

                    // Status pertumbuhan dari database
                    $statusPertumbuhan = $p?->status_pertumbuhan;

                    // Level hanya untuk kebutuhan tampilan warna
                    $statusLower = strtolower($statusPertumbuhan ?? '');

                    if (
                        str_contains($statusLower, 'sangat pendek') ||
                        str_contains($statusLower, 'sangat kurus') ||
                        str_contains($statusLower, 'buruk')
                    ) {
                        $level = 'danger';
                    } elseif (
                        str_contains($statusLower, 'pendek') ||
                        str_contains($statusLower, 'kurus') ||
                        str_contains($statusLower, 'kurang')
                    ) {
                        $level = 'warning';
                    } else {
                        $level = 'ok';
                    }
                @endphp


                <div class="anak-card-wrap">

                    <span class="anak-accent anak-accent--{{ $level }}"></span>

                    <div class="anak-card">

                        {{-- ══ FOTO & IDENTITAS ══ --}}
                        <div class="anak-identity">

                            <div class="anak-photo-wrap">

                                <div class="anak-photo anak-photo-placeholder">
                                    <i class="fa-solid fa-child"></i>
                                </div>

                                <span class="anak-gender-badge">
                                    {{ strtoupper(substr($anak->jenis_kelamin ?? 'L', 0, 1)) }}
                                </span>

                            </div>


                            <div class="anak-identity-text">

                                <p class="anak-name" title="{{ $anak->nama }}">
                                    {{ $anak->nama }}
                                </p>

                                <p class="anak-nik">
                                    NIK: {{ $anak->nik ?? '-' }}
                                </p>

                                <div class="anak-meta-row">

                                    @if ($usiaBulan !== null)
                                        <span class="anak-age-pill">
                                            {{ number_format($usiaBulan, 1, ',', '.') }} Bulan
                                        </span>
                                    @endif

                                    @if ($anak->tanggal_lahir)
                                        <span>
                                            &bull; Lahir:
                                            {{ $anak->tanggal_lahir->translatedFormat('d M Y') }}
                                        </span>
                                    @endif

                                </div>

                                <p class="anak-parent">
                                    <i class="fa-solid fa-baby-carriage"></i>

                                    Orang Tua:
                                    {{ $namaOrangTua }}

                                </p>

                                @if ($anak->alamat)
                                    <p class="anak-parent">
                                        <i class="fa-solid fa-location-dot"></i>
                                        {{ $anak->alamat }}
                                    </p>
                                @endif

                            </div>

                        </div>


                        {{-- ══ PENGUKURAN TERBARU ══ --}}
                        <div class="anak-measure">

                            <div class="anak-measure-top">

                                <span>
                                    <i class="fa-solid fa-calendar-days"></i>

                                    @if ($p?->tanggal_pengukuran)
                                        {{ \Illuminate\Support\Carbon::parse($p->tanggal_pengukuran)->translatedFormat('d M Y') }}
                                    @else
                                        Belum ada pengukuran
                                    @endif

                                </span>

                                @if ($statusPertumbuhan)
                                    <span class="anak-measure-flag">
                                        {{ $statusPertumbuhan }}
                                    </span>
                                @endif

                            </div>


                            <div class="anak-measure-grid">

                                {{-- Tinggi Badan --}}
                                <div class="anak-measure-item">

                                    <p class="anak-measure-label">
                                        PB / TB
                                    </p>

                                    <p class="anak-measure-value {{ $level === 'danger' ? 'is-danger' : '' }}">
                                        {{ $p?->tinggi_badan ?? '-' }}
                                    </p>

                                    <p class="anak-measure-unit">
                                        cm
                                    </p>

                                </div>


                                {{-- Berat Badan --}}
                                <div class="anak-measure-item">

                                    <p class="anak-measure-label">
                                        BB
                                    </p>

                                    <p class="anak-measure-value">
                                        {{ $p?->berat_badan ?? '-' }}
                                    </p>

                                    <p class="anak-measure-unit">
                                        kg
                                    </p>

                                </div>


                                {{-- LILA --}}
                                <div class="anak-measure-item">

                                    <p class="anak-measure-label">
                                        LILA
                                    </p>

                                    <p class="anak-measure-value">
                                        {{ $p?->lingkar_lengan_atas ?? '-' }}
                                    </p>

                                    <p class="anak-measure-unit">
                                        cm
                                    </p>

                                </div>

                            </div>


                            <div class="anak-measure-footnote">

                                Z-Score:
                                {{ $p?->z_score ?? '-' }}
                                SD

                            </div>

                        </div>


                        {{-- ══ STATUS & AKSI ══ --}}
                        <div class="anak-status">

                            <div class="anak-status-text">

                                @if ($statusPertumbuhan)
                                    <p class="anak-status-badge anak-status-badge--{{ $level }}">

                                        @if ($level === 'danger')
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                        @elseif($level === 'warning')
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                        @else
                                            <i class="fa-solid fa-circle-check"></i>
                                        @endif

                                        {{ $statusPertumbuhan }}

                                    </p>
                                @else
                                    <p class="anak-status-note">
                                        Belum ada data status pertumbuhan.
                                    </p>
                                @endif


                                @if ($p?->z_score !== null)
                                    <p class="anak-status-note">
                                        Z-Score terakhir:
                                        {{ $p->z_score }} SD
                                    </p>
                                @endif

                            </div>


                            <a href="{{ route('orangtua.balita.kms', $anak->id) }}" class="anak-kms-btn">
                                <i class="fa-solid fa-chart-line"></i>
                                Lihat KMS
                            </a>

                        </div>

                    </div>

                </div>

            @empty

                <div class="empty-state">

                    <i class="fa-solid fa-child-reaching"></i>

                    <h3>Belum Ada Anak Terdaftar</h3>

                    <p>
                        Data balita kamu akan muncul di sini setelah didaftarkan
                        oleh kader/bidan posyandu.
                    </p>

                </div>

            @endforelse

        </div>

    </main>

@endsection
