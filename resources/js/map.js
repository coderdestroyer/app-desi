document.addEventListener("DOMContentLoaded", () => {

    // =====================================================
    // CEK DATA LOKASI
    // =====================================================
    if (typeof lokasi === "undefined") {
        console.error("Data lokasi tidak ditemukan!");
        return;
    }

    console.log("Jumlah lokasi:", lokasi.length);


    // =====================================================
    // INISIALISASI MAP
    // =====================================================
    const map = L.map("map", {
        zoomControl: true,
        scrollWheelZoom: true,
    });

    map.setView([0.5, 101.5], 6);


    // =====================================================
    // TILE LAYER
    // =====================================================
    L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            attribution: "&copy; OpenStreetMap",
            maxZoom: 18,
        }
    ).addTo(map);


    // =====================================================
    // ICON MARKERS
    // =====================================================
    // Green Icon untuk Kabupaten / Kota biasa
    const kabupatenIcon = L.icon({
        iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png",
        shadowUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
    });

    // Red Icon khusus Ibukota Provinsi (Tampil saat Zoom-Out)
    const ibukotaIcon = L.icon({
        iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png",
        shadowUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
    });


    // =====================================================
    // GROUP MARKER PER LAYER (Dynamic Zoom Control)
    // =====================================================
    const ibukotaGroup = L.layerGroup().addTo(map);   // Selalu Tampil (Hanya Ibukota Provinsi - Tag Merah)
    const kabupatenGroup = L.layerGroup().addTo(map); // Tampil hanya saat Zoom-In (Kabupaten/Kota biasa - Tag Hijau)

    const markers = [];


    // =====================================================
    // FUNGSI MENAMPILKAN DETAIL DAERAH
    // =====================================================
    function tampilkanDetail(item) {

        const detailKosong = document.getElementById("detailKosong");
        const detailDaerah = document.getElementById("detailDaerah");
        const namaDaerah = document.getElementById("namaDaerah");
        const jenisDaerah = document.getElementById("jenisDaerah");
        const provinsiDaerah = document.getElementById("provinsiDaerah");
        const statusDaerah = document.getElementById("statusDaerah");
        const sektorDaerah = document.getElementById("sektorDaerah");
        const koordinatDaerah = document.getElementById("koordinatDaerah");

        if (detailKosong) detailKosong.style.display = "none";
        if (detailDaerah) detailDaerah.style.display = "block";

        if (namaDaerah) namaDaerah.innerText = item.nama;
        if (provinsiDaerah) {
            provinsiDaerah.innerText = item.provinsi || "Sumatera";
        }
        if (jenisDaerah) {
            jenisDaerah.innerText = item.is_ibukota ? "Ibukota Provinsi" : "Kabupaten / Kota";
        }

        if (sektorDaerah) sektorDaerah.innerText = "Memuat data...";
        if (statusDaerah) statusDaerah.innerText = "Memuat...";

        if (koordinatDaerah) {
            koordinatDaerah.innerText = `${item.latitude}, ${item.longitude}`;
        }

        const url = `/map/analysis/${encodeURIComponent(item.nama)}`;
        console.log("Mengambil:", url);

        fetch(url, {
            headers: { "Accept": "application/json" },
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || "Terjadi kesalahan pada server.");
                }
                return data;
            })
            .then((data) => {
                if (!data.success) {
                    if (sektorDaerah) sektorDaerah.innerText = data.message || "Belum ada sektor unggulan";
                    if (statusDaerah) statusDaerah.innerText = "Belum ada data";
                    return;
                }

                const sektor = data.sektor;

                if (Array.isArray(sektor) && sektor.length > 0) {
                    sektorDaerah.innerHTML = sektor
                        .map((namaSektor, index) => {
                            return `
                            <div class="sektor-item">
                                <span class="sektor-number">${index + 1}.</span>
                                <span class="sektor-name">${namaSektor}</span>
                            </div>
                        `;
                        })
                        .join("");
                } else if (typeof sektor === "string" && sektor.trim() !== "") {
                    const daftarSektor = sektor
                        .split(/<br\s*\/?>|\n/i)
                        .map(item => item.trim())
                        .filter(item => item !== "");

                    if (daftarSektor.length > 0) {
                        sektorDaerah.innerHTML = daftarSektor
                            .map((namaSektor, index) => {
                                return `
                                <div class="sektor-item">
                                    <span class="sektor-number">${index + 1}.</span>
                                    <span class="sektor-name">${namaSektor}</span>
                                </div>
                            `;
                            })
                            .join("");
                    } else {
                        sektorDaerah.innerText = "Belum ada sektor unggulan";
                    }
                } else {
                    sektorDaerah.innerText = "Belum ada sektor unggulan";
                }

                if (statusDaerah) {
                    let status = data.status || data.kategori || "Sektor Cepat Maju dan Cepat Tumbuh";
                    if (data.tahun) {
                        status += `\nTahun ${data.tahun}`;
                    }
                    statusDaerah.innerText = status;
                }
            })
            .catch((error) => {
                console.error("ERROR MENGAMBIL ANALISIS:", error);
                if (sektorDaerah) sektorDaerah.innerText = "Gagal mengambil data.";
                if (statusDaerah) statusDaerah.innerText = "Terjadi kesalahan";
            });
    }


    // =====================================================
    // LOOP SEMUA LOKASI & PEMBAGIAN LAYER
    // =====================================================
    lokasi.forEach((item) => {
        // Hapus/abaikan tag level provinsi
        if (item.type === "provinsi") {
            return;
        }

        const latitude = parseFloat(item.latitude);
        const longitude = parseFloat(item.longitude);

        if (Number.isNaN(latitude) || Number.isNaN(longitude)) {
            console.warn("Koordinat tidak valid:", item);
            return;
        }

        // Ibukota Provinsi = Tag Merah (Selalu tampil)
        // Kabupaten biasa = Tag Hijau (Tampil saat Zoom-In)
        const isIbukota = item.is_ibukota;
        const icon = isIbukota ? ibukotaIcon : kabupatenIcon;
        const targetGroup = isIbukota ? ibukotaGroup : kabupatenGroup;
        const zIndexOffset = isIbukota ? 1000 : 0;

        const marker = L.marker(
            [latitude, longitude],
            {
                icon: icon,
                zIndexOffset: zIndexOffset,
            }
        ).addTo(targetGroup);

        const labelTipe = isIbukota ? "Ibukota Provinsi" : "Kabupaten / Kota";
        marker.bindPopup(`
            <div class="popup-card">
                <div class="popup-title">
                    📍 ${item.nama} (${labelTipe})
                </div>
                <div class="popup-desc">
                    Klik untuk melihat informasi investasi.
                </div>
            </div>
        `);

        marker.on("click", () => {
            tampilkanDetail(item);
        });

        markers.push({
            nama: item.nama.toLowerCase(),
            marker: marker,
            lat: latitude,
            lng: longitude,
            item: item,
        });
    });


    // =====================================================
    // LOGIKA FILTER MARKER BERDASARKAN LEVEL ZOOM
    // =====================================================
    const ZOOM_THRESHOLD = 8; // Threshold level zoom (Jauh < 8, Dekat >= 8)

    function updateZoomVisibility() {
        const currentZoom = map.getZoom();
        console.log("Current Zoom Level:", currentZoom);

        if (currentZoom < ZOOM_THRESHOLD) {
            // Zoom Out (Jauh): Sembunyikan kabupaten biasa, tampilkan hanya ibukota (tag merah)
            if (map.hasLayer(kabupatenGroup)) {
                map.removeLayer(kabupatenGroup);
            }
        } else {
            // Zoom In (Dekat): Tampilkan seluruh kabupaten/kota (tag hijau)
            if (!map.hasLayer(kabupatenGroup)) {
                map.addLayer(kabupatenGroup);
            }
        }
    }

    map.on("zoomend", updateZoomVisibility);
    updateZoomVisibility(); // Inisialisasi awal


    // =====================================================
    // SEARCH KABUPATEN / KOTA
    // =====================================================
    const search = document.getElementById("searchKabupaten");

    if (search) {
        search.addEventListener("keyup", function () {
            const keyword = this.value.trim().toLowerCase();
            if (keyword === "") return;

            const ditemukan = markers.find((item) => item.nama.includes(keyword));

            if (ditemukan) {
                // Jika marker sedang tersembunyi karena zoom-out, hidupkan sementara
                if (!map.hasLayer(kabupatenGroup)) {
                    map.addLayer(kabupatenGroup);
                }

                map.flyTo([ditemukan.lat, ditemukan.lng], 10, { duration: 1.5 });
                ditemukan.marker.openPopup();
                tampilkanDetail(ditemukan.item);
            }
        });
    }

    map.on("click", () => {
        map.closePopup();
    });
});