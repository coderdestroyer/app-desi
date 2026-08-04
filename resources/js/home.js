/* ==========================================
        HERO PARALLAX
========================================== */

const hero = document.querySelector(".hero");

if (hero) {

    window.addEventListener("scroll", () => {

        hero.style.backgroundPositionY =
            window.scrollY * 0.35 + "px";

    });

}


/* ==========================================
        BUTTON EFFECT
========================================== */

document.querySelectorAll(".btn1,.btn2").forEach(button => {

    button.addEventListener("mouseenter", () => {

        button.style.transform =
            "translateY(-5px) scale(1.03)";

    });

    button.addEventListener("mouseleave", () => {

        button.style.transform =
            "translateY(0) scale(1)";

    });

});


/* ==========================================
        CHANGE URL WHEN SCROLL
========================================== */

const about = document.querySelector("#tentang");

if (hero && about) {

    // Kalau user buka langsung dengan hash #tentang
    if (window.location.hash === "#tentang") {

        setTimeout(() => {

            const navbarHeight =
                document.querySelector(".main-header")?.offsetHeight || 90;

            window.scrollTo({

                top: about.offsetTop - navbarHeight,

                behavior: "instant"

            });

        }, 100);

    }

    window.addEventListener("scroll", () => {

        const trigger = about.offsetTop - 150; // Align with navbar active trigger

        if (window.scrollY >= trigger) {

            if (window.location.hash !== "#tentang") {

                history.replaceState(null, null, "#tentang");

            }

        } else {

            if (window.location.hash === "#tentang") {

                history.replaceState(null, null, window.location.pathname + window.location.search);

            }

        }

    });

}


/* ==========================================
        PAGE FADE IN
========================================== */

window.addEventListener("load", () => {

    document.body.style.opacity = "0";

    setTimeout(() => {

        document.body.style.transition = "opacity .8s";

        document.body.style.opacity = "1";

    }, 100);

});