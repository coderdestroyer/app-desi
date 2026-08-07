<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForms = document.querySelectorAll('#analisaFilterForm, form[data-live-filter]');
    
    filterForms.forEach(form => {
        initLiveFilter(form);
    });

    if (filterForms.length === 0) {
        document.querySelectorAll('form').forEach(form => {
            const hasSearch = form.querySelector('input[name="search"]');
            const hasFilterSelect = form.querySelector('select[name="role"], select[name="status"], select[name="struktur"], select[name="seksi"], select[name="kategori"], select[name="provinsi_id"], select[name="kabupaten_id"], select[name="tahun"], select[name="per_page"], select[name="kode_kabupaten"], select[name="kode_kecamatan"]');
            if (hasSearch || hasFilterSelect) {
                initLiveFilter(form);
            }
        });
    }

    function initLiveFilter(form) {
        if (!form || form.dataset.liveFilterInitialized) return;
        form.dataset.liveFilterInitialized = 'true';
        form.setAttribute('data-no-loader', 'true');

        let tableContainer = document.getElementById('tableContainer') 
            || form.closest('div, main, body')?.querySelector('#tableContainer, [data-table-container], section.overflow-hidden:has(table), div.overflow-hidden:has(table)');

        if (!tableContainer) {
            let nextEl = form.closest('section, div')?.nextElementSibling;
            while (nextEl) {
                if (nextEl.querySelector('table')) {
                    tableContainer = nextEl;
                    break;
                }
                nextEl = nextEl.nextElementSibling;
            }
        }

        if (!tableContainer) return;
        tableContainer.setAttribute('data-no-loader', 'true');

        let debounceTimer = null;
        let currentFetchController = null;

        function showLoading() {
            tableContainer.style.transition = 'all 0.2s ease-in-out';
            tableContainer.style.opacity = '0.35';
            tableContainer.style.filter = 'blur(3px)';
            tableContainer.style.pointerEvents = 'none';
        }

        function hideLoading() {
            tableContainer.style.opacity = '1';
            tableContainer.style.filter = 'none';
            tableContainer.style.pointerEvents = 'auto';
        }

        function fetchFilteredData(targetUrl = null) {
            if (currentFetchController) {
                currentFetchController.abort();
            }
            currentFetchController = new AbortController();

            showLoading();

            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            for (const [key, value] of Array.from(params.entries())) {
                if (!value) params.delete(key);
            }

            let requestUrl = targetUrl || (form.action + (form.action.includes('?') ? '&' : '?') + params.toString());

            fetch(requestUrl, {
                signal: currentFetchController.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json, text/html'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text().then(htmlText => ({ htmlText }));
                }
            })
            .then(data => {
                let htmlContent = null;
                if (data.html) {
                    htmlContent = data.html;
                } else if (data.htmlText) {
                    htmlContent = data.htmlText;
                }

                if (htmlContent) {
                    if (htmlContent.includes('<!DOCTYPE html>') || htmlContent.includes('<html') || htmlContent.includes('<body') || htmlContent.includes('id="tableContainer"')) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(htmlContent, 'text/html');
                        const newContainer = doc.getElementById('tableContainer') || doc.querySelector('[data-table-container]') || doc.querySelector('section.overflow-hidden:has(table), div.overflow-hidden:has(table)');
                        if (newContainer) {
                            htmlContent = newContainer.innerHTML;
                        }
                    }

                    tableContainer.innerHTML = htmlContent;
                    window.history.pushState(null, '', requestUrl);

                    if (data.kabupatens) {
                        const kabupatenSelect = form.querySelector('#filterKabupaten, select[name="kabupaten_id"], select[name="kode_kabupaten"]');
                        if (kabupatenSelect) {
                            const currentVal = kabupatenSelect.value;
                            kabupatenSelect.innerHTML = '<option value="">Semua Wilayah (Provinsi & Kab/Kota)</option>';
                            data.kabupatens.forEach(kab => {
                                const opt = document.createElement('option');
                                opt.value = kab.kab_id || kab.kode_kabupaten;
                                opt.textContent = kab.nama_kabupaten;
                                if (String(kab.kab_id || kab.kode_kabupaten) === String(currentVal)) {
                                    opt.selected = true;
                                }
                                kabupatenSelect.appendChild(opt);
                            });
                        }
                    }

                    bindPaginationAndResets();
                }
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                console.error('Live filter error:', err);
            })
            .finally(() => {
                if (!currentFetchController || !currentFetchController.signal.aborted) {
                    hideLoading();
                }
            });
        }

        function bindPaginationAndResets() {
            const links = tableContainer.querySelectorAll('a.page-link, nav a, .pagination a');
            links.forEach(link => {
                link.setAttribute('data-no-loader', 'true');
                link.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href && href !== '#' && !href.startsWith('javascript:')) {
                        e.preventDefault();
                        fetchFilteredData(href);
                    }
                });
            });

            const resetLinks = form.querySelectorAll('a[title*="Reset"], a[title*="reset"], a.reset-filter') 
                || document.querySelectorAll('a[title*="Reset"], a[title*="reset"]');
            resetLinks.forEach(link => {
                link.setAttribute('data-no-loader', 'true');
                if (link.dataset.liveResetBound) return;
                link.dataset.liveResetBound = 'true';
                link.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href && !href.includes('#') && !href.startsWith('javascript:')) {
                        e.preventDefault();
                        form.reset();
                        form.querySelectorAll('input[type="text"], input[type="search"]').forEach(input => input.value = '');
                        form.querySelectorAll('select').forEach(select => select.value = '');
                        fetchFilteredData(href);
                    }
                });
            });
        }

        form.querySelectorAll('select').forEach(select => {
            select.removeAttribute('onchange');
            select.addEventListener('change', () => {
                clearTimeout(debounceTimer);
                fetchFilteredData();
            });
        });

        form.querySelectorAll('input[type="text"], input[type="search"]').forEach(input => {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    e.stopPropagation();
                    clearTimeout(debounceTimer);
                    fetchFilteredData();
                }
            });

            input.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchFilteredData();
                }, 400);
            });
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            clearTimeout(debounceTimer);
            fetchFilteredData();
            return false;
        }, true);

        const btnSearch = form.querySelector('button[type="submit"], #btnSearchSubmit');
        if (btnSearch) {
            btnSearch.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                clearTimeout(debounceTimer);
                fetchFilteredData();
            }, true);
        }

        bindPaginationAndResets();
    }
});
</script>
