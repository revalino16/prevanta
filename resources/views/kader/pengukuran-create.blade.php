@extends('kader.layouts.kader')

@section('title', 'Catat Pengukuran ' . $balita->nama . ' — Prevanta')

@section('content')

<main class="content measurement-form-page">
    <div class="measurement-form-heading">
        <a href="{{ route('kader.balita.kms', $balita) }}" class="kms-back-link">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke Profil Balita
        </a>

        <div class="measurement-page-meta">
            <span><i class="fa-solid fa-circle"></i> ID Rekam Medis KIA: <strong>{{ $childCode }}</strong></span>
            <span><i class="fa-solid fa-circle"></i> Tanggal default: hari ini</span>
        </div>
    </div>

    <section class="child-profile-card measurement-child-card" aria-labelledby="child-name">
        <div class="child-photo-wrap">
            <img
                src="{{ asset($balita->jenis_kelamin === 'L' ? 'images/cowo.jpg' : 'images/cewe.jpg') }}"
                alt="Foto {{ $balita->nama }}"
                class="child-photo"
            >
            <span class="child-gender child-gender-{{ strtolower($balita->jenis_kelamin) }}">
                <i class="fa-solid {{ $balita->jenis_kelamin === 'L' ? 'fa-mars' : 'fa-venus' }}"></i>
                {{ $balita->jenis_kelamin }}
            </span>
        </div>

        <div class="child-profile-content">
            <h2 id="child-name">{{ $balita->nama }}</h2>

            <div class="child-details-grid">
                <div class="child-detail">
                    <span class="child-detail-label">Nomor Induk Kependudukan (NIK)</span>
                    <strong>{{ $balita->nik }}</strong>
                </div>
                <div class="child-detail">
                    <span class="child-detail-label">Tanggal Lahir &amp; Usia</span>
                    <strong>{{ $birthDateLabel }} <span>({{ $currentAgeLabel }})</span></strong>
                </div>
                <div class="child-detail child-detail-address">
                    <span class="child-detail-label">Wilayah Domisili</span>
                    <strong>{{ $balita->alamat ?: 'Belum tersedia' }}</strong>
                </div>
                <div class="child-detail">
                    <span class="child-detail-label">ID Balita</span>
                    <strong>{{ $childCode }}</strong>
                </div>
                <div class="child-detail">
                    <span class="child-detail-label">Orang Tua / Wali Balita</span>
                    <strong>{{ $balita->orangTua?->user?->nama ?? 'Belum tersedia' }}</strong>
                </div>
                <div class="child-detail child-status-detail">
                    @if ($latestMeasurement)
                        @php
                            $latestStatus = strtolower($latestMeasurement->status_pertumbuhan ?? '');
                            $statusTone = str_contains($latestStatus, 'sangat') ? 'danger' : (str_contains($latestStatus, 'pendek') ? 'warning' : 'normal');
                        @endphp
                        <span class="growth-status growth-status-{{ $statusTone }}">
                            <i class="fa-solid fa-chart-line"></i>
                            {{ str($latestStatus)->title() }}
                            @if ($latestMeasurement->z_score !== null)
                                ({{ $latestMeasurement->z_score > 0 ? '+' : '' }}{{ $latestMeasurement->z_score }} SD)
                            @endif
                        </span>
                    @else
                        <span class="growth-status growth-status-neutral">
                            <i class="fa-regular fa-clock"></i>
                            Belum ada pengukuran
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="measurement-form-card" aria-labelledby="measurement-form-title">
        <div class="measurement-form-card-head">
            <span class="measurement-form-icon"><i class="fa-regular fa-clipboard"></i></span>
            <div>
                <h1 id="measurement-form-title">Informasi Pelayanan &amp; Antropometri Balita</h1>
                <p>Lengkapi hasil pengukuran dengan teliti. Z-score PB/U atau TB/U dihitung otomatis setelah data disimpan.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="measurement-error-summary" role="alert">
                <div><i class="fa-solid fa-circle-exclamation"></i> Periksa kembali data berikut:</div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('kader.balita.pengukuran.store', $balita) }}"
            method="POST"
            enctype="multipart/form-data"
            class="measurement-entry-form"
            id="measurement-entry-form"
        >
            @csrf

            <div class="measurement-date-row">
                <div>
                    <label for="tanggal_pengukuran">Tanggal Pengukuran <span>*</span></label>
                    <p>Boleh diubah untuk mencatat hasil pengukuran sebelumnya.</p>
                </div>
                <div class="measurement-date-control">
                    <i class="fa-regular fa-calendar"></i>
                    <input
                        type="date"
                        name="tanggal_pengukuran"
                        id="tanggal_pengukuran"
                        value="{{ old('tanggal_pengukuran', $defaultMeasurementDate) }}"
                        min="{{ $balita->tanggal_lahir->toDateString() }}"
                        max="{{ $maximumMeasurementDate }}"
                        data-message-required="Tanggal pengukuran wajib diisi."
                        data-message-invalid="Tanggal pengukuran tidak valid."
                        data-message-min="Tanggal pengukuran tidak boleh sebelum tanggal lahir balita."
                        data-message-max="Tanggal pengukuran tidak boleh melewati hari ini atau batas usia balita 5 tahun."
                        required
                    >
                </div>
                @error('tanggal_pengukuran')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="measurement-section-title">
                <div>
                    <h2>Pengukuran Antropometri Balita <span>*</span></h2>
                    <p>Masukkan angka hasil alat ukur, maksimal 2 angka di belakang koma.</p>
                </div>
                <span class="required-badge">Wajib Diisi</span>
            </div>

            <div class="measurement-input-grid">
                <div class="measurement-input-card @error('berat_badan') has-error @enderror">
                    <div class="measurement-input-label">
                        <label for="berat_badan"><i class="fa-solid fa-scale-balanced"></i> Berat Badan (BB)</label>
                        <span>0,9–{{ $measurementLimits['berat_badan']['max'] }} kg</span>
                    </div>
                    <div class="measurement-unit-input">
                        <input type="number" name="berat_badan" id="berat_badan" value="{{ old('berat_badan') }}" min="{{ $measurementLimits['berat_badan']['min'] }}" max="{{ $measurementLimits['berat_badan']['max'] }}" step="0.01" inputmode="decimal" placeholder="Contoh: 12.4" data-message-required="Berat badan wajib diisi." data-message-invalid="Berat badan harus berupa angka." data-message-min="Berat badan tidak boleh kurang dari 0,9 kg." data-message-max="Berat badan tidak boleh lebih dari 30 kg." data-message-step="Berat badan hanya boleh menggunakan maksimal 2 angka di belakang koma." required>
                        <span>kg</span>
                    </div>
                    <small><i class="fa-regular fa-circle-check"></i> Pastikan timbangan sudah dikalibrasi.</small>
                    @error('berat_badan')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="measurement-input-card @error('tinggi_badan') has-error @enderror">
                    <div class="measurement-input-label">
                        <label for="tinggi_badan"><i class="fa-solid fa-ruler-vertical"></i> Tinggi / Panjang Badan (TB/PB)</label>
                        <span>{{ $measurementLimits['tinggi_badan']['min'] }}–{{ $measurementLimits['tinggi_badan']['max'] }} cm</span>
                    </div>
                    <div class="measurement-unit-input">
                        <input type="number" name="tinggi_badan" id="tinggi_badan" value="{{ old('tinggi_badan') }}" min="{{ $measurementLimits['tinggi_badan']['min'] }}" max="{{ $measurementLimits['tinggi_badan']['max'] }}" step="0.01" inputmode="decimal" placeholder="Contoh: 87.5" data-message-required="Panjang atau tinggi badan wajib diisi." data-message-invalid="Panjang atau tinggi badan harus berupa angka." data-message-min="Panjang atau tinggi badan tidak boleh kurang dari 35 cm." data-message-max="Panjang atau tinggi badan tidak boleh lebih dari 130 cm." data-message-step="Panjang atau tinggi badan hanya boleh menggunakan maksimal 2 angka di belakang koma." required>
                        <span>cm</span>
                    </div>
                    <small><i class="fa-regular fa-circle-check"></i> Gunakan infantometer atau stadiometer.</small>
                    @error('tinggi_badan')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="measurement-input-card @error('lingkar_lengan_atas') has-error @enderror">
                    <div class="measurement-input-label">
                        <label for="lingkar_lengan_atas"><i class="fa-solid fa-ruler-horizontal"></i> Lingkar Lengan Atas (LiLA)</label>
                        <span>{{ $measurementLimits['lingkar_lengan_atas']['min'] }}–{{ $measurementLimits['lingkar_lengan_atas']['max'] }} cm</span>
                    </div>
                    <div class="measurement-unit-input">
                        <input type="number" name="lingkar_lengan_atas" id="lingkar_lengan_atas" value="{{ old('lingkar_lengan_atas') }}" min="{{ $measurementLimits['lingkar_lengan_atas']['min'] }}" max="{{ $measurementLimits['lingkar_lengan_atas']['max'] }}" step="0.01" inputmode="decimal" placeholder="Contoh: 14.8" data-message-required="Lingkar lengan atas wajib diisi." data-message-invalid="Lingkar lengan atas harus berupa angka." data-message-min="Lingkar lengan atas tidak boleh kurang dari 5 cm." data-message-max="Lingkar lengan atas tidak boleh lebih dari 25 cm." data-message-step="Lingkar lengan atas hanya boleh menggunakan maksimal 2 angka di belakang koma." required>
                        <span>cm</span>
                    </div>
                    <small><i class="fa-regular fa-circle-check"></i> Ukur lengan kiri pada titik tengah.</small>
                    @error('lingkar_lengan_atas')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="measurement-input-card @error('lingkar_kepala') has-error @enderror">
                    <div class="measurement-input-label">
                        <label for="lingkar_kepala"><i class="fa-regular fa-face-smile"></i> Lingkar Kepala (LK)</label>
                        <span>{{ $measurementLimits['lingkar_kepala']['min'] }}–{{ $measurementLimits['lingkar_kepala']['max'] }} cm</span>
                    </div>
                    <div class="measurement-unit-input">
                        <input type="number" name="lingkar_kepala" id="lingkar_kepala" value="{{ old('lingkar_kepala') }}" min="{{ $measurementLimits['lingkar_kepala']['min'] }}" max="{{ $measurementLimits['lingkar_kepala']['max'] }}" step="0.01" inputmode="decimal" placeholder="Contoh: 47.2" data-message-required="Lingkar kepala wajib diisi." data-message-invalid="Lingkar kepala harus berupa angka." data-message-min="Lingkar kepala tidak boleh kurang dari 20 cm." data-message-max="Lingkar kepala tidak boleh lebih dari 60 cm." data-message-step="Lingkar kepala hanya boleh menggunakan maksimal 2 angka di belakang koma." required>
                        <span>cm</span>
                    </div>
                    <small><i class="fa-regular fa-circle-check"></i> Lingkarkan pita di atas alis dan telinga.</small>
                    @error('lingkar_kepala')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <fieldset class="measurement-position">
                <legend>Posisi Saat Mengukur TB/PB <span>*</span></legend>
                <p id="position-help">Posisi ini diperlukan agar koreksi dan Z-score dihitung dengan benar.</p>
                <div class="position-options">
                    <label>
                        <input type="radio" name="posisi_pengukuran" value="terlentang" data-message-required="Posisi pengukuran wajib dipilih." {{ old('posisi_pengukuran', $recommendedPosition) === 'terlentang' ? 'checked' : '' }} required>
                        <span><i class="fa-solid fa-bed"></i><strong>Terlentang</strong><small>Panjang badan (PB)</small></span>
                    </label>
                    <label>
                        <input type="radio" name="posisi_pengukuran" value="berdiri" data-message-required="Posisi pengukuran wajib dipilih." {{ old('posisi_pengukuran', $recommendedPosition) === 'berdiri' ? 'checked' : '' }} required>
                        <span><i class="fa-solid fa-child-reaching"></i><strong>Berdiri</strong><small>Tinggi badan (TB)</small></span>
                    </label>
                </div>
                @error('posisi_pengukuran')<span class="field-error">{{ $message }}</span>@enderror
            </fieldset>

            <div class="service-grid">
                <div class="service-field">
                    <label for="jenis_imunisasi_id">Imunisasi <span>Opsional</span></label>
                    <div class="select-control">
                        <i class="fa-solid fa-syringe"></i>
                        <select name="jenis_imunisasi_id" id="jenis_imunisasi_id">
                            <option value="">Tidak ada imunisasi hari ini</option>
                            @foreach ($immunizationTypes as $immunizationType)
                                <option value="{{ $immunizationType->getKey() }}" @selected((string) old('jenis_imunisasi_id') === (string) $immunizationType->getKey())>
                                    {{ $immunizationType->nama_imunisasi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('jenis_imunisasi_id')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="service-field">
                    <label for="jenis_vitamin_id">Vitamin &amp; Suplementasi <span>Opsional</span></label>
                    <div class="select-control">
                        <i class="fa-solid fa-capsules"></i>
                        <select name="jenis_vitamin_id" id="jenis_vitamin_id">
                            <option value="">Tidak ada vitamin hari ini</option>
                            @foreach ($vitaminTypes as $vitaminType)
                                <option value="{{ $vitaminType->getKey() }}" @selected((string) old('jenis_vitamin_id') === (string) $vitaminType->getKey())>
                                    {{ $vitaminType->nama_vitamin }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('jenis_vitamin_id')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="photo-upload-field @error('foto_pertumbuhan') has-error @enderror">
                <div>
                    <label for="foto_pertumbuhan"><i class="fa-regular fa-image"></i> Dokumentasi Pengukuran <span>Opsional</span></label>
                    <p>JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB dan 4096 × 4096 piksel.</p>
                </div>
                <label class="photo-upload-button" for="foto_pertumbuhan">
                    <i class="fa-solid fa-arrow-up-from-bracket"></i>
                    <span id="photo-file-name">Pilih gambar</span>
                </label>
                <input type="file" name="foto_pertumbuhan" id="foto_pertumbuhan" accept="image/jpeg,image/png,image/webp">
                @error('foto_pertumbuhan')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="automatic-calculation-note">
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    <strong>Perhitungan Otomatis Z-score PB/U atau TB/U</strong>
                    <p>Sistem menghitung berdasarkan jenis kelamin, tanggal lahir, tanggal pengukuran, panjang/tinggi badan, dan posisi ukur. Hasil tetap menunggu verifikasi bidan.</p>
                </div>
            </div>

            <div class="measurement-form-actions">
                <a href="{{ route('kader.balita.kms', $balita) }}" class="measurement-cancel-button">Batal</a>
                <button type="submit" class="measurement-submit-button">
                    <i class="fa-regular fa-floppy-disk"></i>
                    Simpan &amp; Catat Pelayanan
                </button>
            </div>
        </form>
    </section>
</main>

@endsection

@push('styles')
    @vite(['resources/css/kader/profil-balita.css', 'resources/css/kader/pengukuran.css'])
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const measurementForm = document.getElementById('measurement-entry-form');
    const dateInput = document.getElementById('tanggal_pengukuran');
    const birthDate = new Date('{{ $balita->tanggal_lahir->toDateString() }}T00:00:00');
    const positionHelp = document.getElementById('position-help');
    const positionInputs = Array.from(document.querySelectorAll('input[name="posisi_pengukuran"]'));
    const photoInput = document.getElementById('foto_pertumbuhan');
    const photoFileName = document.getElementById('photo-file-name');
    let positionChangedByUser = false;

    const getIndonesianValidationMessage = function (field) {
        const validity = field.validity;

        if (validity.badInput || validity.typeMismatch) {
            return field.dataset.messageInvalid || 'Nilai yang dimasukkan tidak valid.';
        }

        if (validity.valueMissing) {
            return field.dataset.messageRequired || 'Kolom ini wajib diisi.';
        }

        if (validity.rangeUnderflow) {
            return field.dataset.messageMin || 'Nilai yang dimasukkan terlalu kecil.';
        }

        if (validity.rangeOverflow) {
            return field.dataset.messageMax || 'Nilai yang dimasukkan terlalu besar.';
        }

        if (validity.stepMismatch) {
            return field.dataset.messageStep || 'Format angka yang dimasukkan tidak valid.';
        }

        return 'Periksa kembali nilai yang dimasukkan.';
    };

    measurementForm.addEventListener('invalid', function (event) {
        const field = event.target;

        if (!(field instanceof HTMLInputElement) && !(field instanceof HTMLSelectElement)) {
            return;
        }

        field.setCustomValidity('');
        field.setCustomValidity(getIndonesianValidationMessage(field));
    }, true);

    measurementForm.addEventListener('input', function (event) {
        const field = event.target;

        if (typeof field.setCustomValidity === 'function') {
            field.setCustomValidity('');
        }

        if (field.name === 'posisi_pengukuran') {
            positionInputs.forEach(function (positionInput) {
                positionInput.setCustomValidity('');
            });
        }
    });

    positionInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            positionChangedByUser = true;
        });
    });

    const updatePositionRecommendation = function () {
        const measurementDate = new Date(dateInput.value + 'T00:00:00');

        if (Number.isNaN(measurementDate.getTime())) {
            return;
        }

        const ageInDays = Math.floor((measurementDate - birthDate) / 86400000);
        const recommendedPosition = ageInDays <= 730 ? 'terlentang' : 'berdiri';
        const recommendationText = recommendedPosition === 'terlentang'
            ? 'Untuk usia ini, posisi yang dianjurkan adalah terlentang (panjang badan).'
            : 'Untuk usia ini, posisi yang dianjurkan adalah berdiri (tinggi badan).';

        positionHelp.textContent = recommendationText + ' Sistem tetap mengoreksi selisih 0,7 cm bila posisi berbeda.';

        if (!positionChangedByUser) {
            const recommendedInput = positionInputs.find(function (input) {
                return input.value === recommendedPosition;
            });

            if (recommendedInput) {
                recommendedInput.checked = true;
            }
        }
    };

    dateInput.addEventListener('change', updatePositionRecommendation);
    updatePositionRecommendation();

    photoInput.addEventListener('change', function () {
        const file = photoInput.files[0];
        photoFileName.textContent = file ? file.name : 'Pilih gambar';
    });
});
</script>
@endpush
