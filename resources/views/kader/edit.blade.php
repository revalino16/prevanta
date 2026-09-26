@extends('kader.layouts.kader')

@section('title', 'Ubah Balita — Prevanta')

@push('styles')
    @vite('resources/css/kader/create.css')
@endpush

@section('content')

    <main class="content">

        <a href="{{ route('kader.monitoringbalita') }}" class="back-link">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke Daftar Balita
        </a>

        <div class="page-head">

            <div>
                <h1>Ubah Balita</h1>

                <p>
                    Ubah data balita yang sudah terdaftar.
                </p>
            </div>

            <span class="session-chip">
                <i class="fa-solid fa-shield-halved"></i>
                Posyandu Mawar Melati - Desa Sukamaju &bull; Sesi Aktif Kader
            </span>

        </div>

        @if (session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        <div class="form-panel">

            <div class="form-panel-head">

                <div class="icon-circle">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>

                <div>
                    <h2>Formulir Ubah Data Balita</h2>

                    <p>
                        Perbarui data identitas anak secara akurat untuk integrasi buku KMS digital dan pemantauan gizi
                        terpadu.
                    </p>
                </div>

            </div>

            <form action="{{ route('kader.balita.update', $balita->id) }}" method="POST" id="tambahBalitaForm">

                @csrf
                @method('PUT')

                {{-- NAMA BALITA --}}
                <div class="form-group">

                    <div class="form-label-row">
                        <label>
                            Nama Balita <span class="req">*</span>
                        </label>

                        <span class="field-hint-top">
                            Sesuai Dokumen Resmi
                        </span>
                    </div>

                    <input type="text" name="nama" placeholder="Contoh: Muhammad Rayyan Al-Ghifari"
                        value="{{ old('nama', $balita->nama) }}" required>

                    <div class="form-hint">
                        <i class="fa-regular fa-circle-question"></i>
                        Nama sesuai dengan akta kelahiran atau Kartu Keluarga (KK).
                    </div>

                </div>

                {{-- NIK BALITA --}}
                <div class="form-group">

                    <div class="form-label-row">
                        <label>
                            NIK Balita <span class="req">*</span>
                        </label>

                        <span class="field-hint-top" id="nikCounter">
                            0/16 Digit
                        </span>
                    </div>

                    <div class="input-icon-wrap">

                        <i class="fa-solid fa-id-card leading"></i>

                        <input type="text" name="nik" id="nikInput" placeholder="Masukkan NIK balita (16 digit)"
                            maxlength="16" inputmode="numeric" value="{{ old('nik', $balita->nik) }}" required>

                    </div>

                    <div class="form-hint">
                        <i class="fa-regular fa-circle-question"></i>
                        Nomor Induk Kependudukan 16 digit. Bila belum memiliki NIK, gunakan NIK Kepala Keluarga sementara.
                    </div>

                </div>

                {{-- PILIH ORANG TUA --}}
                <div class="form-group">

                    <div class="form-label-row">
                        <label>
                            Pilih Orang Tua <span class="req">*</span>
                        </label>

                        <a href="#" class="link-add">
                            <i class="fa-solid fa-user-plus"></i>
                            Daftarkan Orang Tua Baru
                        </a>
                    </div>

                    @php
                        $selectedOrtu = $orangTua->where('id', old('orang_tua_id', $balita->orang_tua_id))->first();
                    @endphp

                    <div class="ortu-search-box">

                        <i class="fa-solid fa-magnifying-glass leading"></i>

                        <input type="text" id="ortuSearchInput" placeholder="Cari nama orang tua..." autocomplete="off"
                            value="{{ $selectedOrtu ? $selectedOrtu->user->nama ?? '' : '' }}"
                            {{ $selectedOrtu ? 'readonly' : '' }}>

                        <button type="button" class="ortu-clear" id="ortuClearBtn"
                            style="{{ $selectedOrtu ? 'display:flex;' : 'display:none;' }}">
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                    </div>

                    <div class="ortu-dropdown" id="ortuDropdown">

                        @forelse ($orangTua as $ortu)
                            <div class="ortu-option" data-id="{{ $ortu->id }}"
                                data-nama="{{ $ortu->user->nama ?? '-' }}" data-alamat="{{ $ortu->alamat ?? '' }}"
                                data-telepon="{{ $ortu->no_telepon ?? ($ortu->telepon ?? '') }}"
                                style="{{ $selectedOrtu ? 'display:none;' : '' }}">

                                <div class="ortu-avatar">
                                    {{ strtoupper(substr($ortu->user->nama ?? '-', 0, 1)) }}
                                </div>

                                <div class="ortu-option-name">
                                    {{ $ortu->user->nama ?? '-' }}
                                </div>

                            </div>

                        @empty

                            <div class="ortu-empty">
                                Belum ada data orang tua terdaftar.
                            </div>
                        @endforelse

                    </div>

                    <div class="ortu-selected {{ $selectedOrtu ? 'show' : '' }}" id="ortuSelectedCard">

                        <div class="ortu-avatar-lg" id="ortuSelectedAvatar">
                            {{ $selectedOrtu ? strtoupper(substr($selectedOrtu->user->nama ?? '-', 0, 1)) : '' }}
                        </div>

                        <div class="ortu-selected-info">

                            <div class="ortu-selected-name">
                                <span
                                    id="ortuSelectedName">{{ $selectedOrtu ? $selectedOrtu->user->nama ?? '' : '' }}</span>
                                <span class="ortu-tag tag-chosen">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Orang Tua Terpilih
                                </span>
                            </div>

                            <div class="ortu-selected-meta" id="ortuSelectedMeta">
                                @if ($selectedOrtu && $selectedOrtu->alamat)
                                    <span><i class="fa-solid fa-location-dot"></i> {{ $selectedOrtu->alamat }}</span>
                                @endif
                                @if ($selectedOrtu && ($selectedOrtu->no_telepon ?? ($selectedOrtu->telepon ?? null)))
                                    <span><i class="fa-solid fa-phone"></i>
                                        {{ $selectedOrtu->no_telepon ?? $selectedOrtu->telepon }}</span>
                                @endif
                            </div>

                        </div>

                        <button type="button" class="btn-ganti-ortu" id="btnGantiOrtu">
                            <i class="fa-solid fa-right-left"></i>
                            Ganti Orang Tua
                        </button>

                    </div>

                    <input type="hidden" name="orang_tua_id" id="orangTuaIdInput"
                        value="{{ old('orang_tua_id', $balita->orang_tua_id) }}">

                </div>

                {{-- TANGGAL LAHIR + KALKULATOR USIA --}}
                <div class="form-row-2">

                    <div class="form-group">

                        <label>
                            Tanggal Lahir <span class="req">*</span>
                        </label>

                        <input type="date" name="tanggal_lahir" id="tanggalLahirInput"
                            value="{{ old('tanggal_lahir', $balita->tanggal_lahir) }}" required>

                        <div class="form-hint">
                            <i class="fa-regular fa-circle-question"></i>
                            Pilih tanggal lahir anak sesuai surat keterangan lahir/akta.
                        </div>

                    </div>

                    <div class="form-group">

                        <label>Kalkulator Usia Posyandu</label>

                        <div class="usia-box">
                            <span class="usia-value">
                                <i class="fa-solid fa-cake-candles"></i>
                                <span id="usiaValue">Usia: -</span>
                            </span>
                            <span class="usia-tag" id="usiaTag">-</span>
                        </div>

                        <div class="form-hint">
                            <i class="fa-regular fa-circle-question"></i>
                            Digunakan sebagai parameter acuan grafik Z-Score KMS.
                        </div>

                    </div>

                </div>

                {{-- JENIS KELAMIN --}}
                <div class="form-group">

                    <label>
                        Jenis Kelamin <span class="req">*</span>
                    </label>

                    <div class="gender-toggle">

                        <input type="radio" name="jenis_kelamin" id="genderL" value="L"
                            {{ old('jenis_kelamin', $balita->jenis_kelamin) === 'L' ? 'checked' : '' }} required>
                        <label for="genderL" class="gender-btn">
                            <i class="fa-solid fa-mars"></i>
                            Laki-laki
                        </label>

                        <input type="radio" name="jenis_kelamin" id="genderP" value="P"
                            {{ old('jenis_kelamin', $balita->jenis_kelamin) === 'P' ? 'checked' : '' }}>
                        <label for="genderP" class="gender-btn">
                            <i class="fa-solid fa-venus"></i>
                            Perempuan
                        </label>

                    </div>

                </div>

                {{-- ALAMAT --}}
                <div class="form-group">

                    <div class="form-label-row">

                        <label>
                            Alamat Lengkap Domisili <span class="req">*</span>
                        </label>

                        <label class="same-address-check">
                            <input type="checkbox" id="sameAddressCheck">
                            Sama dengan alamat Orang Tua
                        </label>

                    </div>

                    <textarea name="alamat" id="alamatTextarea" placeholder="Alamat lengkap tempat tinggal" required>{{ old('alamat', $balita->alamat) }}</textarea>

                    <div class="form-hint">
                        <i class="fa-regular fa-circle-question"></i>
                        Pastikan RW/RT dicantumkan dengan jelas untuk penugasan kunjungan kader.
                    </div>

                </div>

                {{-- ERROR VALIDASI --}}
                @if ($errors->any())
                    <div class="form-error-box">
                        <div class="form-error-title">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            Periksa kembali data yang dimasukkan
                        </div>

                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-actions">

                    <a href="{{ route('kader.monitoringbalita') }}" class="btn-cancel">
                        Batal
                    </a>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-circle-check"></i>
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </main>

@endsection

@push('scripts')
    @vite('resources/js/kader/create.js')

    @if ($selectedOrtu)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Provide the initial address for the JS logic in case user checks the checkbox
                window.selectedOrtuAlamat = @json($selectedOrtu->alamat ?? '');

                // Trigger age calculation
                if (typeof updateUsia === 'function') {
                    updateUsia();
                }
            });
        </script>
    @else
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof updateUsia === 'function') {
                    updateUsia();
                }
            });
        </script>
    @endif
@endpush
