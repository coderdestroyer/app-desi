<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite([
        'resources/css/home.css',
        'resources/css/navbar.css',
        'resources/css/about.css',
        'resources/js/navbar.js',
        'resources/js/home.js',
        'resources/js/about.js',
    ])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
</head>

<body>

    {{-- NAVBAR --}}
    @include('partials.landing.navbar')

    {{-- HERO --}}
    <section id="hero" class="hero">

        <div class="hero-content">

            <h1>
                Dashboard Executive for Sumatera Investment
            </h1>

            <p>
                Dashboard eksekutif untuk investasi di Sumatera dirancang untuk
                memberikan gambaran cepat (high-level overview) bagi Gubernur,
                Bupati, Walikota, Investor dan Calon Investor serta para pengambil
                keputusan lain guna memantau Potensi Unggulan Daerah berdasarkan
                Produk Domestik Regional Bruto (PDRB) Atas Dasar Harga Konstan
                Menurut Lapangan Usaha, Realisasi Investasi, Realisasi Ekspor dan Impor,
                Realisasi Perdagangan Dalam Negeri di seluruh wilayah Pulau Sumatera.
            </p>

            <div class="hero-button">

                <a href="{{ route('analysis') }}" class="btn1">
                    Mulai Analisis
                </a>

                <a href="{{ route('comparison') }}" class="btn1">
                    Analisis Sektor
                </a>

            </div>

        </div>

        <div class="hero-image">

            <img src="{{ asset('images/gedung-dpmptsp.jpg') }}" alt="Gedung DPMPTSP">

        </div>

    </section>

    {{-- ABOUT --}}
    @include('landing.about-section')
    {{-- FOOTER --}}
    {{-- @include('partials.landing.footer') --}}
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.provinsiInvestasi = @json($provinsiInvestasi);
        window.topSectorsData = @json($topSectors);
        window.trendsData = @json($trendsData);

        document.addEventListener("DOMContentLoaded", () => {
            // Initialize map centered on Sumatra with zoom level 6
            const map = L.map("landing-map", {
                zoomControl: true,
                scrollWheelZoom: true,
                dragging: true
            }).setView([0.5, 101.5], 6);
            // Light, clean CartoDB Positron basemap
            L.tileLayer(
                "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
                {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                    maxZoom: 18,
                }
            ).addTo(map);
            let geojsonLayer;
            const sumateraCodes = ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21'];
            const values = Object.values(window.provinsiInvestasi);
            const minVal = Math.min(...values);
            const maxVal = Math.max(...values);
            const range = maxVal - minVal;
            function getColor(val) {
                if (!val) return '#8ce0b7';
                if (range === 0) return '#145239';
                const pct = (val - minVal) / range;
                if (pct > 0.8) return '#0d3826'; // Sangat Tinggi
                if (pct > 0.6) return '#145239'; // Tinggi
                if (pct > 0.4) return '#217d56'; // Sedang
                if (pct > 0.2) return '#3db27c'; // Cukup
                return '#8ce0b7'; // Rendah
            }
            function formatTriliun(num) {
                return 'Rp ' + (num / 1000000000000).toFixed(2) + ' Triliun';
            }
            function style(feature) {
                const name = feature.properties.PROVINSI.toUpperCase().trim();
                let matchedKey = name;
                if (name.includes("ACEH")) matchedKey = "ACEH";
                const val = window.provinsiInvestasi[matchedKey] || 0;

                return {
                    fillColor: getColor(val),
                    weight: 1.5,
                    opacity: 1,
                    color: '#ffffff',
                    fillOpacity: 0.85
                };
            }
            function onEachFeature(feature, layer) {
                const name = feature.properties.PROVINSI.trim();
                let matchedKey = name.toUpperCase().trim();
                if (matchedKey.includes("ACEH")) matchedKey = "ACEH";
                const val = window.provinsiInvestasi[matchedKey] || 0;

                const popupContent = `
                    <div class="map-popup-card">
                        <div class="map-popup-title">📍 Provinsi ${name}</div>
                        <div class="map-popup-value">Potensi Ekonomi (PDRB):<br><strong>${formatTriliun(val)}</strong></div>
                    </div>
                `;

                layer.bindPopup(popupContent, { closeButton: false, offset: L.point(0, -10) });

                layer.on({
                    mouseover: function (e) {
                        const layer = e.target;
                        layer.setStyle({
                            weight: 3,
                            color: '#FFD54F',
                            fillOpacity: 0.95
                        });
                        layer.bringToFront();
                    },
                    mouseout: function (e) {
                        geojsonLayer.resetStyle(e.target);
                    },
                    click: function (e) {
                        map.fitBounds(e.target.getBounds());
                        e.target.openPopup();
                    }
                });
            }
            // Fetch Sumatra boundaries from CDN
            fetch('https://cdn.jsdelivr.net/gh/denyherianto/indonesia-geojson-topojson-maps-with-38-provinces@main/GeoJSON/indonesia-38-provinces.geojson')
                .then(response => {
                    if (!response.ok) throw new Error('Network response error');
                    return response.json();
                })
                .then(data => {
                    const sumateraFeatures = data.features.filter(f => sumateraCodes.includes(f.properties.KODE_PROV));
                    const sumateraGeoJSON = {
                        type: "FeatureCollection",
                        features: sumateraFeatures
                    };
                    geojsonLayer = L.geoJSON(sumateraGeoJSON, {
                        style: style,
                        onEachFeature: onEachFeature
                    }).addTo(map);
                    map.fitBounds(geojsonLayer.getBounds(), { padding: [30, 30] });
                })
                .catch(error => {
                    console.error('Error loading geojson map:', error);
                    // Fallback using circles
                    const fallbackCentroids = [
                        { name: "Aceh", lat: 4.695, lng: 96.749 },
                        { name: "Sumatera Utara", lat: 2.115, lng: 99.545 },
                        { name: "Sumatera Barat", lat: -0.740, lng: 100.800 },
                        { name: "Riau", lat: 0.293, lng: 101.707 },
                        { name: "Jambi", lat: -1.610, lng: 103.613 },
                        { name: "Sumatera Selatan", lat: -3.319, lng: 103.914 },
                        { name: "Bengkulu", lat: -3.578, lng: 102.346 },
                        { name: "Lampung", lat: -4.559, lng: 105.407 },
                        { name: "Kepulauan Bangka Belitung", lat: -2.741, lng: 106.441 },
                        { name: "Kepulauan Riau", lat: 3.946, lng: 108.143 }
                    ];
                    fallbackCentroids.forEach(c => {
                        const matchedKey = c.name.toUpperCase().trim();
                        const val = window.provinsiInvestasi[matchedKey] || 0;
                        const circle = L.circleMarker([c.lat, c.lng], {
                            radius: 12 + ((val - minVal) / (range || 1)) * 18,
                            fillColor: getColor(val),
                            color: "#ffffff",
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.85
                        }).addTo(map);
                        const popupContent = `
                            <div class="map-popup-card">
                                <div class="map-popup-title">📍 Provinsi ${c.name}</div>
                                <div class="map-popup-value">Potensi Ekonomi (PDRB):<br><strong>${formatTriliun(val)}</strong></div>
                            </div>
                        `;
                        circle.bindPopup(popupContent, { closeButton: false });
                        circle.on('mouseover', function (e) {
                            this.setStyle({ color: '#FFD54F', weight: 4 });
                            this.openPopup();
                        });
                        circle.on('mouseout', function (e) {
                            this.setStyle({ color: '#ffffff', weight: 2 });
                        });
                    });
                });

            // =====================================================
            // CHART 1: TOP 5 POTENTIAL SECTORS
            // =====================================================
            const ctxSectors = document.getElementById("topSectorsChart").getContext("2d");
            const sectorNames = window.topSectorsData.map(s => {
                let name = s.nama_sektor;
                name = name.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                if (name.length > 30) name = name.substring(0, 28) + "..";
                return name;
            });
            const sectorValues = window.topSectorsData.map(s => s.total_pdrb / 1000000000000); // in Triliun

            new Chart(ctxSectors, {
                type: 'bar',
                data: {
                    labels: sectorNames,
                    datasets: [{
                        label: 'PDRB (Triliun Rp)',
                        data: sectorValues,
                        backgroundColor: '#1E5D41',
                        borderRadius: 8,
                        borderWidth: 0,
                        barThickness: 16
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return 'Rp ' + context.raw.toFixed(2) + ' Triliun';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                callback: function (value) { return 'Rp ' + value + ' T'; }
                            }
                        },
                        y: {
                            grid: { display: false }
                        }
                    }
                }
            });

            // =====================================================
            // CHART 2: TIME-SERIES LINE CHART (TRENDS)
            // =====================================================
            const ctxTrend = document.getElementById("trendChart").getContext("2d");
            let trendChart;

            window.updateTrendChart = function (provName) {
                const dataPoints = window.trendsData[provName] || [];
                const years = dataPoints.map(d => d.tahun);
                const investasi = dataPoints.map(d => d.investasi / 1000000000000); // T
                const ekspor = dataPoints.map(d => d.ekspor / 1000000000000); // T
                const impor = dataPoints.map(d => d.impor / 1000000000000); // T

                if (trendChart) {
                    trendChart.data.labels = years;
                    trendChart.data.datasets[0].data = investasi;
                    trendChart.data.datasets[1].data = ekspor;
                    trendChart.data.datasets[2].data = impor;
                    trendChart.update();
                } else {
                    trendChart = new Chart(ctxTrend, {
                        type: 'line',
                        data: {
                            labels: years,
                            datasets: [
                                {
                                    label: 'Realisasi Investasi',
                                    data: investasi,
                                    borderColor: '#1E5D41',
                                    backgroundColor: 'rgba(30, 93, 65, 0.08)',
                                    tension: 0.3,
                                    fill: true
                                },
                                {
                                    label: 'Ekspor',
                                    data: ekspor,
                                    borderColor: '#E65100',
                                    backgroundColor: 'transparent',
                                    tension: 0.3
                                },
                                {
                                    label: 'Impor',
                                    data: impor,
                                    borderColor: '#0288D1',
                                    backgroundColor: 'transparent',
                                    tension: 0.3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            return context.dataset.label + ': Rp ' + context.raw.toFixed(2) + ' Triliun';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { grid: { display: false } },
                                y: {
                                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                                    ticks: {
                                        callback: function (value) { return 'Rp ' + value + ' T'; }
                                    }
                                }
                            }
                        }
                    });
                }
            };

            // Initialize trend chart with default selected province (SUMATERA UTARA)
            updateTrendChart('SUMATERA UTARA');
        });
    </script>
</body>

</html>
