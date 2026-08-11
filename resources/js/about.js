/* ==========================================
        NAVBAR SCROLL
========================================== */

const navbar = document.getElementById("mainHeader");

if (navbar) {
    window.addEventListener("scroll", () => {

        if (window.scrollY > 80) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }

    });
}


/* ==========================================
        COUNTER
========================================== */

const counters = document.querySelectorAll(".counter");

const speed = 80;

function startCounter() {

    counters.forEach(counter => {

        const update = () => {

            const target = +counter.dataset.target;
            const count = +counter.innerText;

            const increment = Math.ceil(target / speed);

            if (count < target) {

                counter.innerText = count + increment;

                setTimeout(update, 25);

            } else {

                counter.innerText = target;

            }

        };

        update();

    });

}

const statSection = document.querySelector(".stats");

let counterStarted = false;

if (statSection) {

    window.addEventListener("scroll", () => {

        const top = statSection.getBoundingClientRect().top;

        if (top < window.innerHeight - 120 && !counterStarted) {

            counterStarted = true;

            startCounter();

        }

    });

}


/* ==========================================
        FAQ
========================================== */

const faqs = document.querySelectorAll(".faq-item");

faqs.forEach(item => {

    const btn = item.querySelector(".faq-question");

    if (btn) {

        btn.addEventListener("click", () => {

            faqs.forEach(f => {

                if (f !== item) {

                    f.classList.remove("active");

                }

            });

            item.classList.toggle("active");

        });

    }

});


/* ==========================================
        BACK TO TOP
========================================== */

const topBtn = document.getElementById("topBtn");

if (topBtn) {

    window.addEventListener("scroll", () => {

        if (window.scrollY > 500) {

            topBtn.style.display = "flex";

        } else {

            topBtn.style.display = "none";

        }

    });

    topBtn.addEventListener("click", () => {

        window.scrollTo({

            top: 0,

            behavior: "smooth"

        });

    });

}


/* ==========================================
        SMOOTH SCROLL
========================================== */

document.querySelectorAll('a[href^="#"]').forEach(anchor => {

    anchor.addEventListener("click", function (e) {

        const target = document.querySelector(this.getAttribute("href"));

        if (target) {

            e.preventDefault();

            const navbarHeight = document.querySelector(".main-header")?.offsetHeight || 90;

            const position = Math.max(0, target.offsetTop - navbarHeight);

            window.scrollTo({

                top: position,

                behavior: "smooth"

            });

        }

    });

});





/* ==========================================
        SCROLL ANIMATION
========================================== */

const observer = new IntersectionObserver((entries) => {

    entries.forEach(entry => {

        if (entry.isIntersecting) {

            entry.target.classList.add("show");

        }

    });

}, {

    threshold: 0.2

});

document.querySelectorAll(
    ".about,.stats,.visi,.layanan,.flow,.unggulan,.faq,.contact,.maps,.cta,.dashboard-grid-section"
).forEach(el => {

    el.classList.add("reveal-hidden");

    observer.observe(el);

});


/* ==========================================
        BUTTON EFFECT
========================================== */

document.querySelectorAll(".btn1,.btn2,.cta a").forEach(button => {

    button.addEventListener("mouseenter", () => {

        button.style.transform = "translateY(-5px) scale(1.03)";

    });

    button.addEventListener("mouseleave", () => {

        button.style.transform = "translateY(0) scale(1)";

    });

});


/* ==========================================
        CARD EFFECT
========================================== */

document.querySelectorAll(
    ".layanan-card,.stats .card,.unggulan-item,.visi-card,.contact-card"
).forEach(card => {

    card.addEventListener("mousemove", (e) => {

        const x = e.offsetX / card.offsetWidth - 0.5;
        const y = e.offsetY / card.offsetHeight - 0.5;

        card.style.transform =
            `rotateY(${x * 8}deg)
             rotateX(${y * -8}deg)
             translateY(-8px)`;

    });

    card.addEventListener("mouseleave", () => {

        card.style.transform =
            "rotateY(0deg) rotateX(0deg) translateY(0px)";

    });

});


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
        PAGE FADE IN
========================================== */

window.addEventListener("load", () => {

    document.body.style.opacity = "0";

    setTimeout(() => {

        document.body.style.transition = "opacity .8s";

        document.body.style.opacity = "1";

    }, 100);

});
/* ==========================================
        BACK TO HOME
========================================== */

// let redirectedHome = false;

// window.addEventListener("scroll", () => {

//     if (window.scrollY <= 10 && !redirectedHome) {

//         redirectedHome = true;

//     }

// });

// window.addEventListener("wheel", function(e){

//     if(window.scrollY <= 5 && e.deltaY < 0){

//         window.location.href = "/";

//     }

// });