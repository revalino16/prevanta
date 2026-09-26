@extends('kader.layouts.kader')

@section('title', 'Input Materi Edukasi — Prevanta')

@push('styles')
    @vite('resources/css/kader/create.css')
    <style>
        .upload-dropzone {
            border: 2px dashed #ECE3D8;
            border-radius: 16px;
            padding: 28px 20px;
            text-align: center;
            background: #FDFBF8;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        .upload-dropzone:hover, .upload-dropzone.dragover {
            border-color: #9B2A4A;
            background: #FFF9FA;
        }
        .upload-preview {
            display: none;
            position: relative;
            max-width: 100%;
            height: 220px;
            border-radius: 14px;
            overflow: hidden;
            margin-top: 12px;
            border: 1px solid #ECE3D8;
        }
        .upload-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .btn-remove-img {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.65);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 13px;
        }
        .kategori-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 6px;
        }
        .kategori-btn {
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #FFFFFF;
            border: 1.5px solid #ECE3D8;
            color: #554D48;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .kategori-btn:hover {
            border-color: #D9CFC4;
            background: #FBF9F6;
        }
        .kategori-btn.active {
            background: #9B2A4A;
            color: #FFFFFF;
            border-color: #9B2A4A;
            box-shadow: 0 4px 10px rgba(155, 42, 74, 0.2);
        }
    </style>
@endpush

@section('content')

<main class="content">

    <a href="{{ route('kader.edukasi') }}" class="back-link">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Daftar Edukasi
    </a>

    <div class="page-head">
        <div>
            <h1>Input Materi Edukasi</h1>
            <p>
                Bagikan artikel, pedoman nutrisi, dan panduan tumbuh kembang terpercaya untuk orang tua dan kader.
            </p>
        </div>

        <span class="session-chip">
            <i class="fa-solid fa-shield-halved"></i>
            Posyandu Mawar Melati - Desa Sukamaju &bull; Sesi Aktif Kader
        </span>
    </div>

    {{-- SUCCESS ALERT --}}
    @if (session('success'))
        <div class="success-message">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="form-panel">
        <div class="form-panel-head">
            <div class="icon-circle">
                <i class="fa-solid fa-book-open-reader"></i>
            </div>

            <div>
                <h2>Formulir Materi Edukasi Baru</h2>
                <p>
                    Lengkapi judul, topik materi, gambar sampul, serta artikel lengkap untuk memudahkan orang tua memahami kesehatan balita.
                </p>
            </div>
        </div>

        <form action="{{ route('kader.edukasi.store') }}" method="POST" enctype="multipart/form-data" id="formEdukasi">
            @csrf

            {{-- JUDUL ARTIKEL --}}
            <div class="form-group">
                <div class="form-label-row">
                    <label>
                        Judul Materi Edukasi <span class="req">*</span>
                    </label>
                    <span class="field-hint-top">Maksimal 200 Karakter</span>
                </div>

                <input
                    type="text"
                    name="judul"
                    placeholder="Contoh: Pentingnya Gizi Seimbang & Protein Hewani untuk Mencegah Stunting"
                    value="{{ old('judul') }}"
                    required
                >

                <div class="form-hint">
                    <i class="fa-regular fa-circle-question"></i>
                    Gunakan judul yang ringkas, menarik, dan mudah dipahami oleh orang tua balita.
                </div>
            </div>

            {{-- KATEGORI TOPIK --}}
            <div class="form-group">
                <div class="form-label-row">
                    <label>
                        Kategori Topik <span class="req">*</span>
                    </label>
                    <span class="field-hint-top">Pilih Salah Satu</span>
                </div>

                <input type="hidden" name="kategori" id="selectedKategoriInput" value="{{ old('kategori', $kategoriList[0] ?? 'Gizi & MP-ASI') }}">

                <div class="kategori-pills">
                    @foreach ($kategoriList as $kat)
                        <button
                            type="button"
                            class="kategori-btn {{ old('kategori', $kategoriList[0] ?? '') === $kat ? 'active' : '' }}"
                            data-val="{{ $kat }}"
                        >
                            {{ $kat }}
                        </button>
                    @endforeach
                </div>

                <div class="form-hint">
                    <i class="fa-regular fa-circle-question"></i>
                    Topik ini menentukan badge warna dan filter pengelompokan artikel di beranda edukasi.
                </div>
            </div>

            {{-- GAMBAR SAMPUL --}}
            <div class="form-group">
                <div class="form-label-row">
                    <label>
                        Gambar Sampul Panduan <span class="req">(Opsional)</span>
                    </label>
                    <span class="field-hint-top">JPG, PNG, atau WebP (Maks 3MB)</span>
                </div>

                <input
                    type="file"
                    name="gambar"
                    id="gambarInput"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    style="display: none;"
                >

                <div class="upload-dropzone" id="dropzoneBox">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 32px; color: #9B2A4A; margin-bottom: 8px;"></i>
                    <p style="font-size: 13px; font-weight: 700; color: #2A2420; margin-bottom: 4px;">
                        Klik atau seret gambar ke sini untuk mengunggah
                    </p>
                    <p style="font-size: 11px; color: #8C8279;">
                        Rekomendasi rasio 16:9 atau resolusi minimal 800 x 450 pixel
                    </p>
                </div>

                <div class="upload-preview" id="previewBox">
                    <img src="" id="previewImg" alt="Preview Gambar">
                    <button type="button" class="btn-remove-img" id="btnRemoveImg" title="Hapus Gambar">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="form-hint">
                    <i class="fa-regular fa-circle-question"></i>
                    Jika tidak diunggah, sistem akan menggunakan gambar ilustrasi bawaan yang relevan.
                </div>
            </div>

            {{-- KONTEN EDUKASI --}}
            <div class="form-group">
                <div class="form-label-row">
                    <label>
                        Isi Materi / Penjelasan Lengkap <span class="req">*</span>
                    </label>
                    <span class="field-hint-top" id="wordCountLabel">0 Kata</span>
                </div>

                <textarea
                    name="konten"
                    id="kontenTextarea"
                    rows="8"
                    placeholder="Tuliskan materi edukasi, tips praktis, panduan pemberian makan, atau rekomendasi pemantauan tumbuh kembang di sini..."
                    required
                >{{ old('konten') }}</textarea>

                <div class="form-hint">
                    <i class="fa-regular fa-circle-question"></i>
                    Sertakan informasi praktis seperti takaran porsi, waktu imunisasi, atau tanda bahaya yang perlu dirujuk.
                </div>
            </div>

            {{-- ERROR VALIDATION LIST --}}
            @if ($errors->any())
                <div class="form-error-box">
                    <div class="form-error-title">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        Terdapat kesalahan pada input:
                    </div>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- FORM ACTIONS --}}
            <div class="form-actions">
                <a href="{{ route('kader.edukasi') }}" class="btn-cancel">
                    Batal
                </a>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-circle-check"></i>
                    Simpan Materi Edukasi
                </button>
            </div>
        </form>
    </div>

</main>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Kategori selection
    const kategoriInput = document.getElementById('selectedKategoriInput');
    const kategoriButtons = document.querySelectorAll('.kategori-btn');

    kategoriButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            kategoriButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            kategoriInput.value = btn.dataset.val;
        });
    });

    // 2. Image upload preview & drag-and-drop
    const dropzone = document.getElementById('dropzoneBox');
    const fileInput = document.getElementById('gambarInput');
    const previewBox = document.getElementById('previewBox');
    const previewImg = document.getElementById('previewImg');
    const btnRemove = document.getElementById('btnRemoveImg');

    dropzone.addEventListener('click', () => fileInput.click());

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files && fileInput.files[0]) {
            handleFileSelect(fileInput.files[0]);
        }
    });

    function handleFileSelect(file) {
        if (!file.type.match('image.*')) {
            alert('Silakan pilih file gambar (JPG, PNG, WebP).');
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            previewBox.style.display = 'block';
            dropzone.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    btnRemove.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.value = '';
        previewImg.src = '';
        previewBox.style.display = 'none';
        dropzone.style.display = 'block';
    });

    // 3. Word counter
    const konten = document.getElementById('kontenTextarea');
    const counter = document.getElementById('wordCountLabel');

    const updateWords = () => {
        const text = konten.value.trim();
        const words = text ? text.split(/\s+/).length : 0;
        counter.textContent = words + ' Kata';
    };

    konten.addEventListener('input', updateWords);
    updateWords();
});
</script>
@endpush
