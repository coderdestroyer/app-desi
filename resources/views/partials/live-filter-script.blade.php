<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('analisaFilterForm');
    const tableContainer = document.getElementById('tableContainer');
    if (!form || !tableContainer) return;

    const provinsiSelect = document.getElementById('filterProvinsi');
    const kabupatenSelect = document.getElementById('filterKabupaten');
    const tahunSelect = document.getElementById('filterTahun');
    const searchInput = document.getElementById('filterSearch');

    let debounceTimer = null;

    function showLoading() {
        tableContainer.style.opacity = '0.5';
        tableContainer.style.pointerEvents = 'none';
    }

    function hideLoading() {
        tableContainer.style.opacity = '1';
        tableContainer.style.pointerEvents = 'auto';
    }

    function updateKabupatenDropdown(kabupatens) {
        if (!kabupatenSelect || !kabupatens) return;
        const currentVal = kabupatenSelect.value;
        kabupatenSelect.innerHTML = '<option value="">Semua Wilayah (Provinsi & Kab/Kota)</option>';
        
        kabupatens.forEach(kab => {
            const opt = document.createElement('option');
            opt.value = kab.kab_id;
            opt.textContent = kab.nama_kabupaten;
            if (String(kab.kab_id) === String(currentVal)) {
                opt.selected = true;
            }
            kabupatenSelect.appendChild(opt);
        });
    }

    function fetchFilteredData(targetUrl = null) {
        showLoading();

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        // Clear empty keys
        for (const [key, value] of Array.from(params.entries())) {
            if (!value) params.delete(key);
        }

        let requestUrl = targetUrl || (form.action + '?' + params.toString());

        fetch(requestUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            if (data.html) {
                tableContainer.innerHTML = data.html;
                window.history.pushState(null, '', requestUrl);
                if (data.kabupatens) {
                    updateKabupatenDropdown(data.kabupatens);
                }
                bindPaginationLinks();
            }
        })
        .catch(err => {
            console.error('Live filter error:', err);
        })
        .finally(() => {
            hideLoading();
        });
    }

    function bindPaginationLinks() {
        const links = tableContainer.querySelectorAll('a.page-link, nav a');
        links.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                if (url && url !== '#') {
                    fetchFilteredData(url);
                }
            });
        });
    }

    // Change Listeners for Dropdowns
    if (provinsiSelect) provinsiSelect.addEventListener('change', () => fetchFilteredData());
    if (kabupatenSelect) kabupatenSelect.addEventListener('change', () => fetchFilteredData());
    if (tahunSelect) tahunSelect.addEventListener('change', () => fetchFilteredData());

    const btnSearch = document.getElementById('btnSearchSubmit');

    // Input listener with 300ms Debounce & Enter Key Interceptor for Search Box
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();
                clearTimeout(debounceTimer);
                fetchFilteredData();
            }
        });

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchFilteredData();
            }, 300);
        });
    }

    if (btnSearch) {
        btnSearch.addEventListener('click', function (e) {
            e.preventDefault();
            clearTimeout(debounceTimer);
            fetchFilteredData();
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        clearTimeout(debounceTimer);
        fetchFilteredData();
    });

    bindPaginationLinks();
});
</script>
