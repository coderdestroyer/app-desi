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
            <span class="animate-pulse">Memuat Halaman</span>
            <span class="inline-flex gap-0.5 text-emerald-600 font-bold">
                <span class="animate-bounce" style="animation-delay: 0ms">.</span>
                <span class="animate-bounce" style="animation-delay: 150ms">.</span>
                <span class="animate-bounce" style="animation-delay: 300ms">.</span>
            </span>
        </div>
    </div>
</div>

<script>
    (function () {
        const loader = document.getElementById('pageLoader');
        if (!loader) return;

        let isHiding = false;

        function hideLoader() {
            if (isHiding) return;
            isHiding = true;
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
                loader.style.pointerEvents = 'none';
                isHiding = false;
            }, 300);
        }

        function showLoader() {
            isHiding = false;
            loader.style.display = 'flex';
            loader.style.pointerEvents = 'auto';
            requestAnimationFrame(() => {
                loader.style.opacity = '1';
            });
        }

        // Hide loader on initial load completion
        if (document.readyState === 'complete') {
            hideLoader();
        } else {
            window.addEventListener('load', hideLoader);
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(hideLoader, 200);
            });
        }

        // Safety timeout fallback (3 seconds max)
        setTimeout(hideLoader, 3000);

        // Show loader on page navigation / unload
        window.addEventListener('beforeunload', function () {
            showLoader();
        });

        // Handle page restoration from back/forward cache
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                hideLoader();
            }
        });

        // Handle clicks on internal navigation links
        document.addEventListener('click', function (e) {
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
                link.hasAttribute('download')) {
                return;
            }

            showLoader();
        });

        // Handle non-ajax form submissions
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (form && !form.hasAttribute('data-no-loader') && form.target !== '_blank') {
                showLoader();
            }
        });
    })();
</script>
