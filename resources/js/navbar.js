// ===============================
// Navbar Scroll Effect
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

    // ===============================
    // Active menu saat scroll (Home)
    // ===============================
    if (window.location.pathname === "/") {

        const tentang = document.getElementById("tentang");

        const navHome = document.getElementById("nav-home");
        const navAbout = document.getElementById("nav-about");

        let isScrollingFromClick = false;
        let scrollTimeout;

        function setActive() {
            if (isScrollingFromClick) return;
            if (!tentang) return;

            if (window.scrollY < tentang.offsetTop - 150) {
                navHome?.classList.add("active");
                navAbout?.classList.remove("active");
            } else {
                navHome?.classList.remove("active");
                navAbout?.classList.add("active");
            }
        }

        function handleLinkClick(activeLink, inactiveLink) {
            isScrollingFromClick = true;
            activeLink?.classList.add("active");
            inactiveLink?.classList.remove("active");
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                isScrollingFromClick = false;
            }, 1000);
        }
        navHome?.addEventListener("click", () => handleLinkClick(navHome, navAbout));
        navAbout?.addEventListener("click", () => handleLinkClick(navAbout, navHome));
        
        setActive();
        window.addEventListener("scroll", setActive);
    }
    
    function handleLinkClick(activeLink, inactiveLink) {
            isScrollingFromClick = true;
            activeLink?.classList.add("active");
            inactiveLink?.classList.remove("active");

            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                isScrollingFromClick = false;
            }, 1000);
    }

        navHome?.addEventListener("click", () => handleLinkClick(navHome, navAbout));
        navAbout?.addEventListener("click", () => handleLinkClick(navAbout, navHome));
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