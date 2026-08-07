import './bootstrap';
import '../css/app.css';
import '../css/comparison.css';

import Alpine from 'alpinejs';

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

import Chart from 'chart.js/auto';

window.Chart = Chart;

window.Alpine = Alpine;

Alpine.start();


document.addEventListener('DOMContentLoaded', () => {

    const menuButton =
        document.getElementById('mobileMenuButton');

    const navigation =
        document.getElementById('mainNavigation');


    if (menuButton && navigation) {

        menuButton.addEventListener('click', () => {

            navigation.classList.toggle('show');

        });

    }

});

document.addEventListener('DOMContentLoaded', () => {


    /*
    |--------------------------------------------------------------------------
    | Mobile Navigation
    |--------------------------------------------------------------------------
    */

    const menuButton =
        document.getElementById('mobileMenuButton');

    const navigation =
        document.getElementById('mainNavigation');


    if (menuButton && navigation) {

        menuButton.addEventListener('click', () => {

            navigation.classList.toggle('show');


            const icon =
                menuButton.querySelector('i');


            if (navigation.classList.contains('show')) {

                icon.classList.remove('fa-bars');

                icon.classList.add('fa-xmark');

            } else {

                icon.classList.remove('fa-xmark');

                icon.classList.add('fa-bars');

            }

        });

    }



    /*
    |--------------------------------------------------------------------------
    | FAQ Accordion
    |--------------------------------------------------------------------------
    */

    const faqItems =
        document.querySelectorAll('.faq-item');


    faqItems.forEach((item) => {

        const question =
            item.querySelector('.faq-question');

        const answer =
            item.querySelector('.faq-answer');


        question.addEventListener('click', () => {


            const isActive =
                item.classList.contains('active');


            /*
             * Tutup semua FAQ terlebih dahulu
             */

            faqItems.forEach((otherItem) => {

                otherItem.classList.remove('active');

                const otherAnswer =
                    otherItem.querySelector('.faq-answer');

                otherAnswer.style.maxHeight = null;

            });


            /*
             * Buka FAQ yang dipilih
             */

            if (!isActive) {

                item.classList.add('active');

                answer.style.maxHeight =
                    answer.scrollHeight + 'px';

            }

        });

    });



    /*
    |--------------------------------------------------------------------------
    | Navbar Scroll Effect
    |--------------------------------------------------------------------------
    */

    const header =
        document.getElementById('mainHeader');


    window.addEventListener('scroll', () => {

        if (!header) {
            return;
        }


        if (window.scrollY > 30) {

            header.classList.add('scrolled');

        } else {

            header.classList.remove('scrolled');

        }

    });



    /*
    |--------------------------------------------------------------------------
    | Back To Top
    |--------------------------------------------------------------------------
    */

    const backToTop =
        document.getElementById('backToTop');


    if (backToTop) {

        window.addEventListener('scroll', () => {

            if (window.scrollY > 500) {

                backToTop.classList.add('show');

            } else {

                backToTop.classList.remove('show');

            }

        });


        backToTop.addEventListener('click', () => {

            window.scrollTo({

                top: 0,

                behavior: 'smooth'

            });

        });

    }



    /*
    |--------------------------------------------------------------------------
    | Reveal Animation
    |--------------------------------------------------------------------------
    */

    const revealElements =
        document.querySelectorAll('.reveal');


    const revealObserver =
        new IntersectionObserver(

            (entries) => {

                entries.forEach((entry) => {

                    if (entry.isIntersecting) {

                        entry.target.classList.add('visible');

                    }

                });

            },

            {
                threshold: 0.15
            }

        );


    revealElements.forEach((element) => {

        revealObserver.observe(element);

    });


});

document.addEventListener('DOMContentLoaded', () => {

    const mapElement =
        document.getElementById('investmentMap');


    if (!mapElement) {
        return;
    }


    const map = L.map('investmentMap', {
        center: [2.8, 99.0],
        zoom: 7,
        zoomControl: true
    });


    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }
    ).addTo(map);


    const investmentLocations = [

        {
            name: 'Kota Medan',
            sector: 'industri',
            region: 'medan',
            latitude: 3.5952,
            longitude: 98.6722
        },

        {
            name: 'Deli Serdang',
            sector: 'pertanian',
            region: 'deli-serdang',
            latitude: 3.4202,
            longitude: 98.7041
        },

        {
            name: 'Simalungun',
            sector: 'pariwisata',
            region: 'simalungun',
            latitude: 2.9782,
            longitude: 99.2786
        }

    ];


    const markerLayer =
        L.layerGroup().addTo(map);


    function displayMarkers(data) {

        markerLayer.clearLayers();


        data.forEach((location) => {

            L.marker([
                location.latitude,
                location.longitude
            ])
            .bindPopup(`
                <strong>${location.name}</strong>
                <br>
                Sektor: ${location.sector}
            `)
            .addTo(markerLayer);

        });

    }


    displayMarkers(investmentLocations);


    const filterButton =
        document.getElementById('applyMapFilter');


    filterButton?.addEventListener('click', () => {

        const selectedRegion =
            document.getElementById('regionFilter').value;

        const selectedSector =
            document.getElementById('sectorFilter').value;


        const filteredLocations =
            investmentLocations.filter((location) => {

                const regionMatch =
                    selectedRegion === 'all' ||
                    location.region === selectedRegion;

                const sectorMatch =
                    selectedSector === 'all' ||
                    location.sector === selectedSector;


                return regionMatch && sectorMatch;

            });


        displayMarkers(filteredLocations);

    });

});
/* ==========================================
        NAVBAR SCROLL
========================================== */

const navbar = document.getElementById("navbar");

window.addEventListener("scroll", () => {

    if (window.scrollY > 80) {

        navbar.classList.add("scrolled");

    } else {

        navbar.classList.remove("scrolled");

    }

});


/* ==========================================
        COUNTER
========================================== */

const counters = document.querySelectorAll(".counter");

const speed = 80;

const startCounter = () => {

    counters.forEach(counter => {

        const update = () => {

            const target = +counter.getAttribute("data-target");

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

};

const statSection = document.querySelector(".stats");

let counterStarted = false;

window.addEventListener("scroll", () => {

    const top = statSection.getBoundingClientRect().top;

    if (top < window.innerHeight - 120 && !counterStarted) {

        counterStarted = true;

        startCounter();

    }

});


/* ==========================================
        FAQ
========================================== */

const faqs = document.querySelectorAll(".faq-item");

faqs.forEach(item => {

    const btn = item.querySelector(".faq-question");

    btn.addEventListener("click", () => {

        faqs.forEach(f => {

            if (f !== item) {

                f.classList.remove("active");

            }

        });

        item.classList.toggle("active");

    });

});


/* ==========================================
        BACK TO TOP
========================================== */

const topBtn = document.getElementById("topBtn");

window.addEventListener("scroll", () => {

    if (window.scrollY > 500) {

        topBtn.style.display = "block";

    } else {

        topBtn.style.display = "none";

    }

});

topBtn.onclick = () => {

    window.scrollTo({

        top:0,

        behavior:"smooth"

    });

};


/* ==========================================
        SMOOTH LINK
========================================== */

document.querySelectorAll('a[href^="#"]').forEach(anchor=>{

    anchor.addEventListener("click",function(e){

        e.preventDefault();

        const target=document.querySelector(this.getAttribute("href"));

        if(target){

            target.scrollIntoView({

                behavior:"smooth"

            });

        }

    });

});


/* ==========================================
        SCROLL ANIMATION
========================================== */

const observer = new IntersectionObserver((entries)=>{

    entries.forEach(entry=>{

        if(entry.isIntersecting){

            entry.target.classList.add("show");

        }

    });

},{
    threshold:.2
});

document.querySelectorAll(

".about,.stats,.visi,.layanan,.flow,.unggulan,.faq,.contact,.maps,.cta"

).forEach(el=>{

    el.classList.add("hidden");

    observer.observe(el);

});


/* ==========================================
        BUTTON RIPPLE
========================================== */

const buttons = document.querySelectorAll(".btn1,.btn2,.cta a");

buttons.forEach(button=>{

    button.addEventListener("mouseenter",()=>{

        button.style.transform="translateY(-5px) scale(1.03)";

    });

    button.addEventListener("mouseleave",()=>{

        button.style.transform="translateY(0) scale(1)";

    });

});


/* ==========================================
        CARD HOVER
========================================== */

const cards = document.querySelectorAll(

".layanan-card,.stats .card,.unggulan-item,.visi-card,.contact-card"

);

cards.forEach(card=>{

    card.addEventListener("mousemove",(e)=>{

        const x=e.offsetX/card.offsetWidth-.5;

        const y=e.offsetY/card.offsetHeight-.5;

        card.style.transform=`
        rotateY(${x*8}deg)
        rotateX(${y*-8}deg)
        translateY(-8px)
        `;

    });

    card.addEventListener("mouseleave",()=>{

        card.style.transform="rotateY(0deg) rotateX(0deg) translateY(0px)";

    });

});


/* ==========================================
        HERO PARALLAX
========================================== */

window.addEventListener("scroll",()=>{

    const hero=document.querySelector(".hero");

    hero.style.backgroundPositionY=window.scrollY*0.4+"px";

});


/* ==========================================
        LOADING EFFECT
========================================== */


const resetFilterButton =
    document.getElementById('resetMapFilter');


resetFilterButton?.addEventListener('click', () => {

    const regionFilter =
        document.getElementById('regionFilter');

    const sectorFilter =
        document.getElementById('sectorFilter');


    regionFilter.value = 'all';

    sectorFilter.value = 'all';


    displayMarkers(investmentLocations);


    map.setView(
        [2.8, 99.0],
        7
    );

});