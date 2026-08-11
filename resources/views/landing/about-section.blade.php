    <!-- =========================
            EXECUTIVE DASHBOARD SECTION
    ========================== -->

    <section id="overview" class="dashboard-grid-section bg-[#f8faf9] pt-[35px] pb-10 px-4 sm:px-8 lg:px-[10%] flex flex-col items-center w-full border-t border-b border-[#eef2f0] transition-all duration-[1000ms]">
        <div class="section-title w-full">
            <h5>
                Overview
            </h5>
            <h2>
                Analisis & Visualisasi Potensi Investasi
            </h2>
            <p>
                Monitoring komprehensif indikator makroekonomi, sebaran GIS, kontribusi sektor potensial, serta tren investasi regional.
            </p>
        </div>

        <div class="w-full max-w-[1200px] flex flex-col gap-[30px]">
            {{-- TOP BLOCK: CHARTS & MAP --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-[30px] w-full">

                {{-- Left Column: Stacked Charts --}}
                <div class="flex flex-col gap-[25px] w-full">
                    {{-- Top 5 Potential Sectors --}}
                    <div class="bg-white rounded-[20px] p-[25px] shadow-[0_10px_35px_rgba(0,0,0,0.05)] border border-[#145239]/5 flex flex-col w-full">
                        <div class="flex justify-between items-center mb-[20px] flex-wrap gap-[10px]">
                            <h3 class="text-base font-bold text-[#145239] m-0 flex items-center gap-[10px]">
                                <i class="fa-solid fa-pie-chart"></i> Top 5 Sektor Potensial ({{ $latestYear }})
                            </h3>
                        </div>
                        <div class="relative w-full" style="min-height: 230px;">
                            <canvas id="topSectorsChart"></canvas>
                        </div>
                    </div>

                    {{-- Trend line chart --}}
                    <div class="bg-white rounded-[20px] p-[25px] shadow-[0_10px_35px_rgba(0,0,0,0.05)] border border-[#145239]/5 flex flex-col w-full">
                        <div class="flex justify-between items-center mb-[20px] flex-wrap gap-[10px]">
                            <h3 class="text-base font-bold text-[#145239] m-0 flex items-center gap-[10px]">
                                <i class="fa-solid fa-line-chart"></i> Tren Investasi & Ekspor-Impor
                            </h3>
                            <script>
                                function trendProvinceDropdown() {
                                    return {
                                        provinceDropdownOpen: false,
                                        provinceSearch: '',
                                        selectedProvince: 'SUMATERA UTARA',
                                        provinces: @json(array_keys($provinsiInvestasi)),
                                        get filteredProvinces() {
                                            if (!this.provinceSearch) return this.provinces;
                                            return this.provinces.filter(p => p.toLowerCase().includes(this.provinceSearch.toLowerCase()));
                                        },
                                        selectProvince(prov) {
                                            this.selectedProvince = prov;
                                            this.provinceDropdownOpen = false;
                                            this.provinceSearch = '';
                                            if (typeof updateTrendChart === 'function') {
                                                updateTrendChart(prov);
                                            }
                                        }
                                    };
                                }
                            </script>

                            <div x-data="trendProvinceDropdown()" class="relative z-[60] min-w-[200px]">
                                <div @click="provinceDropdownOpen = !provinceDropdownOpen; if(provinceDropdownOpen) $nextTick(() => $refs.provinceSearchInput.focus())"
                                    class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] bg-white text-sm flex items-center justify-between cursor-pointer hover:border-[#145239] transition-colors shadow-2xs">
                                    <span x-text="selectedProvince || 'Pilih Provinsi...'" :class="selectedProvince ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="provinceDropdownOpen ? 'rotate-180' : ''"></i>
                                </div>

                                <div x-show="provinceDropdownOpen"
                                    x-cloak
                                    @click.outside="provinceDropdownOpen = false"
                                    x-transition.origin.top.duration.150ms
                                    class="absolute z-50 right-0 mt-1.5 w-[220px] bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                        <input type="text" x-model="provinceSearch" x-ref="provinceSearchInput" placeholder="Cari provinsi..."
                                            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] outline-none">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto space-y-0.5 text-xs">
                                        <template x-for="item in filteredProvinces" :key="item">
                                            <div @click="selectProvince(item)"
                                                :class="selectedProvince == item ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                <span x-text="item"></span>
                                                <i x-show="selectedProvince == item" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                            </div>
                                        </template>
                                        <div x-show="filteredProvinces.length === 0" class="px-3 py-2 text-slate-400 text-center italic text-xs">
                                            Tidak ditemukan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="relative w-full" style="min-height: 230px;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Right Column: GIS Map --}}
                <div class="w-full">
                    <div class="bg-white rounded-[20px] p-[25px] shadow-[0_10px_35px_rgba(0,0,0,0.05)] border border-[#145239]/5 h-full flex flex-col">
                        <div class="mb-[20px] flex-shrink-0">
                            <h3 class="text-base font-bold text-[#145239] m-0 flex items-center gap-[10px]">
                                <i class="fa-solid fa-map-location-dot"></i> Sebaran Potensi Ekonomi & GIS ({{ $latestYear }})
                            </h3>
                        </div>
                        <div id="landing-map" class="w-full rounded-lg z-10 flex-grow min-h-[340px]"></div>
                        <div class="mt-[15px] py-[10px] px-[15px] flex-shrink-0">
                            <h4 class="text-[13px] font-bold text-[#145239] mb-2">Skala Potensi Ekonomi (PDRB)</h4>
                            <div class="flex flex-wrap gap-2 text-xs text-[#555]">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded-[3px] bg-[#8ce0b7]"></span>
                                    <span>Rendah</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded-[3px] bg-[#3db27c]"></span>
                                    <span>Cukup</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded-[3px] bg-[#217d56]"></span>
                                    <span>Sedang</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded-[3px] bg-[#145239]"></span>
                                    <span>Tinggi</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-4 h-4 rounded-[3px] bg-[#0d3826]"></span>
                                    <span>Sangat Tinggi</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-center mt-4 flex-shrink-0">
                            <a href="{{ route('investment.map') }}" class="btn1 btn-explore inline-flex items-center gap-2.5 text-base py-[18px] px-10 bg-[#1E5D41] text-white rounded-full shadow-[0_8px_20px_rgba(20,82,57,0.15)] hover:bg-[#145239] transition-all duration-300 font-semibold">
                                <i class="fa-solid fa-map-location-dot"></i> Eksplorasi Peta Investasi Detail
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            {{-- BOTTOM BLOCK: METRICS ROW --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-[25px] w-full">
                {{-- Card 1: Realisasi --}}
                <div class="metric-card bg-white rounded-[18px] p-6 flex items-center gap-5 shadow-[0_10px_30px_rgba(0,0,0,0.04)] border border-[#145239]/5 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_30px_rgba(20,82,57,0.08)] cursor-pointer">
                    <div class="w-[54px] h-[54px] rounded-[14px] bg-[#EEF8F2] text-[#1E5D41] flex items-center justify-center text-[22px] shrink-0">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="flex flex-col">
                        <h3 class="text-xl font-bold text-[#145239] mb-1.5">Rp {{ number_format($totalRealisasi / 1000000000000, 2, ',', '.') }} T</h3>
                        <p class="text-[12px] text-[#666] font-semibold uppercase tracking-[0.5px]">Total Realisasi Investasi ({{ $latestYear }})</p>
                    </div>
                </div>

                {{-- Card 2: PDRB Tertinggi --}}
                <div class="metric-card bg-white rounded-[18px] p-6 flex items-center gap-5 shadow-[0_10px_30px_rgba(0,0,0,0.04)] border border-[#145239]/5 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_30px_rgba(20,82,57,0.08)] cursor-pointer">
                    <div class="w-[54px] h-[54px] rounded-[14px] bg-[#EEF8F2] text-[#1E5D41] flex items-center justify-center text-[22px] shrink-0">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <div class="flex flex-col">
                        <h3 class="text-xl font-bold text-[#145239] mb-1.5">{{ $pdrbTertinggiNama }}</h3>
                        <p class="text-[12px] text-[#666] font-semibold uppercase tracking-[0.5px]">PDRB Tertinggi (Rp {{ number_format($pdrbTertinggiNilai / 1000000000000, 2, ',', '.') }} T)</p>
                    </div>
                </div>

                {{-- Card 3: Proyek Aktif --}}
                <div class="metric-card bg-white rounded-[18px] p-6 flex items-center gap-5 shadow-[0_10px_30px_rgba(0,0,0,0.04)] border border-[#145239]/5 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_30px_rgba(20,82,57,0.08)] cursor-pointer">
                    <div class="w-[54px] h-[54px] rounded-[14px] bg-[#EEF8F2] text-[#1E5D41] flex items-center justify-center text-[22px] shrink-0">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <div class="flex flex-col">
                        <h3 class="text-xl font-bold text-[#145239] mb-1.5">{{ $jumlahProyek }} Proyek</h3>
                        <p class="text-[12px] text-[#666] font-semibold uppercase tracking-[0.5px]">Jumlah Proyek Aktif (IPRO)</p>
                    </div>
                </div>
            </div>

            {{-- STATS BLOCK --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-[25px] mt-[30px] w-full stats">
                <div class="card bg-white py-10 px-[25px] rounded-[20px] text-center transition-all duration-[400ms] shadow-[0_10px_25px_rgba(0,0,0,0.08)] cursor-pointer hover:-translate-y-2.5 hover:shadow-[0_20px_40px_rgba(0,0,0,0.12)]">
                    <h1 class="counter text-[52px] text-[#0B5D3D] mb-2.5 font-bold" data-target="{{ \App\Models\Kabupaten::count() }}">0</h1>
                    <p class="text-[#666] text-[17px]">Kabupaten / Kota</p>
                </div>

                <div class="card bg-white py-10 px-[25px] rounded-[20px] text-center transition-all duration-[400ms] shadow-[0_10px_25px_rgba(0,0,0,0.08)] cursor-pointer hover:-translate-y-2.5 hover:shadow-[0_20px_40px_rgba(0,0,0,0.12)]">
                    <h1 class="counter text-[52px] text-[#0B5D3D] mb-2.5 font-bold" data-target="{{ \App\Models\Sektor::count() }}">0</h1>
                    <p class="text-[#666] text-[17px]">Sektor Ekonomi</p>
                </div>

                <div class="card bg-white py-10 px-[25px] rounded-[20px] text-center transition-all duration-[400ms] shadow-[0_10px_25px_rgba(0,0,0,0.08)] cursor-pointer hover:-translate-y-2.5 hover:shadow-[0_20px_40px_rgba(0,0,0,0.12)]">
                    <h1 class="counter text-[52px] text-[#0B5D3D] mb-2.5 font-bold" data-target="4">0</h1>
                    <p class="text-[#666] text-[17px]">Metode Analisis</p>
                </div>

                <div class="card bg-white py-10 px-[25px] rounded-[20px] text-center transition-all duration-[400ms] shadow-[0_10px_25px_rgba(0,0,0,0.08)] cursor-pointer hover:-translate-y-2.5 hover:shadow-[0_20px_40px_rgba(0,0,0,0.12)]">
                    <h1 class="counter text-[52px] text-[#0B5D3D] mb-2.5 font-bold" data-target="100">0</h1>
                    <p class="text-[#666] text-[17px]">% Data Terintegrasi</p>
                </div>
            </div>
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

                <h3>Tipologi Sektor</h3>

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

                    Apa itu Tipologi Sektor?

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
