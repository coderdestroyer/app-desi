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
    <style>
        .map-section {
            background: #ffffff;
            padding: 90px 10%;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-top: 1px solid #eef2f0;
            border-bottom: 1px solid #eef2f0;
        }
        
        .map-section-header {
            text-align: center;
            max-width: 800px;
            margin-bottom: 45px;
        }
        
        .map-section-header h5 {
            display: inline-block;
            padding: 8px 18px;
            background: #EEF8F2;
            color: #1E5D41;
            border-radius: 30px;
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .map-section-header h2 {
            font-size: 38px;
            font-weight: 800;
            color: #145239;
            margin-bottom: 15px;
            letter-spacing: -1px;
        }
        
        .map-section-header p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .map-section-card {
            width: 100%;
            max-width: 1100px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(20, 82, 57, 0.08);
            padding: 24px;
            position: relative;
            margin-bottom: 40px;
            overflow: hidden;
        }
        
        #landing-map {
            width: 100%;
            height: 500px;
            border-radius: 16px;
            z-index: 1;
            background-color: #f7faf8;
        }
        
        /* Map Legend */
        .map-legend {
            margin-top: 20px;
            background: #f7faf8;
            padding: 15px 25px;
            border-radius: 12px;
            border: 1px solid rgba(20, 82, 57, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .map-legend h4 {
            font-size: 14px;
            font-weight: 600;
            color: #145239;
            margin: 0;
        }
        
        .legend-items {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #555;
            font-weight: 500;
        }
        
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .legend-color.color-1 { background-color: #8ce0b7; }
        .legend-color.color-2 { background-color: #3db27c; }
        .legend-color.color-3 { background-color: #217d56; }
        .legend-color.color-4 { background-color: #145239; }
        .legend-color.color-5 { background-color: #0d3826; }
        
        .map-section-action {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }
        
        .btn-explore {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            padding: 18px 40px;
            box-shadow: 0 8px 20px rgba(20, 82, 57, 0.15);
        }
        
        .btn-explore i {
            font-size: 18px;
        }
        
        /* Custom styles for leaflet popup card */
        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(20, 82, 57, 0.08);
            padding: 6px;
        }
        
        .leaflet-popup-tip {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }
        
        .map-popup-card {
            padding: 5px;
            font-family: 'Poppins', sans-serif;
        }
        
        .map-popup-title {
            font-size: 15px;
            font-weight: 700;
            color: #145239;
            margin-bottom: 6px;
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
        }
        
        .map-popup-value {
            font-size: 13px;
            color: #555;
            line-height: 1.5;
        }
        
        .map-popup-value strong {
            color: #145239;
            font-size: 14px;
            font-weight: 700;
        }
    </style>

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

        <div class="hero-image">

            <img src="{{ asset('images/gedung-dpmptsp.jpg') }}" alt="Gedung DPMPTSP">

        </div>

    </section>
    
    {{-- GIS MAP SECTION --}}
    <section id="peta-ringkas" class="map-section">
        <div class="map-section-header">
            <h5>Peta GIS Sumatera</h5>
            <h2>Visualisasi Potensi Wilayah</h2>
            <p>Pemetaan intensitas potensi ekonomi dan investasi di 10 Provinsi Pulau Sumatera berdasarkan skala produk regional domestik bruto (PDRB) tahun {{ $latestYear }}.</p>
        </div>
        <div class="map-section-card">
            <div id="landing-map"></div>
            
            {{-- LEGEND --}}
            <div class="map-legend">
                <h4>Skala Potensi Ekonomi & Investasi</h4>
                <div class="legend-items">
                    <div class="legend-item"><span class="legend-color color-1"></span><span>Rendah</span></div>
                    <div class="legend-item"><span class="legend-color color-2"></span><span>Cukup</span></div>
                    <div class="legend-item"><span class="legend-color color-3"></span><span>Sedang</span></div>
                    <div class="legend-item"><span class="legend-color color-4"></span><span>Tinggi</span></div>
                    <div class="legend-item"><span class="legend-color color-5"></span><span>Sangat Tinggi</span></div>
                </div>
            </div>
        </div>
        <div class="map-section-action">
            <a href="{{ route('investment.map') }}" class="btn1 btn-explore">
                <i class="fa-solid fa-map-location-dot"></i> Eksplorasi Peta Investasi Detail
            </a>
        </div>
    </section>
    {{-- ABOUT --}}
    <!-- @include('landing.about-section') -->
    {{-- FOOTER --}}
    {{-- @include('partials.landing.footer') --}}
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        window.provinsiInvestasi = @json($provinsiInvestasi);
        
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
                    mouseover: function(e) {
                        const layer = e.target;
                        layer.setStyle({
                            weight: 3,
                            color: '#FFD54F',
                            fillOpacity: 0.95
                        });
                        layer.bringToFront();
                    },
                    mouseout: function(e) {
                        geojsonLayer.resetStyle(e.target);
                    },
                    click: function(e) {
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
                        circle.on('mouseover', function(e) {
                            this.setStyle({ color: '#FFD54F', weight: 4 });
                            this.openPopup();
                        });
                        circle.on('mouseout', function(e) {
                            this.setStyle({ color: '#ffffff', weight: 2 });
                        });
                    });
                });
        });
    </script>
</body>
</html>