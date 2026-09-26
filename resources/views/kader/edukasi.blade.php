@extends('kader.layouts.kader')

@section('title', 'Edukasi Tumbuh Kembang — Prevanta')

@push('styles')
    @vite('resources/css/kader/edukasi.css')
@endpush

@section('content')

<main class="content edukasi-page">

    {{-- SUCCESS ALERT --}}
    @if (session('success'))
        <div style="background: #eaf6ee; border: 1px solid #c8ebd2; color: #1e6b39; padding: 14px 20px; border-radius: 16px; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <i class="fa-solid fa-circle-check" style="font-size: 16px; color: #2fa36b;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ══ TOP HEADER ═════════════════════════════════════════════ --}}
    <div class="edukasi-head">
        <div class="edukasi-head-left">
            <div class="edukasi-badge">
                <i class="fa-solid fa-heart-pulse"></i>
                Panduan Orang Tua &amp; Kader
            </div>

            <h1 class="edukasi-title">Edukasi Tumbuh Kembang</h1>

            <p class="edukasi-sub">
                Informasi terverifikasi seputar kesehatan, pemenuhan nutrisi, dan pemantauan balita.
            </p>
        </div>

        <div>
            <a href="{{ route('kader.edukasi.create') }}" class="btn-input-edukasi">
                <i class="fa-solid fa-circle-plus"></i>
                <span>Input Materi Edukasi</span>
            </a>
        </div>
    </div>


    {{-- ══ FILTER PILLS ═══════════════════════════════════════════ --}}
    <div class="filter-bar">
        @foreach ($kategoriList as $kat)
            @php
                $isActive = ($selectedKategori === $kat) || (empty($selectedKategori) && $kat === 'Semua Topik');
                $url = route('kader.edukasi', array_merge(request()->except('page'), ['kategori' => $kat]));
            @endphp
            <a
                href="{{ $url }}"
                class="filter-pill {{ $isActive ? 'active' : '' }}"
            >
                {{ $kat }}
            </a>
        @endforeach
    </div>


    {{-- ══ SEARCH INPUT ═══════════════════════════════════════════ --}}
    <form method="GET" action="{{ route('kader.edukasi') }}" id="searchForm">
        @if (request('kategori') && request('kategori') !== 'Semua Topik')
            <input type="hidden" name="kategori" value="{{ request('kategori') }}">
        @endif

        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari materi edukasi..."
                class="search-input"
                id="searchEduInput"
            >
        </div>
    </form>


    {{-- ══ SECTION TITLE ══════════════════════════════════════════ --}}
    <div class="section-head">
        <div class="section-title">
            <i class="fa-solid fa-book-open"></i>
            <span>Artikel &amp; Panduan Bunda</span>
        </div>

        <div class="section-count">
            {{ $edukasiList->count() }} Panduan Rekomendasi
        </div>
    </div>


    {{-- ══ ARTICLE CARDS GRID ════════════════════════════════════ --}}
    @if ($edukasiList->count() > 0)
        <div class="articles-grid">
            @foreach ($edukasiList as $item)
                @php
                    // Deteksi warna dan label badge
                    $katLower = strtolower($item->kategori ?? '');
                    $judulLower = strtolower($item->judul ?? '');

                    $badgeClass = 'default';
                    $badgeLabel = $item->kategori ?: 'Panduan';

                    if (str_contains($katLower, 'imunisasi') || str_contains($judulLower, 'imunisasi')) {
                        $badgeClass = 'imunisasi';
                        $badgeLabel = 'Imunisasi & Vaksin';
                    } elseif (str_contains($katLower, 'gizi') || str_contains($judulLower, 'gizi')) {
                        $badgeClass = 'gizi';
                        $badgeLabel = 'Gizi Seimbang';
                    } elseif (str_contains($katLower, 'gtm') || str_contains($judulLower, 'gtm')) {
                        $badgeClass = 'gtm';
                        $badgeLabel = 'Tips Makan (GTM)';
                    } elseif (str_contains($katLower, 'kebersihan') || str_contains($judulLower, 'kebersihan') || str_contains($judulLower, 'diare')) {
                        $badgeClass = 'sanitasi';
                        $badgeLabel = 'Sanitasi & Higienitas';
                    } elseif (str_contains($judulLower, 'tidur') || str_contains($katLower, 'tidur')) {
                        $badgeClass = 'istirahat';
                        $badgeLabel = 'Pola Istirahat';
                    } elseif (str_contains($katLower, 'stimulasi') || str_contains($judulLower, 'stimulasi')) {
                        $badgeClass = 'stimulasi';
                        $badgeLabel = 'Stimulasi Balita';
                    }

                    // Tentukan gambar
                    $imgUrl = $item->gambar;
                    if (empty($imgUrl)) {
                        $imgUrl = 'https://images.unsplash.com/photo-1544126592-807ade215a0b?auto=format&fit=crop&w=800&q=80';
                    } elseif (!str_starts_with($imgUrl, 'http')) {
                        $imgUrl = asset($imgUrl);
                    }

                    // Waktu baca perkiraan (kata / 60 kata per menit)
                    $wordCount = str_word_count(strip_tags($item->konten));
                    $readMinutes = max(2, ceil($wordCount / 60));
                @endphp

                <article class="article-card">
                    {{-- THUMBNAIL --}}
                    <div class="article-thumb-wrap">
                        <img
                            src="{{ $imgUrl }}"
                            alt="{{ $item->judul }}"
                            class="article-thumb"
                            loading="lazy"
                        >
                        <span class="thumb-badge {{ $badgeClass }}">
                            {{ $badgeLabel }}
                        </span>
                    </div>

                    {{-- BODY --}}
                    <div class="article-body">
                        <h2 class="article-card-title" title="{{ $item->judul }}">
                            {{ $item->judul }}
                        </h2>

                        <p class="article-card-desc">
                            {{ Str::limit(strip_tags($item->konten), 125, '...') }}
                        </p>

                        {{-- FOOTER --}}
                        <div class="article-footer">
                            <span class="read-time">
                                <i class="fa-regular fa-clock"></i>
                                {{ $readMinutes }} Menit Baca
                            </span>

                            <button
                                type="button"
                                class="read-link btn-read-modal"
                                data-title="{{ $item->judul }}"
                                data-kategori="{{ $item->kategori }}"
                                data-badge="{{ $badgeLabel }}"
                                data-badge-class="{{ $badgeClass }}"
                                data-img="{{ $imgUrl }}"
                                data-content="{{ $item->konten }}"
                                data-time="{{ $readMinutes }} Menit Baca"
                                data-id="{{ $item->id }}"
                            >
                                <span>Baca Selengkapnya</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fa-solid fa-book-open-reader"></i>
            <h3>Tidak Ada Materi Edukasi</h3>
            <p>Belum ada artikel untuk kategori atau pencarian ini. Coba pilih topik lain atau tambahkan materi baru.</p>
            <div style="margin-top: 18px;">
                <a href="{{ route('kader.edukasi') }}" class="filter-pill" style="display:inline-block;">Reset Filter</a>
            </div>
        </div>
    @endif

</main>


{{-- ══ MODAL DETAIL BACA ════════════════════════════════════════ --}}
<div class="modal-backdrop" id="modalDetail">
    <div class="modal-card">
        <div class="modal-header">
            <span class="thumb-badge" id="modalBadge">Kategori</span>
            <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            <img src="" alt="" class="modal-img" id="modalImg">

            <h2 class="modal-title" id="modalTitle"></h2>

            <div class="modal-meta">
                <span id="modalTime"><i class="fa-regular fa-clock"></i> 2 Menit Baca</span>
                <span>&bull;</span>
                <span>Posyandu Mawar Melati</span>
            </div>

            <div class="modal-content-text" id="modalContent"></div>

            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #ECE3D8; display: flex; justify-content: space-between; align-items: center;">
                <form id="deleteForm" method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi edukasi ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: #dc2626; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-regular fa-trash-can"></i>
                        Hapus Materi
                    </button>
                </form>

                <button type="button" class="filter-pill" id="modalCloseBtn2" style="padding: 6px 18px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Modal Element Handlers
    const modalBackdrop = document.getElementById('modalDetail');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const modalCloseBtn2 = document.getElementById('modalCloseBtn2');
    const modalTitle = document.getElementById('modalTitle');
    const modalBadge = document.getElementById('modalBadge');
    const modalImg = document.getElementById('modalImg');
    const modalContent = document.getElementById('modalContent');
    const modalTime = document.getElementById('modalTime');
    const deleteForm = document.getElementById('deleteForm');

    const openModal = (data) => {
        modalTitle.textContent = data.title;
        modalBadge.textContent = data.badge;
        modalBadge.className = 'thumb-badge ' + data.badgeClass;
        modalImg.src = data.img;
        modalImg.alt = data.title;
        modalContent.textContent = data.content;
        modalTime.innerHTML = '<i class="fa-regular fa-clock"></i> ' + data.time;
        deleteForm.action = '{{ url("/kader/edukasi") }}/' + data.id;

        modalBackdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modalBackdrop.classList.remove('show');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('.btn-read-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal({
                id: btn.dataset.id,
                title: btn.dataset.title,
                kategori: btn.dataset.kategori,
                badge: btn.dataset.badge,
                badgeClass: btn.dataset.badgeClass,
                img: btn.dataset.img,
                content: btn.dataset.content,
                time: btn.dataset.time
            });
        });
    });

    modalCloseBtn?.addEventListener('click', closeModal);
    modalCloseBtn2?.addEventListener('click', closeModal);

    modalBackdrop?.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalBackdrop.classList.contains('show')) {
            closeModal();
        }
    });
});
</script>
@endpush
