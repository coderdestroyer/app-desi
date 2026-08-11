// ===============================
// Navbar Scroll Effect & ScrollSpy
// ===============================
window.addEventListener("scroll", () => {
    const navbar = document.querySelector(".main-header");
    if (navbar) {
        navbar.classList.toggle("scrolled", window.scrollY > 50);
    }
});

// ===============================
// DOM Ready
// ===============================
document.addEventListener("DOMContentLoaded", () => {

    const navHome = document.getElementById("nav-home");
    const navAbout = document.getElementById("nav-about");
    const overviewSection = document.getElementById("overview");
    const heroSection = document.getElementById("hero");

    if (navHome && navAbout && (overviewSection || heroSection)) {

        let isScrollingFromClick = false;
        let scrollTimeout;

        function updateActiveNav() {
            if (isScrollingFromClick) return;

            const navbarHeight = document.querySelector(".main-header")?.offsetHeight || 90;
            const scrollPos = window.scrollY + navbarHeight + 50;

            if (overviewSection && scrollPos >= overviewSection.offsetTop) {
                navHome.classList.remove("active");
                navAbout.classList.add("active");
            } else {
                navHome.classList.add("active");
                navAbout.classList.remove("active");
            }
        }

        function handleLinkClick(activeLink, inactiveLink) {
            isScrollingFromClick = true;
            activeLink?.classList.add("active");
            inactiveLink?.classList.remove("active");

            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                isScrollingFromClick = false;
                updateActiveNav();
            }, 800);
        }

        navHome.addEventListener("click", () => handleLinkClick(navHome, navAbout));
        navAbout.addEventListener("click", () => handleLinkClick(navAbout, navHome));

        // Listen for scroll & resize
        window.addEventListener("scroll", updateActiveNav, { passive: true });
        window.addEventListener("resize", updateActiveNav);

        // Run once on initial load (with slight delay for dynamic layout rendering)
        updateActiveNav();
        setTimeout(updateActiveNav, 300);
    }

    // ===============================
    // Mobile Hamburger Menu
    // ===============================
    const menuBtn = document.getElementById("mobileMenuButton");
    const navMenu = document.getElementById("mainNavigation");

    if (menuBtn && navMenu) {
        menuBtn.addEventListener("click", () => {
            navMenu.classList.toggle("show");
        });
    }

});