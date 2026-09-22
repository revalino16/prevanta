@extends('kader.layouts.kader')

@section('title', 'Monitoring Balita — Prevanta')


@push('styles')

<style>

    /* ============ FILTER PANEL ============ */

    .filter-panel {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);

        padding: 16px 20px;

        margin-bottom: 20px;

        box-shadow: var(--shadow);
    }

    .filter-row {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 12px;
        flex-wrap: wrap;

        margin-bottom: 16px;
    }

    .tabs {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        background: var(--bg);
        padding: 4px;
        border-radius: 13px;
    }

    .tab {
        padding: 9px 16px;
        border-radius: 10px;

        font-size: 12.5px;
        font-weight: 700;

        color: var(--ink-soft);

        border: none;
        background: transparent;

        cursor: pointer;

        transition: .15s ease;
        white-space: nowrap;
    }

    .tab:hover {
        color: var(--wine-deep);
    }

    .tab.active {
        background: var(--card);
        color: var(--wine-deep);
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }

    .reset-link {
        font-size: 12.5px;
        color: var(--ink-faint);
        font-weight: 700;

        display: flex;
        align-items: center;
        gap: 6px;

        background: none;
        border: none;

        cursor: pointer;
    }

    .reset-link:hover {
        color: var(--wine);
    }

    .search-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 240px;
    }

    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--ink-faint);
        font-size: 13px;
    }

    .search-box input {
        width: 100%;
        padding: 12px 14px 12px 42px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: var(--bg);
        font-size: 13.5px;
        color: var(--ink);
        outline: none;
    }

    .search-box input:focus {
        border-color: var(--wine);
        background: var(--card);
    }

    #genderFilter {
        padding: 12px 16px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: var(--bg);
        font-size: 13.5px;
        font-weight: 600;
        color: var(--ink-soft);
        outline: none;
    }

    /* ============ LIST PANEL ============ */

    .monitoring-panel {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;

        padding: 16px 22px;

        background: var(--wine-tint);
        border-bottom: 1px solid var(--line);
    }

    .panel-title {
        font-size: 14.5px;
        font-weight: 800;
        color: var(--wine-deep);

        display: flex;
        align-items: center;
        gap: 9px;
    }

    .panel-title i {
        color: var(--wine);
    }

    .panel-count {
        font-size: 12.5px;
        color: var(--ink-faint);
        font-weight: 600;
    }

    .balita-list {
        width: 100%;
    }

    .balita-row {
        display: flex;
        border-bottom: 1px solid var(--line);
        transition: background .12s ease;
    }

    .balita-row:last-child {
        border-bottom: none;
    }

    .balita-row:hover {
        background: #FDFAF7;
    }

    .row-bar {
        width: 5px;
        flex-shrink: 0;
        background: var(--ink-faint);
    }

    .row-bar.bar-success { background: var(--green); }
    .row-bar.bar-warning { background: var(--amber); }
    .row-bar.bar-danger  { background: var(--red); }
    .row-bar.bar-info    { background: var(--blue); }

    .row-body {
        flex: 1;
        display: grid;
        grid-template-columns: 260px 1fr 200px 130px;
        gap: 22px;
        align-items: center;
        padding: 18px 22px;
        min-width: 0;
    }

    /* ---- kolom: identitas balita ---- */

    .who {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        min-width: 0;
    }

    .avatar-wrap {
        position: relative;
        flex-shrink: 0;
    }

    .avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;

        background: var(--wine-tint);
        color: var(--wine-deep);

        display: flex;
        align-items: center;
        justify-content: center;

        font-weight: 800;
        font-size: 16px;

        border: 1px solid var(--wine-tint-line);
    }

    .gender-flag {
        position: absolute;
        bottom: -2px;
        right: -2px;

        width: 17px;
        height: 17px;
        border-radius: 50%;

        font-size: 8.5px;
        font-weight: 800;
        color: #fff;

        display: flex;
        align-items: center;
        justify-content: center;

        border: 2px solid var(--card);
    }

    .gender-flag.flag-L { background: var(--blue); }
    .gender-flag.flag-P { background: var(--wine); }

    .who-name {
        font-size: 14.5px;
        font-weight: 800;
        color: var(--ink);

        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .who-nik {
        font-size: 11px;
        color: var(--ink-faint);
        font-weight: 600;
        letter-spacing: .01em;
        margin-top: 2px;
    }

    .who-meta {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-top: 7px;
        flex-wrap: wrap;
    }

    .gender-chip {
        background: var(--bg);
        color: var(--ink-soft);
        font-size: 10.5px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 999px;
        border: 1px solid var(--line);
    }

    .mother {
        font-size: 11px;
        color: var(--wine);
        font-weight: 700;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* ---- kolom: hasil pengukuran ---- */

    .measure {
        min-width: 0;
    }

    .measure .m-top {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        color: var(--ink-faint);
        font-weight: 600;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .measure .m-top .highlight {
        font-weight: 800;
        padding: 2px 9px;
        border-radius: 999px;
        background: var(--bg);
    }

    .m-vals {
        display: flex;
        gap: 22px;
        flex-wrap: wrap;
    }

    .m-vals .val-item {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .m-vals .val-label {
        font-size: 9.5px;
        color: var(--ink-faint);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .m-vals .val-num {
        font-size: 16px;
        font-weight: 800;
        color: var(--ink);
    }

    .m-vals .val-unit {
        font-size: 10.5px;
        font-weight: 600;
        color: var(--ink-faint);
    }

    /* ---- kolom: status & tindak lanjut ---- */

    .status-col {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;

        font-size: 12px;
        font-weight: 800;

        padding: 7px 14px;
        border-radius: 999px;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .status-badge.status-success { background: var(--green-tint); border-color: var(--green-tint-line); color: var(--green); }
    .status-badge.status-warning { background: var(--amber-tint); border-color: var(--amber-tint-line); color: var(--amber); }
    .status-badge.status-danger  { background: var(--red-tint);   border-color: var(--red-tint-line);   color: var(--red); }
    .status-badge.status-info    { background: var(--wine-tint);  border-color: var(--wine-tint-line);  color: var(--wine-deep); }
    .status-badge.status-empty   { background: #F2F0EE; color: var(--ink-faint); }

    .action-line {
        display: flex;
        align-items: center;
        gap: 6px;

        font-size: 11.5px;
        color: var(--ink-soft);
        font-weight: 700;
    }

    .action-line i {
        color: var(--wine);
        font-size: 10.5px;
    }

    /* ---- kolom: aksi ---- */

    .row-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }

    .btn-kms {
        display: inline-flex;
        align-items: center;
        gap: 7px;

        background: var(--wine-tint);
        color: var(--wine-deep);

        font-size: 11.5px;
        font-weight: 800;

        padding: 9px 14px;
        border-radius: 999px;
        border: none;
        white-space: nowrap;
    }

    .btn-kms:hover {
        background: var(--wine-tint-line);
    }

    .btn-edit {
        width: 30px;
        height: 30px;
        border-radius: 9px;

        background: var(--bg);
        color: var(--ink-soft);
        border: none;

        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-edit:hover {
        background: var(--line);
    }

    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: var(--ink-faint);
    }

    .empty-state i {
        font-size: 35px;
        margin-bottom: 15px;
        display: block;
    }

    .empty-state h3 {
        font-size: 15px;
        color: var(--ink);
        margin-bottom: 6px;
    }

    .empty-state p {
        font-size: 13.5px;
    }

    @media (max-width: 1100px) {

        .row-body {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .row-actions {
            flex-direction: row;
            align-items: center;
            justify-content: flex-end;
        }

        .status-col {
            flex-direction: row;
            align-items: center;
        }

    }

    @media (max-width: 950px) {

        .filter-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .tabs {
            width: 100%;
        }

        .search-row {
            width: 100%;
        }

        .search-box input {
            width: 100%;
        }

    }

</style>

@endpush


@section('content')

<main class="content">

    <div class="page-head">

        <div>

            <div class="eyebrow">
                MONITORING
            </div>

            <h1>
                Monitoring Balita
            </h1>

            <p>
                Pantau data dan perkembangan balita.
            </p>

        </div>


        <a
            href="{{ route('kader.balita.tambah') }}"
            class="btn btn-primary"
        >

            <i class="fa-solid fa-plus"></i>

            Tambah Balita

        </a>

    </div>


    @if (session('success'))

        <div class="success-message">
            {{ session('success') }}
        </div>

    @endif


    @php
        // Fungsi bantu murni untuk tampilan (warna badge/garis status)
        // berdasarkan teks status_pertumbuhan yang sudah ada — tidak mengubah data.
        $resolveStatusKey = function (?string $statusText) {
            if (!$statusText) {
                return null;
            }

            $lower = strtolower($statusText);

            if (str_contains($lower, 'sangat pendek') || str_contains($lower, 'sangat kurus')) {
                return 'danger';
            }

            if (str_contains($lower, 'pendek') || str_contains($lower, 'kurus') || str_contains($lower, 'kurang')) {
                return 'warning';
            }

            if (str_contains($lower, 'tinggi') || str_contains($lower, 'gemuk') || str_contains($lower, 'lebih')) {
                return 'info';
            }

            return 'success';
        };

        $highlightLabel = [
            'danger'  => 'Perlu Rujukan',
            'warning' => 'Perlu Pemantauan',
            'info'    => 'Perlu Pemantauan',
            'success' => 'Sesuai KMS',
        ];

        $countNormal = 0;
        $countPendek = 0;
        $countSangatPendek = 0;

        foreach ($balita as $b) {
            $p = $b->pengukuran->first();
            $key = $resolveStatusKey($p->status_pertumbuhan ?? null);

            if ($key === 'success') {
                $countNormal++;
            } elseif ($key === 'warning' || $key === 'info') {
                $countPendek++;
            } elseif ($key === 'danger') {
                $countSangatPendek++;
            }
        }
    @endphp


    {{-- FILTER PANEL --}}
    <div class="filter-panel">

        <div class="filter-row">

            <div class="tabs" id="tabs">

                <button type="button" class="tab active" data-status="">
                    Semua Status ({{ $balita->count() }})
                </button>

                <button type="button" class="tab" data-status="success">
                    Normal ({{ $countNormal }})
                </button>

                <button type="button" class="tab" data-status="warning">
                    Pendek ({{ $countPendek }})
                </button>

                <button type="button" class="tab" data-status="danger">
                    Sangat Pendek ({{ $countSangatPendek }})
                </button>

            </div>

            <button type="button" class="reset-link" id="resetFilter">
                <i class="fa-solid fa-rotate-left"></i> Reset Filter
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

                <option value="">Semua Gender</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>

            </select>

        </div>

    </div>


    {{-- LIST PANEL --}}
    <div class="monitoring-panel">

        <div class="panel-head">

            <div class="panel-title">
                <i class="fa-regular fa-face-smile"></i>
                Daftar Hasil Pengukuran Antropometri
            </div>

            <span class="panel-count" id="visibleCount">
                Menampilkan {{ $balita->count() }} Balita
            </span>

        </div>


        <div class="balita-list">

            @forelse ($balita as $item)

                @php
                    $pengukuran = $item->pengukuran->first();
                    $statusKey = $resolveStatusKey($pengukuran->status_pertumbuhan ?? null);
                @endphp


                <div
                    class="balita-row"
                    data-name="{{ strtolower($item->nama) }}"
                    data-nik="{{ $item->nik }}"
                    data-gender="{{ $item->jenis_kelamin }}"
                    data-status="{{ $statusKey }}"
                >

                    <span class="row-bar {{ $statusKey ? 'bar-'.$statusKey : '' }}"></span>

                    <div class="row-body">

                        {{-- BALITA --}}

                        <div class="who">

                            <div class="avatar-wrap">

                                <div class="avatar">
                                    {{ strtoupper(substr($item->nama, 0, 1)) }}
                                </div>

                                @if ($item->jenis_kelamin === 'L' || $item->jenis_kelamin === 'P')
                                    <span class="gender-flag flag-{{ $item->jenis_kelamin }}">
                                        {{ $item->jenis_kelamin }}
                                    </span>
                                @endif

                            </div>

                            <div style="min-width: 0;">

                                <div class="who-name">
                                    {{ $item->nama }}
                                </div>

                                <div class="who-nik">
                                    NIK: {{ $item->nik }}
                                </div>

                                <div class="who-meta">

                                    <span class="gender-chip">

                                        @if ($item->jenis_kelamin === 'L')
                                            Laki-laki
                                        @elseif ($item->jenis_kelamin === 'P')
                                            Perempuan
                                        @else
                                            -
                                        @endif

                                    </span>

                                </div>

                                @if (($item->orangTua->user->nama ?? null))
                                    <div class="mother">
                                        <i class="fa-solid fa-people-roof"></i>
                                        {{ $item->orangTua->user->nama }}
                                    </div>
                                @endif

                            </div>

                        </div>


                        {{-- HASIL PENGUKURAN --}}

                        <div class="measure">

                            <div class="m-top">

                                <i class="fa-regular fa-calendar"></i>

                                @if ($pengukuran)

                                    {{ $pengukuran->tanggal_pengukuran }}

                                    <span
                                        class="highlight"
                                        style="color: var({{ $statusKey === 'danger' ? '--red' : ($statusKey === 'warning' || $statusKey === 'info' ? '--amber' : '--green') }});"
                                    >
                                        {{ $highlightLabel[$statusKey] ?? '' }}
                                    </span>

                                @else

                                    Belum ada pengukuran

                                @endif

                            </div>

                            <div class="m-vals">

                                <div class="val-item">
                                    <div class="val-label">PB/TB</div>
                                    <div class="val-num">
                                        {{ $pengukuran->tinggi_badan ?? '-' }}
                                        @if ($pengukuran && $pengukuran->tinggi_badan !== null)
                                            <span class="val-unit">cm</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="val-item">
                                    <div class="val-label">BB</div>
                                    <div class="val-num">
                                        {{ $pengukuran->berat_badan ?? '-' }}
                                        @if ($pengukuran && $pengukuran->berat_badan !== null)
                                            <span class="val-unit">kg</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="val-item">
                                    <div class="val-label">LILA</div>
                                    <div class="val-num">
                                        {{ $pengukuran->lingkar_lengan_atas ?? '-' }}
                                        @if ($pengukuran && $pengukuran->lingkar_lengan_atas !== null)
                                            <span class="val-unit">cm</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="val-item">
                                    <div class="val-label">LK</div>
                                    <div class="val-num">
                                        {{ $pengukuran->lingkar_kepala ?? '-' }}
                                        @if ($pengukuran && $pengukuran->lingkar_kepala !== null)
                                            <span class="val-unit">cm</span>
                                        @endif
                                    </div>
                                </div>

                            </div>

                        </div>


                        {{-- STATUS + AKSI --}}

                        <div class="status-col">

                            @if ($pengukuran)

                                <span class="status-badge {{ $statusKey ? 'status-'.$statusKey : '' }}">

                                    @if ($statusKey === 'danger')
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    @elseif ($statusKey === 'success')
                                        <i class="fa-solid fa-circle-check"></i>
                                    @else
                                        <i class="fa-regular fa-circle-question"></i>
                                    @endif

                                    {{ $pengukuran->status_pertumbuhan ?? '-' }}

                                </span>

                            @else

                                <span class="status-badge status-empty">
                                    Belum diukur
                                </span>

                            @endif

                            @if ($pengukuran)
                                <div class="action-line">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                    {{ $highlightLabel[$statusKey] ?? 'Pemantauan Rutin' }}
                                </div>
                            @endif

                        </div>


                        <div class="row-actions">

                            <a href="#" class="btn-kms">
                                <i class="fa-solid fa-chart-line"></i> Lihat KMS
                            </a>

                            <a
                                href="{{ route('kader.balita.edit', $item->id) }}"
                                class="btn-edit"
                                title="Edit"
                            >
                                <i class="fa-solid fa-pen" style="font-size: 11px;"></i>
                            </a>

                        </div>

                    </div>

                </div>

            @empty

                <div class="empty-state">

                    <i class="fa-solid fa-child"></i>

                    <h3>Belum ada data balita</h3>

                    <p>Data balita yang terdaftar akan ditampilkan di sini.</p>

                </div>

            @endforelse

        </div>

    </div>

</main>

@endsection


@push('scripts')

<script>

    const searchInput  = document.getElementById('searchInput');
    const genderFilter = document.getElementById('genderFilter');
    const tabs          = document.querySelectorAll('.tab');
    const resetFilter    = document.getElementById('resetFilter');
    const visibleCount   = document.getElementById('visibleCount');

    let activeStatus = '';

    function filterBalita() {

        const search = searchInput.value.toLowerCase().trim();
        const gender = genderFilter.value;

        const rows = document.querySelectorAll('.balita-row');

        let visible = 0;

        rows.forEach(row => {

            const name      = row.dataset.name   || '';
            const nik       = row.dataset.nik    || '';
            const rowGender = row.dataset.gender || '';
            const rowStatus = row.dataset.status || '';

            const matchSearch = name.includes(search) || nik.includes(search);
            const matchGender = gender === '' || rowGender === gender;
            const matchStatus = activeStatus === '' || rowStatus === activeStatus;

            const show = matchSearch && matchGender && matchStatus;

            row.style.display = show ? 'flex' : 'none';

            if (show) visible++;

        });

        if (visibleCount) {
            visibleCount.textContent = `Menampilkan ${visible} Balita`;
        }

    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            activeStatus = tab.dataset.status;
            filterBalita();
        });
    });

    if (resetFilter) {
        resetFilter.addEventListener('click', () => {
            searchInput.value = '';
            genderFilter.value = '';
            activeStatus = '';
            tabs.forEach(t => t.classList.remove('active'));
            tabs[0].classList.add('active');
            filterBalita();
        });
    }

    searchInput.addEventListener('input', filterBalita);
    genderFilter.addEventListener('change', filterBalita);

</script>

@endpush
