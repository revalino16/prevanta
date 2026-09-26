@extends('kader.layouts.kader')

@section('title', 'Jadwal Posyandu — Prevanta')

@push('styles')
    @vite('resources/css/kader/jadwal.css')
@endpush

@section('content')

<main class="content">

    {{-- PAGE HEADER --}}
    <div class="page-head">
        <div>
            <div class="eyebrow">Manajemen Posyandu</div>
            <h1>Jadwal Kegiatan</h1>
            <p>
                Kelola jadwal pelayanan posyandu bulanan, edukasi, dan imunisasi. 
                Jadwal yang Anda buat akan terlihat oleh orang tua balita.
            </p>
        </div>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))
        <div class="success-message">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if ($errors->any())
        <div style="background: #fee2e2; color: #b91c1c; padding: 16px; border-radius: 12px; margin-bottom: 24px; border: 1px solid #f87171; display: flex; align-items: flex-start; gap: 12px; font-size: 0.95rem;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-top: 4px; font-size: 1.1rem;"></i>
            <div>
                <strong style="display: block; margin-bottom: 4px; color: #991b1b;">Terdapat Kesalahan Input:</strong>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- STAT CARDS --}}
    <div class="jadwal-stats">
        
        <div class="jstat-card">
            <div class="jstat-icon wine">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
            <div>
                <div class="jstat-label">Total Jadwal Bulan Ini</div>
                <div class="jstat-value">{{ $totalBulanIni }}</div>
                <div class="jstat-sub">Kegiatan Terdaftar</div>
            </div>
        </div>

        <div class="jstat-card">
            <div class="jstat-icon blue">
                <i class="fa-solid fa-bullhorn"></i>
            </div>
            <div>
                <div class="jstat-label">Kegiatan Mendatang</div>
                <div class="jstat-value">{{ $jadwalMendatang->count() }}</div>
                <div class="jstat-sub">Mulai Hari Ini</div>
            </div>
        </div>

        <div class="jstat-card">
            <div class="jstat-icon green">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <div>
                <div class="jstat-label">Kegiatan Selesai</div>
                <div class="jstat-value">{{ $sudahLewat }}</div>
                <div class="jstat-sub">Bulan Ini</div>
            </div>
        </div>

    </div>

    {{-- MAIN GRID --}}
    <div class="jadwal-grid">
        
        {{-- KIRI: DAFTAR JADWAL --}}
        <div class="jadwal-col-left">

            {{-- PANEL: JADWAL MENDATANG --}}
            <div class="jadwal-panel" style="margin-bottom: 24px;">
                <div class="jadwal-panel-head">
                    <div class="jadwal-panel-title">
                        <i class="fa-solid fa-clock"></i>
                        Agenda &amp; Jadwal Mendatang
                    </div>
                    <div class="jadwal-panel-badge">
                        {{ $jadwalMendatang->count() }} Kegiatan
                    </div>
                </div>

                <div class="jadwal-list">
                    @forelse ($jadwalMendatang as $item)
                        
                        @php
                            $isToday = $item->tanggal->isToday();
                        @endphp

                        <div class="jadwal-item {{ $isToday ? 'is-today' : '' }}">
                            
                            {{-- Tanggal Box --}}
                            <div class="jadwal-date-col">
                                <div class="jdate-day">{{ $item->tanggal->format('d') }}</div>
                                <div class="jdate-mon">{{ $item->tanggal->isoFormat('MMM') }}</div>
                            </div>

                            {{-- Informasi --}}
                            <div class="jadwal-info">
                                <div class="jadwal-jenis">
                                    {{ $item->jenis_kegiatan }}
                                    @if ($isToday)
                                        <span class="today-chip">HARI INI</span>
                                    @endif
                                </div>
                                <div class="jadwal-meta">
                                    <span>
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ $item->tanggal->isoFormat('dddd, D MMMM YYYY') }}
                                    </span>
                                    @if ($item->lokasi)
                                    <span>
                                        <i class="fa-solid fa-location-dot"></i>
                                        {{ $item->lokasi }}
                                    </span>
                                    @endif
                                </div>
                                @if ($item->keterangan)
                                    <div class="jadwal-desc">
                                        {{ $item->keterangan }}
                                    </div>
                                @endif
                            </div>

                            {{-- Action --}}
                            <div class="jadwal-item-actions">
                                <form action="{{ route('kader.jadwal.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-hapus" title="Hapus Jadwal">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>

                        </div>
                    @empty
                        <div class="jadwal-empty">
                            <i class="fa-regular fa-calendar-xmark"></i>
                            <p>Belum ada jadwal kegiatan mendatang.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- PANEL: JADWAL LEWAT --}}
            @if ($jadwalLewat->count() > 0)
                <div class="jadwal-panel">
                    <div class="jadwal-panel-head" style="background: var(--bg);">
                        <div class="jadwal-panel-title" style="color: var(--ink-soft);">
                            <i class="fa-solid fa-check-double" style="color: var(--ink-faint);"></i>
                            Riwayat Kegiatan Bulan Ini
                        </div>
                    </div>

                    <div class="jadwal-list" style="opacity: 0.75;">
                        @foreach ($jadwalLewat as $item)
                            <div class="jadwal-item">
                                <div class="jadwal-date-col">
                                    <div class="jdate-day">{{ $item->tanggal->format('d') }}</div>
                                    <div class="jdate-mon">{{ $item->tanggal->isoFormat('MMM') }}</div>
                                </div>
                                <div class="jadwal-info">
                                    <div class="jadwal-jenis">
                                        {{ $item->jenis_kegiatan }}
                                        <span class="lewat-chip">SELESAI</span>
                                    </div>
                                    <div class="jadwal-meta">
                                        <span><i class="fa-regular fa-calendar"></i> {{ $item->tanggal->isoFormat('dddd, D MMMM YYYY') }}</span>
                                    </div>
                                </div>
                                <div class="jadwal-item-actions">
                                    <form action="{{ route('kader.jadwal.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-hapus" title="Hapus Jadwal">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- KANAN: FORM TAMBAH JADWAL --}}
        <div class="jadwal-col-right">
            
            <div class="form-panel">
                
                <div class="form-panel-head">
                    <div class="form-panel-title">Buat Jadwal Baru</div>
                    <div class="form-panel-sub">Tambahkan kegiatan Posyandu baru</div>
                </div>

                <form action="{{ route('kader.jadwal.store') }}" method="POST" class="form-body">
                    @csrf

                    <div class="form-group">
                        <label for="jenis_kegiatan" class="form-label">Nama/Jenis Kegiatan <span>*</span></label>
                        <input type="text" name="jenis_kegiatan" id="jenis_kegiatan" class="form-input" placeholder="Misal: Posyandu Balita & Imunisasi" required>
                    </div>

                    <div class="form-group">
                        <label for="tanggal" class="form-label">Tanggal Pelaksanaan <span>*</span></label>
                        <input type="date" name="tanggal" id="tanggal" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="lokasi" class="form-label">Lokasi Posyandu</label>
                        <input type="text" name="lokasi" id="lokasi" class="form-input" placeholder="Misal: Posko RW 03 Mawar Melati">
                    </div>

                    <div class="form-group">
                        <label for="keterangan" class="form-label">Keterangan Tambahan</label>
                        <textarea name="keterangan" id="keterangan" class="form-textarea" placeholder="Catatan untuk orang tua (contoh: Bawa KMS dan fotokopi KK)"></textarea>
                    </div>

                    <button type="submit" class="btn-tambah">
                        <i class="fa-solid fa-plus"></i>
                        Simpan Jadwal
                    </button>

                </form>

            </div>

        </div>

    </div>

</main>

@endsection
