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
                        <div class="row-actions">

                            @if ($pengukuran)
                                <button
                                    type="button"
                                    class="btn-verif-trigger"
                                    data-pengukuran-id="{{ $pengukuran->id }}"
                                    data-nama="{{ $item->nama }}"
                                    data-nik="{{ $item->nik }}"
                                    data-gender="{{ $item->jenis_kelamin }}"
                                    data-tanggal-lahir="{{ \Carbon\Carbon::parse($item->tanggal_lahir)->isoFormat('D MMM YYYY') }}"
                                    data-tanggal-ukur="{{ $pengukuran->tanggal_pengukuran }}"
                                    data-bb="{{ $pengukuran->berat_badan ?? '-' }}"
                                    data-tb="{{ $pengukuran->tinggi_badan ?? '-' }}"
                                    data-lk="{{ $pengukuran->lingkar_kepala ?? '-' }}"
                                    data-lila="{{ $pengukuran->lingkar_lengan_atas ?? '-' }}"
                                    data-zscore="{{ $pengukuran->z_score ?? '-' }}"
                                    data-status="{{ $pengukuran->status_pertumbuhan ?? '-' }}"
                                    data-status-key="{{ $item->status_key }}"
                                    onclick="openVerifModal(this)"
                                >
                                    <i class="fa-solid fa-circle-check"></i>
                                    Verifikasi
                                </button>
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


{{-- ===================================================
     MODAL VERIFIKASI
     =================================================== --}}
<div class="verif-backdrop" id="verifBackdrop" aria-hidden="true"></div>

<div class="verif-modal" id="verifModal" role="dialog" aria-modal="true" aria-labelledby="verifModalTitle">

    <div class="verif-modal-inner">

        {{-- HEADER --}}
        <div class="verif-modal-header">
            <div class="verif-modal-icon">
                <i class="fa-solid fa-stethoscope"></i>
            </div>
            <div>
                <h2 class="verif-modal-title" id="verifModalTitle">Form Verifikasi Pengukuran</h2>
                <p class="verif-modal-subtitle">Tinjau data, tentukan tindak lanjut, dan tambahkan catatan penyuluhan.</p>
            </div>
            <button type="button" class="verif-modal-close" onclick="closeVerifModal()" aria-label="Tutup">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>


        {{-- DATA BALITA --}}
        <div class="verif-section-label">
            <i class="fa-solid fa-child"></i>
            Data Balita
        </div>

        <div class="verif-data-grid">

            <div class="verif-data-item">
                <div class="verif-data-label">Nama</div>
                <div class="verif-data-value" id="vNama">—</div>
            </div>

            <div class="verif-data-item">
                <div class="verif-data-label">NIK</div>
                <div class="verif-data-value" id="vNik">—</div>
            </div>

            <div class="verif-data-item">
                <div class="verif-data-label">Jenis Kelamin</div>
                <div class="verif-data-value" id="vGender">—</div>
            </div>

            <div class="verif-data-item">
                <div class="verif-data-label">Tanggal Lahir</div>
                <div class="verif-data-value" id="vTanggalLahir">—</div>
            </div>

        </div>


        {{-- DATA PENGUKURAN --}}
        <div class="verif-section-label" style="margin-top: 20px;">
            <i class="fa-solid fa-ruler"></i>
            Hasil Pengukuran Kader
        </div>

        <div class="verif-ukur-grid">

            <div class="verif-ukur-card">
                <div class="verif-ukur-icon"><i class="fa-solid fa-weight-scale"></i></div>
                <div class="verif-ukur-label">Berat Badan</div>
                <div class="verif-ukur-value" id="vBb">—</div>
                <div class="verif-ukur-unit">kg</div>
            </div>

            <div class="verif-ukur-card">
                <div class="verif-ukur-icon"><i class="fa-solid fa-arrows-up-down"></i></div>
                <div class="verif-ukur-label">PB / TB</div>
                <div class="verif-ukur-value" id="vTb">—</div>
                <div class="verif-ukur-unit">cm</div>
            </div>

            <div class="verif-ukur-card">
                <div class="verif-ukur-icon"><i class="fa-solid fa-circle-dot"></i></div>
                <div class="verif-ukur-label">Lingkar Kepala</div>
                <div class="verif-ukur-value" id="vLk">—</div>
                <div class="verif-ukur-unit">cm</div>
            </div>

            <div class="verif-ukur-card">
                <div class="verif-ukur-icon"><i class="fa-solid fa-expand"></i></div>
                <div class="verif-ukur-label">LILA</div>
                <div class="verif-ukur-value" id="vLila">—</div>
                <div class="verif-ukur-unit">cm</div>
            </div>

        </div>

        <div class="verif-status-row">
            <div class="verif-status-chip" id="vStatusChip">
                <i class="fa-solid fa-chart-line"></i>
                <span id="vStatusText">—</span>
            </div>
            <div class="verif-zscore" id="vZscore">Z-Score: —</div>
        </div>


        {{-- FORM VERIFIKASI --}}
        <form
            method="POST"
            id="verifForm"
            action=""
        >
            @csrf

            <div class="verif-section-label" style="margin-top: 20px;">
                <i class="fa-solid fa-clipboard-list"></i>
                Keputusan Verifikasi
            </div>


            {{-- TINDAK LANJUT --}}
            <div class="verif-field">
                <label class="verif-field-label" for="tindak_lanjut">
                    Tindak Lanjut <span class="req">*</span>
                </label>
                <div class="verif-tl-toggle">

                    <input type="radio" name="tindak_lanjut" id="tlTidakPerlu" value="tidak_perlu" checked>
                    <label for="tlTidakPerlu" class="tl-btn tl-no">
                        <i class="fa-solid fa-circle-check"></i>
                        Tidak Perlu Tindak Lanjut
                    </label>

                    <input type="radio" name="tindak_lanjut" id="tlPerlu" value="perlu">
                    <label for="tlPerlu" class="tl-btn tl-yes">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Perlu Tindak Lanjut
                    </label>

                </div>
            </div>


            {{-- CATATAN PENYULUHAN --}}
            <div class="verif-field">
                <label class="verif-field-label" for="catatan_penyuluhan">
                    Catatan Penyuluhan
                    <span class="verif-field-hint">Opsional</span>
                </label>
                <textarea
                    name="catatan_penyuluhan"
                    id="catatan_penyuluhan"
                    class="verif-textarea"
                    rows="4"
                    placeholder="Tuliskan catatan penyuluhan, saran gizi, atau rekomendasi tindak lanjut..."
                ></textarea>
            </div>


            {{-- ACTIONS --}}
            <div class="verif-modal-actions">

                <button type="button" class="verif-btn-cancel" onclick="closeVerifModal()">
                    <i class="fa-solid fa-xmark"></i>
                    Batal
                </button>

                <button type="submit" class="verif-btn-submit" id="verifSubmitBtn">
                    <i class="fa-solid fa-circle-check"></i>
                    Verifikasi Sekarang
                </button>

            </div>

        </form>

    </div>

</div>


@endsection


@push('styles')
    @vite('resources/css/kader/monitoringbalita.css')
@endpush


@push('scripts')
    @vite('resources/js/kader/monitoringbalita.js')

    <script>
        const verifRouteBase = '{{ url("bidan/verifikasi") }}';

        function openVerifModal(btn) {
            const id     = btn.dataset.pengukuranId;
            const gender = btn.dataset.gender;

            // Identitas
            document.getElementById('vNama').textContent        = btn.dataset.nama;
            document.getElementById('vNik').textContent         = btn.dataset.nik;
            document.getElementById('vGender').textContent      = gender === 'L' ? 'Laki-laki' : 'Perempuan';
            document.getElementById('vTanggalLahir').textContent = btn.dataset.tanggalLahir;

            // Pengukuran
            const bb   = btn.dataset.bb;
            const tb   = btn.dataset.tb;
            const lk   = btn.dataset.lk;
            const lila = btn.dataset.lila;

            document.getElementById('vBb').textContent   = bb   !== '-' ? bb   : '—';
            document.getElementById('vTb').textContent   = tb   !== '-' ? tb   : '—';
            document.getElementById('vLk').textContent   = lk   !== '-' ? lk   : '—';
            document.getElementById('vLila').textContent = lila !== '-' ? lila : '—';

            const statusText = btn.dataset.status;
            const statusKey  = btn.dataset.statusKey;
            document.getElementById('vStatusText').textContent = statusText;
            const chip = document.getElementById('vStatusChip');
            chip.className = 'verif-status-chip verif-status-chip--' + (statusKey || 'empty');

            const zscore = btn.dataset.zscore;
            document.getElementById('vZscore').textContent = zscore !== '-' ? 'Z-Score: ' + zscore : '';

            // Reset form
            document.getElementById('verifForm').action = verifRouteBase + '/' + id;
            document.getElementById('tlTidakPerlu').checked = true;
            document.getElementById('catatan_penyuluhan').value = '';

            // Open
            document.getElementById('verifBackdrop').classList.add('open');
            document.getElementById('verifModal').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeVerifModal() {
            document.getElementById('verifBackdrop').classList.remove('open');
            document.getElementById('verifModal').classList.remove('open');
            document.body.style.overflow = '';
        }

        document.getElementById('verifBackdrop').addEventListener('click', closeVerifModal);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeVerifModal();
        });
    </script>
@endpush
