// ---------- NIK: hitung digit ----------

const nikInput = document.getElementById('nikInput');
const nikCounter = document.getElementById('nikCounter');

if (nikInput) {

    function updateNikCounter() {
        nikInput.value = nikInput.value
            .replace(/[^0-9]/g, '')
            .slice(0, 16);

        nikCounter.textContent = `${nikInput.value.length}/16 Digit`;
    }

    nikInput.addEventListener('input', updateNikCounter);

    updateNikCounter();
}


// ---------- PILIH ORANG TUA ----------

const ortuSearchInput = document.getElementById('ortuSearchInput');
const ortuDropdown = document.getElementById('ortuDropdown');
const ortuOptions = Array.from(document.querySelectorAll('.ortu-option'));
const ortuClearBtn = document.getElementById('ortuClearBtn');
const ortuSelectedCard = document.getElementById('ortuSelectedCard');
const ortuSelectedName = document.getElementById('ortuSelectedName');
const ortuSelectedMeta = document.getElementById('ortuSelectedMeta');
const ortuSelectedAvatar = document.getElementById('ortuSelectedAvatar');
const orangTuaIdInput = document.getElementById('orangTuaIdInput');
const btnGantiOrtu = document.getElementById('btnGantiOrtu');
const sameAddressCheck = document.getElementById('sameAddressCheck');
const alamatTextarea = document.getElementById('alamatTextarea');

let selectedOrtuAlamat = '';

function openDropdown() {
    if (ortuDropdown) {
        ortuDropdown.classList.add('open');
    }
}

function closeDropdown() {
    if (ortuDropdown) {
        ortuDropdown.classList.remove('open');
    }
}

function filterOrtu() {

    const q = ortuSearchInput.value
        .toLowerCase()
        .trim();

    ortuOptions.forEach(opt => {

        const nama = (opt.dataset.nama || '').toLowerCase();

        const match = nama.includes(q);

        opt.style.display = match ? 'flex' : 'none';

    });

    openDropdown();
}

function selectOrtu(opt) {

    orangTuaIdInput.value = opt.dataset.id;

    selectedOrtuAlamat = opt.dataset.alamat || '';

    ortuSelectedName.textContent = opt.dataset.nama;

    ortuSelectedAvatar.textContent =
        opt.dataset.nama.charAt(0).toUpperCase();

    ortuSelectedMeta.innerHTML = '';

    if (opt.dataset.alamat) {

        const s = document.createElement('span');

        s.innerHTML = `
            <i class="fa-solid fa-location-dot"></i>
            ${opt.dataset.alamat}
        `;

        ortuSelectedMeta.appendChild(s);
    }

    if (opt.dataset.telepon) {

        const s = document.createElement('span');

        s.innerHTML = `
            <i class="fa-solid fa-phone"></i>
            ${opt.dataset.telepon}
        `;

        ortuSelectedMeta.appendChild(s);
    }

    ortuSearchInput.value = opt.dataset.nama;
    ortuSearchInput.readOnly = true;

    ortuClearBtn.style.display = 'flex';

    ortuSelectedCard.classList.add('show');

    closeDropdown();

    if (sameAddressCheck.checked) {
        alamatTextarea.value = selectedOrtuAlamat;
        alamatTextarea.readOnly = true;
    }
}

function resetOrtu() {

    orangTuaIdInput.value = '';

    selectedOrtuAlamat = '';

    ortuSearchInput.value = '';
    ortuSearchInput.readOnly = false;

    ortuClearBtn.style.display = 'none';

    ortuSelectedCard.classList.remove('show');

    ortuOptions.forEach(opt => {
        opt.style.display = 'flex';
    });

    alamatTextarea.readOnly = false;

    ortuSearchInput.focus();
}

if (ortuSearchInput) {

    ortuSearchInput.addEventListener('focus', () => {

        if (!orangTuaIdInput.value) {
            openDropdown();
        }

    });

    ortuSearchInput.addEventListener('input', filterOrtu);

    ortuOptions.forEach(opt => {

        opt.addEventListener('click', () => {
            selectOrtu(opt);
        });

    });

    ortuClearBtn.addEventListener('click', resetOrtu);

    btnGantiOrtu.addEventListener('click', resetOrtu);

    document.addEventListener('click', (e) => {

        if (
            !e.target.closest('.ortu-search-box') &&
            !e.target.closest('.ortu-dropdown')
        ) {
            closeDropdown();
        }

    });

}


// ---------- SAMA DENGAN ALAMAT ORANG TUA ----------

if (sameAddressCheck) {

    sameAddressCheck.addEventListener('change', () => {

        if (
            sameAddressCheck.checked &&
            selectedOrtuAlamat
        ) {

            alamatTextarea.value = selectedOrtuAlamat;
            alamatTextarea.readOnly = true;

        } else {

            alamatTextarea.readOnly = false;

        }

    });

}


// ---------- KALKULATOR USIA POSYANDU ----------

const tanggalLahirInput =
    document.getElementById('tanggalLahirInput');

const usiaValue =
    document.getElementById('usiaValue');

const usiaTag =
    document.getElementById('usiaTag');

function updateUsia() {

    if (!tanggalLahirInput.value) {

        usiaValue.textContent = 'Usia: -';
        usiaTag.textContent = '-';

        return;
    }

    const lahir = new Date(tanggalLahirInput.value);
    const now = new Date();

    let months =
        (now.getFullYear() - lahir.getFullYear()) * 12 +
        (now.getMonth() - lahir.getMonth());

    if (now.getDate() < lahir.getDate()) {
        months--;
    }

    if (months < 0) {
        months = 0;
    }

    usiaValue.textContent = `Usia: ${months} Bulan`;

    if (months < 24) {

        usiaTag.textContent =
            'Baduta (Bawah Dua Tahun)';

    } else if (months <= 59) {

        usiaTag.textContent =
            'Balita (Dua–Lima Tahun)';

    } else {

        usiaTag.textContent =
            'Di Luar Rentang Balita';
    }
}

if (tanggalLahirInput) {

    tanggalLahirInput.addEventListener(
        'change',
        updateUsia
    );

    updateUsia();
}
