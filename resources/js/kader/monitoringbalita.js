// ── Server-side filter: submit form on search input (debounced) ──
const searchInput  = document.getElementById('searchInput');
const filterForm   = document.getElementById('filterForm');

let debounceTimer;

if (searchInput && filterForm) {
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            filterForm.submit();
        }, 400); // wait 400 ms after user stops typing
    });
}

// Gender select auto-submits via onchange in HTML (already set)
// Tab links navigate directly via href (already set in HTML)
