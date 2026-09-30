{{--
    resources/views/orangtua/riwayat.blade.php

    Dikirim dari AnakkuController@riwayat:
      $anak       -> anak terpilih (null jika orang tua belum punya anak)
      $daftarAnak -> semua anak milik orang tua (untuk dropdown "Ganti Anak")
      $riwayat    -> pemeriksaan antropometri, terbaru di atas
      $imunisasi  -> imunisasi & vitamin

    Sidebar / navbar / topbar sudah dari layout, jadi tidak ada di file ini.
--}}
@extends('orangtua.layouts.orangtua') {{-- TODO: samakan dengan @extends di anakku.blade.php --}}

@section('content')
@php
    // Format angka Indonesia: 77,8
    $fmt = fn ($n, $d = 1) => number_format((float) $n, $d, ',', '.');
    // Z-score: merah bila di bawah -2 SD atau di atas +3 SD, hijau bila normal
    $zClass = fn ($z) => $z === null ? '' : (($z < -2 || $z > 3) ? 'rw-text-danger' : 'rw-text-ok');
    $zLabel = fn ($z) => $z === null ? '-' : ($z >= 0 ? '+' : '') . number_format((float) $z, 2, '.', '') . ' SD';
    // Warna badge status
    $badge = fn ($s) => match (true) {
        str_contains(strtolower($s), 'sangat pendek'), str_contains(strtolower($s), 'buruk') => 'rw-badge-danger',
        str_contains(strtolower($s), 'pendek'), str_contains(strtolower($s), 'kurang'), str_contains(strtolower($s), 'waspada') => 'rw-badge-warn',
        default => 'rw-badge-ok',
    };
@endphp
@include('orangtua.partials.riwayat-style')

<div class="rw-app">
<div class="rw-content">
@if(!$anak)
    <div class="rw-empty">
        <b>Belum ada data anak</b>
        Data anak Anda belum terdaftar. Silakan hubungi kader posyandu.
    </div>
@else
    <div class="rw-head">
        <div>
            <h1>Riwayat Anak</h1><span class="rw-tag">Mode Pantau (Read-Only)</span>
            <p>Pantau rekapitulasi pengukuran antropometri, status imunisasi, dan pemberian vitamin berkala buah hati Anda.</p>
        </div>
        @if(!empty($anak->status_pendampingan))
        <div class="rw-pendampingan">
            <span class="rw-dot"></span>
            <div>Status Pendampingan<b>{{ $anak->status_pendampingan }}</b></div>
        </div>
        @endif
    </div>

    {{-- ---------- Anak terpilih ---------- --}}
    <section class="rw-child">
        <div class="rw-child-row">
            <div class="rw-child-photo">
                <img src="{{ $anak->foto }}" alt="Foto {{ $anak->nama }}">
                <span>{{ $anak->is_laki ? '♂L' : '♀P' }}</span>
            </div>
            <div class="rw-child-info">
                <small>Anak yang Dipilih:</small>
                <small class="rw-meta">{{ $anak->usia_bulan }} Bulan • {{ $anak->jk_label }}</small>
                <h2>{{ $anak->nama }}</h2>
                <div class="rw-nik">NIK: <code>{{ $anak->nik }}</code> &nbsp;•&nbsp; {{ $anak->posyandu_nama ?? '-' }}</div>
            </div>

            @if($daftarAnak->count() > 1)
            <div class="rw-switch" id="switch">
                <button type="button" aria-haspopup="true" aria-expanded="false">
                    <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h8M8 17h5"/></svg>
                    Ganti Anak
                    <svg viewBox="0 0 24 24" style="width:12px;height:12px"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <ul>
                    @foreach($daftarAnak as $a)
                        <li><a href="{{ request()->fullUrlWithQuery(['anak' => $a->id]) }}" class="{{ $a->id == $anak->id ? 'rw-on' : '' }}">{{ $a->nama }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        <div class="rw-child-note">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            Data yang ditampilkan di bawah ini khusus untuk {{ $anak->nama }}.
        </div>
    </section>

    {{-- ---------- Tab ---------- --}}
    <div class="rw-tabs" role="tablist">
        <button type="button" class="rw-on" role="tab" data-tab="antropometri" aria-selected="true">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 17v-5M12 17V8M16 17v-3"/></svg>
            Pengukuran Antropometri
        </button>
        <button type="button" role="tab" data-tab="imunisasi" aria-selected="false">
            <svg viewBox="0 0 24 24"><path d="M18 2l4 4M20 4l-9 9M15 9l-9 9-1 3 3-1 9-9M9 15l-2-2"/></svg>
            Imunisasi &amp; Vitamin
        </button>
    </div>

    {{-- ---------- Panel: Antropometri ---------- --}}
    <div class="rw-panel" id="tab-antropometri">
        @forelse($riwayat as $p)
            @php
                $tbAlert = $p->z_tb_u !== null && ($p->z_tb_u < -2 || $p->z_tb_u > 3);
            @endphp
            <article class="rw-exam">
                <div class="rw-exam-head">
                    <div class="rw-exam-title">
                        <span class="rw-exam-ico"><svg viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="8" rx="1.5"/><path d="M7 8v3M11 8v4M15 8v3M19 8v4"/></svg></span>
                        <div>
                            <h3>Pemeriksaan Usia {{ $p->usia_bulan }} Bulan</h3>
                            <small>Tanggal Pemeriksaan: {{ \Carbon\Carbon::parse($p->tanggal_pemeriksaan)->locale('id')->translatedFormat('d F Y') }} • {{ $p->posyandu_nama ?? $anak->posyandu_nama ?? '-' }}</small>
                        </div>
                    </div>
                    @if(!empty($p->verifikator))
                        <div class="rw-verif">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>
                            Status: Terverifikasi oleh: <i>{{ $p->verifikator }}</i>
                        </div>
                    @else
                        <div class="rw-verif rw-pending">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                            Status: Menunggu verifikasi bidan
                        </div>
                    @endif
                </div>

                <div class="rw-grid">
                    {{-- Tinggi badan --}}
                    <div class="rw-tile {{ $tbAlert ? 'rw-alert' : '' }}">
                        <div class="rw-tile-label">Tinggi Badan (TB)
                            <svg viewBox="0 0 24 24"><path d="M12 4v16M8 8l4-4 4 4M8 16l4 4 4-4"/></svg>
                        </div>
                        <div class="rw-val">{{ $fmt($p->tinggi_badan) }}<small>cm</small></div>
                        <div class="rw-row"><span>Z-Score TB/U</span><b class="{{ $zClass($p->z_tb_u) }}">{{ $zLabel($p->z_tb_u) }}</b></div>
                        <span class="rw-badge {{ $badge($p->status_tb_u) }}">{{ $p->status_tb_u }}</span>
                    </div>

                    {{-- Berat badan --}}
                    <div class="rw-tile">
                        <div class="rw-tile-label">Berat Badan (BB)
                            <svg viewBox="0 0 24 24"><path d="M6 3h12M6 21h12M7 3c0 5 10 5 10 9s-10 4-10 9M17 3c0 5-10 5-10 9s10 4 10 9"/></svg>
                        </div>
                        <div class="rw-val">{{ $fmt($p->berat_badan, 2) }}<small>kg</small></div>
                        <div class="rw-row"><span>Z-Score BB/U</span><b class="{{ $zClass($p->z_bb_u) }}">{{ $zLabel($p->z_bb_u) }}</b></div>
                        <span class="rw-badge {{ $badge($p->status_bb_u) }}">{{ $p->status_bb_u }}</span>
                    </div>

                    {{-- LiLA --}}
                    <div class="rw-tile">
                        <div class="rw-tile-label">Lingkar Lengan (LiLA)
                            <svg viewBox="0 0 24 24"><path d="M5 19L19 5M5 19v-5M5 19h5M19 5v5M19 5h-5"/></svg>
                        </div>
                        <div class="rw-val">{{ $fmt($p->lila) }}<small>cm</small></div>
                        <div class="rw-row"><span>Status LiLA</span><b class="{{ strtolower($p->warna_lila ?? '') === 'merah' ? 'rw-text-danger' : (strtolower($p->warna_lila ?? '') === 'hijau' ? 'rw-text-ok' : '') }}">{{ $p->warna_lila ?? 'Kuning' }}</b></div>
                        <span class="rw-badge {{ $badge($p->status_lila) }}">{{ $p->status_lila }}</span>
                    </div>

                    {{-- Lingkar kepala --}}
                    <div class="rw-tile">
                        <div class="rw-tile-label">Lingkar Kepala (LK)
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
                        </div>
                        <div class="rw-val">{{ $fmt($p->lingkar_kepala) }}<small>cm</small></div>
                        <div class="rw-row"><span>Klasifikasi</span><b class="rw-text-ok">{{ $p->klasifikasi_lk ?? 'Ideal' }}</b></div>
                        <span class="rw-badge {{ $badge($p->status_lk) }}">{{ $p->status_lk }}</span>
                    </div>
                </div>
            </article>
        @empty
            <div class="rw-empty">
                <b>Belum ada riwayat pengukuran</b>
                Data akan muncul setelah kader posyandu mencatat pemeriksaan pertama {{ $anak->nama }}.
            </div>
        @endforelse

        @if(method_exists($riwayat, 'links'))
            {{ $riwayat->links() }}
        @endif
    </div>

    {{-- ---------- Panel: Imunisasi & Vitamin ---------- --}}
    <div class="rw-panel" id="tab-imunisasi" hidden>
        @if(!empty($imunisasi) && count($imunisasi))
            <div class="rw-imun">
                @foreach($imunisasi as $i)
                    <div class="rw-imun-item">
                        <div>
                            <b>{{ $i->nama }}</b>
                            <small>{{ $i->tanggal ? \Carbon\Carbon::parse($i->tanggal)->locale('id')->translatedFormat('d F Y') : 'Dijadwalkan usia '.$i->usia_target.' bulan' }}</small>
                        </div>
                        <span class="rw-pill {{ $i->tanggal ? 'rw-done' : 'rw-todo' }}">{{ $i->tanggal ? 'Sudah diberikan' : 'Belum diberikan' }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rw-empty">
                <b>Belum ada data imunisasi &amp; vitamin</b>
                Riwayat imunisasi dan vitamin A akan tampil di sini setelah dicatat oleh kader.
            </div>
        @endif
    </div>
@endif
</div>
</div>

<script>
    // Tab Antropometri / Imunisasi
    document.querySelectorAll('.rw-tabs button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.rw-tabs button').forEach(b => {
                const on = b === btn;
                b.classList.toggle('rw-on', on);
                b.setAttribute('aria-selected', on);
            });
            document.querySelectorAll('.rw-panel').forEach(p => p.hidden = p.id !== 'tab-' + btn.dataset.tab);
        });
    });

    // Dropdown "Ganti Anak"
    const sw = document.getElementById('switch');
    if (sw) {
        const trigger = sw.querySelector('button');
        trigger.addEventListener('click', e => {
            e.stopPropagation();
            const open = sw.classList.toggle('rw-open');
            trigger.setAttribute('aria-expanded', open);
        });
        document.addEventListener('click', () => sw.classList.remove('rw-open'));
    }
</script>
@endsection
