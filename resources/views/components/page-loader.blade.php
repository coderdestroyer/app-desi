<!-- GLOBAL PAGE LOADER SPINNER COMPONENT -->
<div id="pageLoader" class="fixed inset-0 z-[999999] flex flex-col items-center justify-center bg-slate-900/60 backdrop-blur-md transition-opacity duration-300 pointer-events-auto">
    <div class="relative flex flex-col items-center justify-center p-8 rounded-3xl bg-white/95 border border-white/50 shadow-2xl backdrop-blur-xl max-w-xs w-full mx-4 text-center transform transition-all duration-300 scale-100">
        <!-- Dual Ring Glowing Spinner -->
        <div class="relative w-20 h-20 mb-5 flex items-center justify-center">
            <!-- Outer Spinning Ring (Emerald Gradient) -->
            <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-[#145239] border-r-[#0F8A5F] animate-spin"></div>
            <!-- Inner Spinning Ring Reverse (Gold/Amber Accent) -->
            <div class="absolute inset-2 rounded-full border-4 border-transparent border-b-[#FFD54F] border-l-[#D8A62A] animate-[spin_1.2s_linear_infinite_reverse]"></div>
            <!-- Center Logo / Emblem -->
            <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center shadow-inner overflow-hidden p-1">
                <img src="{{ asset('images/logo-sumut.png') }}" alt="Logo Sumut" class="w-full h-full object-contain" onerror="this.src='https://ui-avatars.com/api/?name=SU&background=145239&color=FFD54F'">
            </div>
        </div>

        <!-- Animated Text -->
        <h4 class="text-xs font-extrabold tracking-widest text-[#145239] uppercase mb-1">DPMPTSP</h4>
        <div class="flex items-center justify-center gap-1 text-xs font-semibold text-slate-600">
            <span id="pageLoaderText" class="animate-pulse">Memuat Halaman</span>
            <span class="inline-flex gap-0.5 text-emerald-600 font-bold">
                <span class="animate-bounce" style="animation-delay: 0ms">.</span>
                <span class="animate-bounce" style="animation-delay: 150ms">.</span>
                <span class="animate-bounce" style="animation-delay: 300ms">.</span>
            </span>
        </div>
    </div>
</div>

<script>
    (function() {
        const loader = document.getElementById('pageLoader');
        const loaderTextEl = document.getElementById('pageLoaderText');
        if (!loader) return;

        let isHiding = false;
        let isNavigating = false;
        let hideTimer = null;
        let domContentTimer = null;
        let safetyTimer = null;

        function clearAllTimers() {
            if (hideTimer) {
                clearTimeout(hideTimer);
                hideTimer = null;
            }
            if (domContentTimer) {
                clearTimeout(domContentTimer);
                domContentTimer = null;
            }
            if (safetyTimer) {
                clearTimeout(safetyTimer);
                safetyTimer = null;
            }
        }

        function hideLoader() {
            if (isNavigating) return;
            if (isHiding) return;

            isHiding = true;
            loader.style.opacity = '0';
            hideTimer = setTimeout(() => {
                loader.style.display = 'none';
                loader.style.pointerEvents = 'none';
                isHiding = false;
                hideTimer = null;
            }, 300);
        }

        function showLoader(customText) {
            let text = customText || 'Memuat Halaman';
            if (loaderTextEl) {
                loaderTextEl.innerText = text;
            }

            isNavigating = true;
            clearAllTimers();
            isHiding = false;

            loader.style.display = 'flex';
            loader.style.pointerEvents = 'auto';
            requestAnimationFrame(() => {
                loader.style.opacity = '1';
            });
        }

        // Global functions for programmatic invocation
        window.showPageLoader = function(text) {
            showLoader(text);
        };

        window.hidePageLoader = function() {
            isNavigating = false;
            hideLoader();
        };

        // Listen for custom show-loader events
        window.addEventListener('show-loader', function(e) {
            const text = e.detail && e.detail.text ? e.detail.text : 'Memproses Data';
            showLoader(text);
        });

        window.addEventListener('hide-loader', function() {
            isNavigating = false;
            hideLoader();
        });

        // Hide loader on initial load completion
        safetyTimer = setTimeout(hideLoader, 3000);

        if (document.readyState === 'complete') {
            hideLoader();
        } else {
            window.addEventListener('load', hideLoader, {
                once: true
            });
            document.addEventListener('DOMContentLoaded', function() {
                domContentTimer = setTimeout(hideLoader, 200);
            }, {
                once: true
            });
        }

        // Show loader on page navigation / unload
        window.addEventListener('beforeunload', function() {
            showLoader(loaderTextEl ? loaderTextEl.innerText : 'Memuat Halaman');
        });

        // Handle page restoration from back/forward cache
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                isNavigating = false;
                hideLoader();
            }
        });

        // Handle clicks on internal navigation links & action buttons
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            const target = link.getAttribute('target');

            if (!href ||
                href.startsWith('#') ||
                href.startsWith('javascript:') ||
                href.startsWith('mailto:') ||
                href.startsWith('tel:') ||
                target === '_blank' ||
                e.ctrlKey ||
                e.metaKey ||
                link.hasAttribute('download') ||
                link.hasAttribute('data-no-loader')) {
                return;
            }

            if (link.href === window.location.href) {
                return;
            }

            // Determine custom text from attribute or link content
            let loaderText = link.getAttribute('data-loader-text');
            if (!loaderText) {
                const linkContent = (link.textContent || '').toLowerCase();
                if (linkContent.includes('hapus') || linkContent.includes('delete')) {
                    loaderText = 'Menghapus Data';
                } else if (linkContent.includes('simpan') || linkContent.includes('save') || linkContent.includes('tambah') || linkContent.includes('inisiasi')) {
                    loaderText = 'Menyimpan Data';
                } else {
                    loaderText = 'Memuat Halaman';
                }
            }

            showLoader(loaderText);
        });

        // Handle non-ajax form submissions dynamically
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form && !form.hasAttribute('data-no-loader') && form.target !== '_blank') {
                let loaderText = form.getAttribute('data-loader-text');

                if (!loaderText) {
                    const submitter = e.submitter;
                    const submitterText = submitter ? (submitter.textContent || '').toLowerCase() : '';
                    const formAction = (form.action || '').toLowerCase();
                    const formMethod = (form.method || 'get').toLowerCase();
                    const hasMethodDelete = form.querySelector('input[name="_method"][value="DELETE"], input[name="_method"][value="delete"]');

                    if (submitterText.includes('hapus') || submitterText.includes('delete') || hasMethodDelete || formAction.includes('destroy') || formAction.includes('delete')) {
                        loaderText = 'Menghapus Data';
                    } else if (submitterText.includes('simpan') || submitterText.includes('save') || submitterText.includes('tambah') || submitterText.includes('init') || submitterText.includes('update') || formMethod === 'post' || formAction.includes('save') || formAction.includes('store') || formAction.includes('update') || formAction.includes('init')) {
                        loaderText = 'Menyimpan Data';
                    } else if (submitterText.includes('cari') || submitterText.includes('filter') || submitterText.includes('search')) {
                        loaderText = 'Memuat Data';
                    } else {
                        loaderText = 'Memproses Data';
                    }
                }

                showLoader(loaderText);
            }
        });
    })();
</script>