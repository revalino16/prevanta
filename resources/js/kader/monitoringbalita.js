const searchInput = document.getElementById('searchInput');
const genderFilter = document.getElementById('genderFilter');
const tabs = document.querySelectorAll('.tab');
const resetFilter = document.getElementById('resetFilter');
const visibleCount = document.getElementById('visibleCount');

let activeStatus = '';

function filterBalita() {
    const search = searchInput.value.toLowerCase().trim();
    const gender = genderFilter.value;

    const rows = document.querySelectorAll('.balita-row');

    let visible = 0;

    rows.forEach(row => {
        const name = row.dataset.name || '';
        const nik = row.dataset.nik || '';
        const rowGender = row.dataset.gender || '';
        const rowStatus = row.dataset.status || '';

        const matchSearch =
            name.includes(search) ||
            nik.includes(search);

        const matchGender =
            gender === '' ||
            rowGender === gender;

        const matchStatus =
            activeStatus === '' ||
            rowStatus === activeStatus;

        const show =
            matchSearch &&
            matchGender &&
            matchStatus;

        row.style.display = show ? 'flex' : 'none';

        if (show) {
            visible++;
        }
    });

    if (visibleCount) {
        visibleCount.textContent =
            `Menampilkan ${visible} Balita`;
    }
}


tabs.forEach(tab => {
    tab.addEventListener('click', () => {

        tabs.forEach(item => {
            item.classList.remove('active');
        });

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

        tabs.forEach(tab => {
            tab.classList.remove('active');
        });

        tabs[0].classList.add('active');

        filterBalita();
    });
}


if (searchInput) {
    searchInput.addEventListener('input', filterBalita);
}

if (genderFilter) {
    genderFilter.addEventListener('change', filterBalita);
}
