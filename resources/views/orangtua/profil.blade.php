@extends('orangtua.layouts.orangtua')

@section('title', 'Profil & Pengaturan Akun - Prevanta')

@push('styles')
    @vite('resources/css/orangtua/profil.css')
@endpush

@section('content')
    @php
        $user = $user ?? Auth::user();
        $orangTua = $orangTua ?? $user?->orangTua;
        $initialLetter = strtoupper(substr($user->nama ?? 'O', 0, 1));
        $savedPhotoUrl = $orangTua?->foto_profil_url;

        $hubunganRaw = strtolower((string) ($orangTua?->hubungan_dengan_balita ?? ''));
        $hubunganLabel = match ($hubunganRaw) {
            'orangtua', 'orang tua', '' => 'Orang Tua / Wali',
            'wali' => 'Wali',
            default => ucfirst($orangTua->hubungan_dengan_balita),
        };

        $jenisKelaminLabel = match ($orangTua?->jenis_kelamin) {
            'P' => 'Perempuan',
            'L' => 'Laki-laki',
            default => '-',
        };

        $oldHapusFoto = old('hapus_foto', '0') === '1';
        $hasInitialPhoto = filled($savedPhotoUrl) && ! $oldHapusFoto;
    @endphp

    <main class="content op-page">

        {{-- ══ 1. PAGE HEADER ══════════════════════════════════════ --}}
        <div class="op-header">
            <div>
                <h1 class="op-title">Profil &amp; Pengaturan Akun</h1>
                <p class="op-subtitle">
                    Kelola identitas akun, informasi kontak, dan foto profil orang tua Anda.
                </p>
            </div>

            <div class="op-verified-badge">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Akun Orang Tua Terverifikasi</span>
            </div>
        </div>

        {{-- ══ FLASH MESSAGE & VALIDATION SUMMARY ══════════════════ --}}
        @if (session('success'))
            <div class="op-alert op-alert-success" role="status">
                <i class="fa-solid fa-circle-check" style="margin-top: 2px;"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="op-alert op-alert-error" role="alert">
                <i class="fa-solid fa-triangle-exclamation" style="margin-top: 2px;"></i>
                <div>
                    <strong>Periksa kembali isian formulir Anda:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form
            id="form-profil-orangtua"
            action="{{ route('orangtua.profil.update') }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')

            <input
                type="hidden"
                name="hapus_foto"
                id="hapus_foto"
                value="{{ old('hapus_foto', '0') }}"
            >

            {{-- ══ 2. HERO IDENTITY & FOTO PROFIL CARD ═══════════════ --}}
            <section class="op-hero-card" aria-label="Identitas Akun Orang Tua">
                <div class="op-hero-identity">
                    <div
                        class="op-avatar-wrap"
                        id="op-avatar-preview"
                        data-initial-letter="{{ $initialLetter }}"
                        data-saved-photo-url="{{ $savedPhotoUrl ?? '' }}"
                    >
                        @if ($hasInitialPhoto)
                            <img src="{{ $savedPhotoUrl }}" alt="Foto profil {{ $user->nama }}">
                        @else
                            <span>{{ $initialLetter }}</span>
                        @endif
                    </div>

                    <div class="op-identity-meta">
                        <h2 class="op-identity-name">
                            <span>{{ $user->nama }}</span>
                            <i class="fa-solid fa-circle-check" title="Akun terverifikasi"></i>
                        </h2>

                        <p class="op-identity-nik">
                            NIK: {{ $orangTua?->nik ?: '-' }}
                        </p>

                        <div class="op-identity-pills">
                            <span class="op-pill op-pill-wine">{{ $hubunganLabel }}</span>
                            <span class="op-pill op-pill-neutral">{{ $jenisKelaminLabel }}</span>
                            <span class="op-pill op-pill-green">
                                <i class="fa-solid fa-location-dot"></i>
                                Posyandu Mawar Melati &bull; RW 03
                            </span>
                        </div>
                    </div>
                </div>

                <div class="op-hero-photo-actions">
                    <input
                        type="file"
                        id="foto_profil"
                        name="foto_profil"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="op-sr-only"
                    >

                    <div class="op-photo-btn-row">
                        <label for="foto_profil" class="op-btn-upload">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Ubah Foto Profil</span>
                        </label>

                        <button
                            type="button"
                            id="btn-hapus-foto"
                            class="op-btn-remove-photo"
                            @disabled(! $hasInitialPhoto)
                        >
                            Hapus Foto
                        </button>
                    </div>

                    <p class="op-photo-hint">
                        Foto profil orang tua (bukan foto balita). Format JPG, PNG, atau WEBP, maksimal ukuran berkas 2 MB.
                    </p>

                    @error('foto_profil')
                        <span class="op-error-text">{{ $message }}</span>
                    @enderror
                </div>
            </section>

            {{-- ══ 3. MAIN IDENTITY & CONTACT CARD ═══════════════════ --}}
            <div class="op-form-grid">

                {{-- INFORMASI IDENTITAS & KONTAK --}}
                <section class="op-card" aria-labelledby="op-identitas-heading">
                    <div class="op-card-head">
                        <div class="op-card-icon">
                            <i class="fa-regular fa-id-card"></i>
                        </div>
                        <div>
                            <h2 id="op-identitas-heading" class="op-card-title">
                                Informasi Identitas &amp; Kontak Orang Tua
                            </h2>
                            <p class="op-card-desc">
                                Pastikan data sesuai KTP dan nomor kontak aktif untuk koordinasi jadwal Posyandu.
                            </p>
                        </div>
                    </div>

                    <div class="op-field-row">
                        <div class="op-field-group">
                            <div class="op-label-row">
                                <label for="nama" class="op-label">
                                    Nama Lengkap Orang Tua <span class="op-required" aria-hidden="true">*</span>
                                </label>
                            </div>
                            <div class="op-input-wrap">
                                <i class="fa-regular fa-user op-input-icon" aria-hidden="true"></i>
                                <input
                                    type="text"
                                    id="nama"
                                    name="nama"
                                    class="op-input @error('nama') is-invalid @enderror"
                                    value="{{ old('nama', $user->nama) }}"
                                    data-initial="{{ $user->nama }}"
                                    minlength="2"
                                    maxlength="60"
                                    pattern="[A-Za-z]+( [A-Za-z]+)*"
                                    title="Nama lengkap hanya boleh berisi huruf dan spasi (2–60 karakter)."
                                    autocomplete="name"
                                    aria-describedby="nama-feedback"
                                    @error('nama') aria-invalid="true" @enderror
                                    required
                                >
                            </div>
                            @error('nama')
                                <span id="nama-feedback" class="op-error-text">{{ $message }}</span>
                            @else
                                <span id="nama-feedback" class="op-help-text">Sesuai KTP, hanya huruf dan spasi (2–60 karakter, tanpa gelar, angka, atau simbol).</span>
                            @enderror
                        </div>

                        <div class="op-field-group">
                            <div class="op-label-row">
                                <label for="nik" class="op-label">
                                    NIK (Nomor Induk Kependudukan) <span class="op-required" aria-hidden="true">*</span>
                                </label>
                                <span id="nik-counter" class="op-digit-badge">0/16 Digit</span>
                            </div>
                            <div class="op-input-wrap">
                                <i class="fa-solid fa-fingerprint op-input-icon" aria-hidden="true"></i>
                                <input
                                    type="text"
                                    id="nik"
                                    name="nik"
                                    class="op-input @error('nik') is-invalid @enderror"
                                    value="{{ old('nik', $orangTua?->nik) }}"
                                    data-initial="{{ $orangTua?->nik ?? '' }}"
                                    inputmode="numeric"
                                    maxlength="16"
                                    pattern="[0-9]{16}"
                                    aria-describedby="nik-feedback"
                                    @error('nik') aria-invalid="true" @enderror
                                    required
                                >
                            </div>
                            @error('nik')
                                <span id="nik-feedback" class="op-error-text">{{ $message }}</span>
                            @else
                                <span id="nik-feedback" class="op-help-text">16 digit angka resmi kependudukan Indonesia.</span>
                            @enderror
                        </div>
                    </div>

                    <div class="op-field-row">
                        <div class="op-field-group">
                            <div class="op-label-row">
                                <label for="email" class="op-label">
                                    Email <span class="op-required" aria-hidden="true">*</span>
                                </label>
                            </div>
                            <div class="op-input-wrap">
                                <i class="fa-regular fa-envelope op-input-icon" aria-hidden="true"></i>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="op-input @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}"
                                    data-initial="{{ $user->email }}"
                                    maxlength="100"
                                    autocomplete="email"
                                    autocorrect="off"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    aria-describedby="email-feedback"
                                    @error('email') aria-invalid="true" @enderror
                                    required
                                >
                            </div>
                            @error('email')
                                <span id="email-feedback" class="op-error-text">{{ $message }}</span>
                            @else
                                <span id="email-feedback" class="op-help-text">Untuk notifikasi hasil KMS &amp; buletin.</span>
                            @enderror
                        </div>

                        <div class="op-field-group">
                            <div class="op-label-row">
                                <label for="no_hp" class="op-label">
                                    No. HP / WhatsApp <span class="op-required" aria-hidden="true">*</span>
                                </label>
                            </div>
                            <div class="op-input-wrap">
                                <i class="fa-solid fa-phone op-input-icon" aria-hidden="true"></i>
                                <input
                                    type="text"
                                    id="no_hp"
                                    name="no_hp"
                                    class="op-input @error('no_hp') is-invalid @enderror"
                                    value="{{ old('no_hp', $user->no_hp) }}"
                                    data-initial="{{ $user->no_hp ?? '' }}"
                                    inputmode="numeric"
                                    maxlength="13"
                                    pattern="[0-9]{8,13}"
                                    autocomplete="tel"
                                    aria-describedby="no_hp-feedback"
                                    @error('no_hp') aria-invalid="true" @enderror
                                    required
                                >
                            </div>
                            @error('no_hp')
                                <span id="no_hp-feedback" class="op-error-text">{{ $message }}</span>
                            @else
                                <span id="no_hp-feedback" class="op-help-text">8–13 digit aktif untuk pesan Posyandu.</span>
                            @enderror
                        </div>
                    </div>

                    <div class="op-domisili-box">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <span>
                            Terkait domisili Posyandu aktif: <strong>Posyandu Mawar Melati</strong>.
                            Apabila pindah domisili atau pos pelayanan, hubungi kader Posyandu terkait.
                        </span>
                    </div>
                </section>

            </div>

            {{-- ══ 4. BOTTOM ACTION BAR ══════════════════════════════ --}}
            <div class="op-action-bar">
                <div class="op-action-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Hanya data profil &amp; kontak orang tua di atas yang dapat diperbarui secara mandiri.</span>
                </div>

                <div class="op-action-buttons">
                    <button type="button" id="btn-batal-profil" class="op-btn-cancel">
                        Batal
                    </button>
                    <button type="button" id="btn-open-confirm-modal" class="op-btn-save">
                        <i class="fa-regular fa-circle-check"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </form>

        {{-- ══ 5. MODAL KONFIRMASI SIMPAN PERUBAHAN ══════════════════ --}}
        <div
            id="op-confirm-modal"
            class="op-modal-backdrop"
            role="dialog"
            aria-modal="true"
            aria-labelledby="op-confirm-title"
            hidden
        >
            <div class="op-modal-card">
                <div class="op-modal-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 id="op-confirm-title" class="op-modal-title">Simpan Perubahan Profil?</h3>
                <p class="op-modal-text">
                    Apakah Anda yakin data identitas, kontak, dan foto profil yang dimasukkan sudah sesuai?
                </p>
                <div class="op-modal-actions">
                    <button type="button" id="btn-close-confirm-modal" class="op-btn-cancel">
                        Periksa Kembali
                    </button>
                    <button type="button" id="btn-submit-confirm-modal" class="op-btn-save">
                        Ya, Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>

    </main>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('form-profil-orangtua');
            if (!form) {
                return;
            }

            const namaInput = document.getElementById('nama');
            const nikInput = document.getElementById('nik');
            const emailInput = document.getElementById('email');
            const noHpInput = document.getElementById('no_hp');
            const nikCounter = document.getElementById('nik-counter');

            const fotoInput = document.getElementById('foto_profil');
            const hapusFotoInput = document.getElementById('hapus_foto');
            const avatarPreview = document.getElementById('op-avatar-preview');
            const btnHapusFoto = document.getElementById('btn-hapus-foto');
            const btnBatal = document.getElementById('btn-batal-profil');

            const modal = document.getElementById('op-confirm-modal');
            const btnOpenModal = document.getElementById('btn-open-confirm-modal');
            const btnCloseModal = document.getElementById('btn-close-confirm-modal');
            const btnSubmitModal = document.getElementById('btn-submit-confirm-modal');

            let activeObjectUrl = null;

            const sanitizeNama = () => {
                if (!namaInput) {
                    return;
                }

                const cleaned = namaInput.value
                    .replace(/[^a-zA-Z\s]/g, '')
                    .replace(/^\s+/, '')
                    .replace(/\s{2,}/g, ' ')
                    .slice(0, 60);

                if (namaInput.value !== cleaned) {
                    namaInput.value = cleaned;
                }

                namaInput.setCustomValidity('');
            };

            const updateNikCounter = () => {
                if (!nikInput || !nikCounter) {
                    return;
                }

                const digits = nikInput.value.replace(/\D/g, '').slice(0, 16);
                if (nikInput.value !== digits) {
                    nikInput.value = digits;
                }

                nikCounter.textContent = `${digits.length}/16 Digit`;
                nikCounter.classList.toggle('is-complete', digits.length === 16);
            };

            const sanitizeNoHp = () => {
                if (!noHpInput) {
                    return;
                }

                const digits = noHpInput.value.replace(/\D/g, '').slice(0, 13);
                if (noHpInput.value !== digits) {
                    noHpInput.value = digits;
                }
            };

            const renderAvatarInitial = () => {
                if (!avatarPreview) {
                    return;
                }

                const initial = avatarPreview.dataset.initialLetter || 'O';
                avatarPreview.innerHTML = '';
                const span = document.createElement('span');
                span.textContent = initial;
                avatarPreview.appendChild(span);
            };

            const renderAvatarImage = (src) => {
                if (!avatarPreview) {
                    return;
                }

                avatarPreview.innerHTML = '';
                const img = document.createElement('img');
                img.src = src;
                img.alt = 'Preview foto profil orang tua';
                avatarPreview.appendChild(img);
            };

            const revokeObjectUrl = () => {
                if (activeObjectUrl) {
                    URL.revokeObjectURL(activeObjectUrl);
                    activeObjectUrl = null;
                }
            };

            // Filter huruf/spasi Nama, penghitung digit NIK, & filter angka No. HP
            namaInput?.addEventListener('input', sanitizeNama);
            nikInput?.addEventListener('input', updateNikCounter);
            noHpInput?.addEventListener('input', sanitizeNoHp);
            updateNikCounter();

            // Live preview upload foto profil
            fotoInput?.addEventListener('change', () => {
                const file = fotoInput.files?.[0];
                if (!file) {
                    return;
                }

                revokeObjectUrl();
                activeObjectUrl = URL.createObjectURL(file);
                renderAvatarImage(activeObjectUrl);

                if (hapusFotoInput) {
                    hapusFotoInput.value = '0';
                }

                if (btnHapusFoto) {
                    btnHapusFoto.disabled = false;
                }
            });

            // Tombol Hapus Foto (Opsi A: reset preview ke inisial & tandai hapus_foto=1)
            btnHapusFoto?.addEventListener('click', () => {
                revokeObjectUrl();
                if (fotoInput) {
                    fotoInput.value = '';
                }

                renderAvatarInitial();

                if (hapusFotoInput) {
                    hapusFotoInput.value = '1';
                }

                btnHapusFoto.disabled = true;
            });

            // Tombol Batal: reset seluruh input form & preview foto kembali ke data awal
            btnBatal?.addEventListener('click', () => {
                [namaInput, nikInput, emailInput, noHpInput].forEach((input) => {
                    if (input) {
                        input.value = input.dataset.initial ?? '';
                    }
                });

                namaInput?.setCustomValidity('');

                revokeObjectUrl();
                if (fotoInput) {
                    fotoInput.value = '';
                }

                if (hapusFotoInput) {
                    hapusFotoInput.value = '0';
                }

                const savedPhotoUrl = avatarPreview?.dataset.savedPhotoUrl || '';
                if (savedPhotoUrl !== '') {
                    renderAvatarImage(savedPhotoUrl);
                    if (btnHapusFoto) {
                        btnHapusFoto.disabled = false;
                    }
                } else {
                    renderAvatarInitial();
                    if (btnHapusFoto) {
                        btnHapusFoto.disabled = true;
                    }
                }

                updateNikCounter();
            });

            // Pop-up Modal Konfirmasi Simpan Perubahan
            const openModal = () => {
                if (!modal) {
                    return;
                }

                modal.hidden = false;
                modal.classList.add('is-open');
                btnCloseModal?.focus();
            };

            const closeModal = () => {
                if (!modal) {
                    return;
                }

                modal.classList.remove('is-open');
                modal.hidden = true;
                btnOpenModal?.focus();
            };

            const applyClientValidation = () => {
                if (namaInput) {
                    const normalizedNama = namaInput.value.trim().replace(/\s+/g, ' ');
                    namaInput.value = normalizedNama;
                    namaInput.setCustomValidity('');

                    if (normalizedNama.length > 0 && normalizedNama.length < 2) {
                        namaInput.setCustomValidity('Nama lengkap minimal 2 karakter.');
                    } else if (normalizedNama.length > 60) {
                        namaInput.setCustomValidity('Nama lengkap maksimal 60 karakter.');
                    } else if (normalizedNama.length > 0 && !/^[a-zA-Z]+(?:\s[a-zA-Z]+)*$/.test(normalizedNama)) {
                        namaInput.setCustomValidity('Nama lengkap hanya boleh berisi huruf dan spasi.');
                    }
                }
            };

            btnOpenModal?.addEventListener('click', () => {
                applyClientValidation();

                if (!form.reportValidity()) {
                    return;
                }

                openModal();
            });

            btnCloseModal?.addEventListener('click', closeModal);

            modal?.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
                    closeModal();
                }
            });

            btnSubmitModal?.addEventListener('click', () => {
                btnSubmitModal.disabled = true;
                form.submit();
            });
        })();
    </script>
@endpush
