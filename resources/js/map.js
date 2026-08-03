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

    map.setView([2.9, 99.2], 8);


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
    // Green Icon for Kabupaten
    const kabupatenIcon = L.icon({
        iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png",
        shadowUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
    });

    // Gold/Yellow Icon for Provinsi
    const provinsiIcon = L.icon({
        iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-gold.png",
        shadowUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
    });


    // =====================================================
    // GROUP MARKER
    // =====================================================
    const group = L.featureGroup().addTo(map);

    const markers = [];


    // =====================================================
    // FUNGSI MENAMPILKAN DETAIL DAERAH
    // =====================================================
    function tampilkanDetail(item) {

        const detailKosong =
            document.getElementById("detailKosong");

        const detailDaerah =
            document.getElementById("detailDaerah");

        const namaDaerah =
            document.getElementById("namaDaerah");

        const jenisDaerah =
            document.getElementById("jenisDaerah");

        const statusDaerah =
            document.getElementById("statusDaerah");

        const sektorDaerah =
            document.getElementById("sektorDaerah");

        const koordinatDaerah =
            document.getElementById("koordinatDaerah");


        // =================================================
        // TAMPILKAN PANEL DETAIL
        // =================================================
        if (detailKosong) {
            detailKosong.style.display = "none";
        }

        if (detailDaerah) {
            detailDaerah.style.display = "block";
        }


        // =================================================
        // NAMA & JENIS DAERAH
        // =================================================
        if (namaDaerah) {
            namaDaerah.innerText = item.nama;
        }

        if (jenisDaerah) {
            jenisDaerah.innerText = item.type === "provinsi" ? "Provinsi" : "Kabupaten / Kota";
        }


        // =================================================
        // LOADING
        // =================================================
        if (sektorDaerah) {
            sektorDaerah.innerText = "Memuat data...";
        }

        if (statusDaerah) {
            statusDaerah.innerText = "Memuat...";
        }


        // =================================================
        // KOORDINAT
        // =================================================
        if (koordinatDaerah) {

            koordinatDaerah.innerText =
                `${item.latitude}, ${item.longitude}`;

        }


        // =================================================
        // URL API
        // =================================================
        const url =
            `/map/analysis/${encodeURIComponent(item.nama)}`;

        console.log("Mengambil:", url);


        // =================================================
        // FETCH DATA SEKTOR UNGGULAN
        // =================================================
        fetch(url, {
            headers: {
                "Accept": "application/json",
            },
        })

            .then(async (response) => {

                const data = await response.json();

                console.log("SERVER RESPONSE:", data);

                if (!response.ok) {

                    throw new Error(
                        data.message ||
                        "Terjadi kesalahan pada server."
                    );

                }

                return data;

            })

            .then((data) => {

                // =============================================
                // API GAGAL / DATA TIDAK DITEMUKAN
                // =============================================
                if (!data.success) {

                    if (sektorDaerah) {

                        sektorDaerah.innerText =
                            data.message ||
                            "Belum ada sektor unggulan";

                    }

                    if (statusDaerah) {

                        statusDaerah.innerText =
                            "Belum ada data";

                    }

                    return;
                }


                // =============================================
                // AMBIL DATA SEKTOR
                // =============================================
                const sektor = data.sektor;


                // =============================================
                // JIKA SEKTOR BERUPA ARRAY
                // =============================================
                if (Array.isArray(sektor) && sektor.length > 0) {

                    sektorDaerah.innerHTML = sektor
                        .map((namaSektor, index) => {

                            return `
                            <div class="sektor-item">

                                <span class="sektor-number">
                                    ${index + 1}.
                                </span>

                                <span class="sektor-name">
                                    ${namaSektor}
                                </span>

                            </div>
                        `;

                        })
                        .join("");

                }


                // =============================================
                // JIKA BACKEND MASIH MENGIRIM STRING
                // =============================================
                else if (
                    typeof sektor === "string" &&
                    sektor.trim() !== ""
                ) {

                    const daftarSektor = sektor
                        .split(/<br\s*\/?>|\n/i)
                        .map(item => item.trim())
                        .filter(item => item !== "");


                    if (daftarSektor.length > 0) {

                        sektorDaerah.innerHTML = daftarSektor
                            .map((namaSektor, index) => {

                                return `
                                <div class="sektor-item">

                                    <span class="sektor-number">
                                        ${index + 1}.
                                    </span>

                                    <span class="sektor-name">
                                        ${namaSektor}
                                    </span>

                                </div>
                            `;

                            })
                            .join("");

                    } else {

                        sektorDaerah.innerText =
                            "Belum ada sektor unggulan";

                    }

                }


                // =============================================
                // TIDAK ADA SEKTOR
                // =============================================
                else {

                    sektorDaerah.innerText =
                        "Belum ada sektor unggulan";

                }


                // =============================================
                // STATUS
                // =============================================
                if (statusDaerah) {

                    let status =
                        data.status ||
                        data.kategori ||
                        "Sektor Cepat Maju dan Cepat Tumbuh";

                    if (data.tahun) {

                        status += `\nTahun ${data.tahun}`;

                    }

                    statusDaerah.innerText = status;

                }

            })

            .catch((error) => {

                console.error(
                    "ERROR MENGAMBIL ANALISIS:",
                    error
                );


                if (sektorDaerah) {

                    sektorDaerah.innerText =
                        "Gagal mengambil data.";

                }


                if (statusDaerah) {

                    statusDaerah.innerText =
                        "Terjadi kesalahan";

                }

            });

    }


    // =====================================================
    // LOOP SEMUA LOKASI
    // =====================================================
    lokasi.forEach((item) => {

        // =============================================
        // VALIDASI KOORDINAT
        // =============================================
        const latitude =
            parseFloat(item.latitude);

        const longitude =
            parseFloat(item.longitude);


        if (
            Number.isNaN(latitude) ||
            Number.isNaN(longitude)
        ) {

            console.warn(
                "Koordinat tidak valid:",
                item
            );

            return;
        }


        // =============================================
        // PILIH ICON BERDASARKAN TYPE (PROVINSI / KABUPATEN)
        // =============================================
        const icon = item.type === "provinsi" ? provinsiIcon : kabupatenIcon;


        // =============================================
        // BUAT MARKER
        // =============================================
        const zIndexOffset = item.type === "provinsi" ? 1000 : 0;
        const marker = L.marker(
            [latitude, longitude],
            {
                icon: icon,
                zIndexOffset: zIndexOffset,
            }
        ).addTo(group);


        // =============================================
        // POPUP
        // =============================================
        const labelTipe = item.type === "provinsi" ? "Provinsi" : "Kabupaten / Kota";
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


        // =============================================
        // KLIK MARKER
        // =============================================
        marker.on("click", () => {

            tampilkanDetail(item);

        });


        // =============================================
        // SIMPAN MARKER UNTUK SEARCH
        // =============================================
        markers.push({

            nama: item.nama.toLowerCase(),

            marker: marker,

            lat: latitude,

            lng: longitude,

            item: item,

        });

    });


    // =====================================================
    // FIT MAP KE SEMUA MARKER
    // =====================================================
    if (group.getLayers().length > 0) {

        map.fitBounds(
            group.getBounds(),
            {
                padding: [50, 50],
            }
        );

    }


    // =====================================================
    // SEARCH KABUPATEN / KOTA
    // =====================================================
    const search =
        document.getElementById("searchKabupaten");


    if (search) {

        search.addEventListener(
            "keyup",
            function () {

                const keyword =
                    this.value
                        .trim()
                        .toLowerCase();


                // Kalau search kosong
                if (keyword === "") {
                    return;
                }


                const ditemukan =
                    markers.find((item) =>
                        item.nama.includes(keyword)
                    );


                if (ditemukan) {

                    // Pindahkan map
                    map.flyTo(
                        [
                            ditemukan.lat,
                            ditemukan.lng
                        ],
                        10,
                        {
                            duration: 1.5,
                        }
                    );


                    // Buka popup
                    ditemukan.marker.openPopup();


                    // Tampilkan detail juga
                    tampilkanDetail(
                        ditemukan.item
                    );

                }

            }
        );

    }


    // =====================================================
    // KLIK MAP
    // =====================================================
    map.on("click", () => {

        map.closePopup();

    });

});