<section id="tentang" class="about">
    
        <div class="about-image">

            <img src="{{ asset('images/gedung-dpmptsp.jpg') }}"
                alt="Gedung DPMPTSP">

        </div>

        <div class="about-text">

            <h5>Tentang Kami</h5>

            <h2>

                Dinas Penanaman Modal dan
                Pelayanan Terpadu Satu Pintu
                Provinsi Sumatera Utara

            </h2>

            <p>

                DPMPTSP Provinsi Sumatera Utara
                merupakan instansi pemerintah
                yang bertugas memberikan pelayanan
                perizinan, meningkatkan investasi,
                serta mendukung pembangunan ekonomi
                daerah melalui sistem pelayanan
                yang efektif, transparan,
                dan terintegrasi.

            </p>

            <div class="about-list">

                <div>

                    <i class="fa-solid fa-circle-check"></i>

                    Pelayanan Perizinan

                </div>

                <div>

                    <i class="fa-solid fa-circle-check"></i>

                    Analisis Potensi Wilayah

                </div>

                <div>

                    <i class="fa-solid fa-circle-check"></i>

                    Dashboard GIS

                </div>

                <div>

                    <i class="fa-solid fa-circle-check"></i>

                    Analisis PDRB

                </div>

            </div>

        </div>

    </section>

    <!-- =========================
            STATISTIK
    ========================== -->

    <section class="stats">

        <div class="card">

            <h1 class="counter"
                data-target="{{ \App\Models\Kabupaten::count() }}">

                0

            </h1>

            <p>Kabupaten / Kota</p>

        </div>

        <div class="card">

            <h1 class="counter"
                data-target="{{ \App\Models\Sektor::count() }}">

                0

            </h1>

            <p>Sektor Ekonomi</p>

        </div>

        <div class="card">

            <h1 class="counter"
                data-target="4">

                0

            </h1>

            <p>Metode Analisis</p>

        </div>

        <div class="card">

            <h1 class="counter"
                data-target="100">

                0

            </h1>

            <p>% Data Terintegrasi</p>

        </div>

    </section>





    <!-- =========================
            VISI MISI
    ========================== -->

    <section class="visi">

        <h5>

            Visi & Misi

        </h5>

        <div class="visi-grid">

            <div class="visi-card">

                <i class="fa-solid fa-eye"></i>

                <h3>

                    Visi

                </h3>

                <p>

                       "Kolaborasi SUMUT Berkah menuju Sumatera Utara yang Unggul, Maju dan Berkelanjutan"
                </p>

            </div>

            <div class="visi-card">

                <i class="fa-solid fa-bullseye"></i>

                <h3>

                    Misi

                </h3>

                <ul>

                    <li>Meningkatkan Kualitas Sumber Daya Manusia (SDM).</li>

                    <li>Menjaga Stabilitas Makro Ekonomi Daerah.</li>

                    <li>Mengembangkan dan Menata Infrastruktur yang Berkualitas, Estetik dan Ramah Lingkungan.</li>

                    <li>Menyediakan data ekonomi yang akurat.</li>
                    
                    <li>Memperkuat ketahanan sosial, dan budaya untuk membangun masyarakat Sumut yang tangguh.</li>
                </ul>

            </div>

        </div>

    </section>
        <!-- =========================
            LAYANAN
    ========================== -->

    <section class="layanan">

        <div class="section-title">

            <h5>Layanan Kami</h5>

            <h2>Fitur Dashboard Analisis</h2>

            <p>

                Dashboard ini membantu pengguna dalam
                melakukan analisis ekonomi regional
                menggunakan beberapa metode analisis
                yang umum digunakan oleh BPS maupun
                pemerintah daerah.

            </p>

        </div>

        <div class="layanan-grid">

            <div class="layanan-card">

                <i class="fa-solid fa-chart-line"></i>

                <h3>Location Quotient</h3>

                <p>

                    Menentukan sektor basis maupun
                    sektor non basis berdasarkan
                    data PDRB daerah.

                </p>

            </div>

            <div class="layanan-card">

                <i class="fa-solid fa-chart-column"></i>

                <h3>Shift Share</h3>

                <p>

                    Menganalisis perubahan struktur
                    ekonomi dan daya saing suatu daerah.

                </p>

            </div>

            <div class="layanan-card">

                <i class="fa-solid fa-layer-group"></i>

                <h3>Tipologi Klassen</h3>

                <p>

                    Mengelompokkan wilayah berdasarkan
                    pertumbuhan ekonomi dan kontribusi
                    terhadap PDRB.

                </p>

            </div>

            <div class="layanan-card">

                <i class="fa-solid fa-map-location-dot"></i>

                <h3>GIS</h3>

                <p>

                    Menampilkan hasil analisis
                    menggunakan visualisasi peta
                    secara interaktif.

                </p>

            </div>

            <div class="layanan-card">

                <i class="fa-solid fa-database"></i>

                <h3>Tipology Sektor</h3>

                <p>

                    Mengelompokkan sektor ekonomi berdasarkan tingkat pertumbuhan dan kontribusinya untuk mengidentifikasi 
                    sektor unggulan

                </p>

            </div>

            <div class="layanan-card">

                <i class="fa-solid fa-file-arrow-down"></i>

                <h3>Export Laporan</h3>

                <p>

                    Mengunduh hasil analisis dalam
                    bentuk laporan yang siap dicetak.

                </p>

            </div>

        </div>

    </section>



    <!-- =========================
            FLOW
    ========================== -->

    <section class="flow">

        <div class="section-title">

            <h5>Alur Penggunaan</h5>

            <h2>Cara Menggunakan Website</h2>

        </div>

        <div class="flow-container">

            <div class="flow-item">

                <div class="circle">

                    <i class="fa-solid fa-right-to-bracket"></i>

                </div>

                <h3>01</h3>

                <p>Login ke Sistem</p>

            </div>

            <div class="arrow">

                <i class="fa-solid fa-arrow-right"></i>

            </div>

            <div class="flow-item">

                <div class="circle">

                    <i class="fa-solid fa-keyboard"></i>

                </div>

                <h3>02</h3>

                <p>Input Data PDRB</p>

            </div>

            <div class="arrow">

                <i class="fa-solid fa-arrow-right"></i>

            </div>

            <div class="flow-item">

                <div class="circle">

                    <i class="fa-solid fa-calculator"></i>

                </div>

                <h3>03</h3>

                <p>Pilih Metode Analisis</p>

            </div>

            <div class="arrow">

                <i class="fa-solid fa-arrow-right"></i>

            </div>

            <div class="flow-item">

                <div class="circle">

                    <i class="fa-solid fa-chart-pie"></i>

                </div>

                <h3>04</h3>

                <p>Lihat Hasil Analisis</p>

            </div>

        </div>

    </section>



    <!-- =========================
            KEUNGGULAN
    ========================== -->

    <section class="unggulan">

        <div class="section-title">

            <h5>Keunggulan</h5>

            <h2>Mengapa Menggunakan Dashboard Ini?</h2>

        </div>

        <div class="unggulan-grid">

            <div class="unggulan-item">

                <i class="fa-solid fa-bolt"></i>

                <h3>Cepat</h3>

                <p>

                    Proses analisis dilakukan
                    secara otomatis hanya
                    dalam hitungan detik.

                </p>

            </div>

            <div class="unggulan-item">

                <i class="fa-solid fa-shield-halved"></i>

                <h3>Akurat</h3>

                <p>

                    Menggunakan metode analisis
                    ekonomi regional yang
                    banyak digunakan
                    oleh instansi pemerintah.

                </p>

            </div>

            <div class="unggulan-item">

                <i class="fa-solid fa-map"></i>

                <h3>Interaktif</h3>

                <p>

                    Hasil analisis dapat
                    divisualisasikan melalui
                    peta digital berbasis GIS.

                </p>

            </div>

            <div class="unggulan-item">

                <i class="fa-solid fa-cloud-arrow-down"></i>

                <h3>Laporan</h3>

                <p>

                    Hasil analisis dapat
                    diunduh sebagai laporan
                    dalam format PDF.

                </p>

            </div>

        </div>

    </section>



    <!-- =========================
            CTA
    ========================== -->

    <section class="cta">

        <div class="cta-box">

            <h2>

                Siap Melakukan Analisis?

            </h2>

            <p>

                Gunakan Dashboard Analisis Potensi
                Investasi untuk memperoleh informasi
                mengenai sektor unggulan, pertumbuhan
                ekonomi, serta visualisasi data
                berbasis GIS secara cepat dan akurat.

            </p>

            <a href="{{ route('home') }}">

                Mulai Analisis

            </a>

        </div>

    </section>
        <!-- =========================
            FAQ
    ========================== -->

   <section class="faq">

        <div class="section-title">

            <h5>Pertanyaan Umum</h5>

            <h2>Frequently Asked Questions</h2>

        </div>

        <div class="faq-container">

            <div class="faq-item">

                <button class="faq-question">

                    Apa itu metode Location Quotient (LQ)?

                    <i class="fa-solid fa-plus"></i>

                </button>

                <div class="faq-answer">

                    <p>

                        Location Quotient (LQ) merupakan metode
                        yang digunakan untuk mengetahui sektor
                        basis maupun sektor non basis berdasarkan
                        kontribusi suatu sektor terhadap PDRB.

                    </p>

                </div>

            </div>

            <div class="faq-item">

                <button class="faq-question">

                    Apa itu Shift Share?

                    <i class="fa-solid fa-plus"></i>

                </button>

                <div class="faq-answer">

                    <p>

                        Shift Share digunakan untuk mengetahui
                        pertumbuhan ekonomi serta daya saing
                        suatu sektor dibandingkan wilayah acuan.

                    </p>

                </div>

            </div>

            <div class="faq-item">

                <button class="faq-question">

                    Apa itu Tipologi Klassen?

                    <i class="fa-solid fa-plus"></i>

                </button>

                <div class="faq-answer">

                    <p>

                        Tipologi Klassen digunakan untuk
                        mengelompokkan daerah berdasarkan
                        tingkat pertumbuhan ekonomi dan
                        kontribusinya.

                    </p>

                </div>

            </div>

             <div class="faq-item">

                <button class="faq-question">

                    Apa itu Tipology Sektor?

                    <i class="fa-solid fa-plus"></i>

                </button>

                <div class="faq-answer">

                    <p>

                        Mengelompokkan sektor ekonomi berdasarkan tingkat pertumbuhan dan kontribusinya 
                        untuk mengidentifikasi sektor unggulan serta sektor yang perlu dikembangkan

                    </p>

                </div>

            </div>

            <div class="faq-item">

                <button class="faq-question">

                    Siapa yang dapat menggunakan website ini?

                    <i class="fa-solid fa-plus"></i>

                </button>

                <div class="faq-answer">

                    <p>

                        Website ini dapat digunakan oleh
                        operator, pemerintah daerah,
                        peneliti, akademisi, maupun
                        masyarakat umum.

                    </p>

                </div>

            </div>

        </div>

    </section>



    <!-- =========================
            KONTAK
    ========================== -->

    <section class="contact">

        <div class="section-title">

            <h5>Hubungi Kami</h5>

            <h2>Informasi Kontak</h2>

        </div>

        <div class="contact-grid">

            <div class="contact-card">

                <i class="fa-solid fa-location-dot"></i>

                <h3>Alamat</h3>

                <p>

                    Jl. K.H. Wahid Hasyim No.8A, Merdeka, 
                    Kec. Medan Baru, Kota Medan, Sumatera Utara 20154, Indonesia.

                </p>

            </div>

            <div class="contact-card">

                <i class="fa-solid fa-phone"></i>

                <h3>Telepon</h3>

                <p>

                    (061) 4514614

                </p>

            </div>

            <div class="contact-card">

                <i class="fa-solid fa-envelope"></i>

                <h3>Email</h3>

                <p>

                    dpmptsp@sumutprov.go.id

                </p>

            </div>

        </div>

    </section>



    <!-- =========================
            GOOGLE MAPS
    ========================== -->

    <section class="maps">

        <iframe

           src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.058729154579!2d98.65581707497314!3d3.5739699964002023!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30312fd63f5fe24d%3A0x84645c5d9a30054a!2sDinas%20Penanaman%20Modal%20dan%20PTSP%20Prov.%20Sumut!5e0!3m2!1sid!2sid!4v1783478328320!5m2!1sid!2sid" 
           width="600" height="450" style="border:0;" 
           allowfullscreen="" loading="lazy" 
           referrerpolicy="strict-origin-when-cross-origin">

        </iframe>

    </section>



    <!-- =========================
            FOOTER
    ========================== -->

    @include('partials.landing.footer')