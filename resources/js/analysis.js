import Chart from "chart.js/auto";

/* ==========================================================
   CENTER TEXT PLUGIN
========================================================== */

const centerTextPlugin = {

    id: "centerText",

    beforeDraw(chart) {

        // Hanya untuk doughnut
        if (chart.config.type !== "doughnut") {
            return;
        }

        const options = chart.options.plugins?.centerText;

        if (!options || options.total == null) {
            return;
        }

        const {
            ctx,
            chartArea: { left, right, top, bottom }
        } = chart;

        const x = (left + right) / 2;
        const y = (top + bottom) / 2;

        ctx.save();

        ctx.textAlign = "center";
        ctx.textBaseline = "middle";

        ctx.fillStyle = "#243247";
        ctx.font = "700 32px Poppins";
        ctx.fillText(options.total, x, y - 10);

        ctx.fillStyle = "#667085";
        ctx.font = "500 16px Poppins";
        ctx.fillText("Sektor", x, y + 18);

        ctx.restore();

    }

};

Chart.register(centerTextPlugin);

Chart.register(centerTextPlugin);

document.addEventListener("DOMContentLoaded", () => {

    const charts = window.dashboardCharts ?? {};
    console.log("Dashboard Charts:", charts);

    /*
    ==========================================================
    GENERIC CHART RENDERER
    ==========================================================
    */

    function renderChart(canvasId, chart) {

        const canvas = document.getElementById(canvasId);

        if (!canvas || !chart) {
            return;
        }

        console.log(canvasId, chart);

        const config = {

            type: chart.type ?? "bar",

            data: {

                labels:
                    chart.labels ??
                    chart.categories ??
                    [],

                datasets:
                    chart.datasets ??
                    [{
                        label:
                            chart.label ??
                            "Nilai",

                        data:
                            chart.series ??
                            [],

                        borderWidth:2,

                        borderColor:"#ffffff",

                        hoverOffset:12
                    }]

            },

            options: {

                indexAxis: chart.indexAxis ?? "x",

                responsive: true,

                maintainAspectRatio: false,

                layout: {

                    padding: {
                        left: 20,
                        right: 20,
                        top: 20,
                        bottom: 10
                    }

                },

                cutout:

                    chart.type === "doughnut"

                        ? "58%"

                        : undefined,

                plugins:{


                    legend: {

                        display:false,

                        position: "bottom",

                        align: "center",

                        labels: {

                            usePointStyle: true,

                            pointStyle: "circle",

                            padding: 20

                        }

                    },

                    title:{
                        display:true
                    }

                }

            }

        };

        if (config.type === "doughnut") {

            config.data.datasets[0].cutout =
                chart.datasets?.[0]?.cutout ?? '62%';

            config.data.datasets[0].radius =
                chart.datasets?.[0]?.radius ?? '82%';

            config.options.plugins.centerText = {
                total: chart.total
            };
            

        }

        if (config.type === "bar") {

            config.options.scales = {

                y: {

                    beginAtZero: true

                }

            };

        }

        if (config.type === "scatter") {

            config.options.scales = {

                x: {

                    title: {

                        display: true,

                        text: chart.xLabel ?? ""

                    }

                },

                y: {

                    title: {

                        display: true,

                        text: chart.yLabel ?? ""

                    }

                }

            };

        }

        // Hindari error Canvas is already in use
        if (canvas.chartInstance) {
            canvas.chartInstance.destroy();
        }

        canvas.chartInstance = new Chart(canvas, config);

    }

    /*
    ==========================================================
    FILTER ANIMATION
    ==========================================================
    */

    document
        .querySelectorAll(".filter-box select")
        .forEach(select => {

            select.addEventListener("change", () => {

                select.classList.add("active-filter");

                setTimeout(() => {

                    select.classList.remove("active-filter");

                }, 300);

            });

        });

    /*
    ==========================================================
    CARD ANIMATION
    ==========================================================
    */

    const observer = new IntersectionObserver(

        entries => {

            entries.forEach(entry => {

                if (entry.isIntersecting) {

                    entry.target.classList.add("show");

                }

            });

        },

        {
            threshold: 0.2
        }

    );

    document
        .querySelectorAll(".summary-card")
        .forEach(card => observer.observe(card));

    
    /*
    ==========================================================
    RENDER CHARTS
    ==========================================================
    */

    renderChart(
        "doughnutChart",
        charts.doughnut
    );

    renderChart(
        "barChart",
        charts.bar
    );

    renderChart(
        "scatterChart",
        charts.scatter
    );

    /*
    ==========================================================
    TAB
    ==========================================================
    */

    document
        .querySelectorAll(".tab-button")
        .forEach(button => {

            button.addEventListener("click", () => {

                document
                    .querySelectorAll(".tab-button")
                    .forEach(btn => btn.classList.remove("active"));

                document
                    .querySelectorAll(".tab-content")
                    .forEach(tab => tab.classList.remove("active"));

                button.classList.add("active");

                document
                    .getElementById(button.dataset.tab)
                    ?.classList.add("active");

            });

        });

    /*
    ==========================================================
    EXPORT EXCEL & JSON HANDLERS
    ==========================================================
    */
    const btnExcel = document.getElementById("btnExportExcel");
    const btnJson = document.getElementById("btnExportJson");

    if (btnExcel) {
        btnExcel.addEventListener("click", () => {
            const tableData = window.dashboardTable ?? [];
            const info = window.dashboardInfo ?? {};
            handleExportExcel(tableData, info);
        });
    }

    if (btnJson) {
        btnJson.addEventListener("click", () => {
            const tableData = window.dashboardTable ?? [];
            const info = window.dashboardInfo ?? {};
            handleExportJson(tableData, info);
        });
    }

    /*
    ==========================================================
    FILTER KABUPATEN BERDASARKAN PROVINSI
    ==========================================================
    */
    const analysisForm = document.querySelector(".filter-box");
    if (analysisForm) {
        const provSelect = analysisForm.querySelector('select[name="provinsi"]');
        const kabSelect = analysisForm.querySelector('select[name="kabupaten"]');
        if (provSelect && kabSelect) {
            setupDynamicKabupatenDropdown(provSelect, kabSelect);
        }
    }

});

function getSanitizedFilename(info, extension) {
    const metode = (info.metode || 'sektoral').toLowerCase();
    const wilayah = (info.wilayah || 'wilayah').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
    const tahun = info.tahun || '2025';
    return `analisis_${metode}_${wilayah}_${tahun}.${extension}`;
}

function handleExportExcel(tableData, info) {
    if (!tableData || !tableData.length) {
        alert("Tidak ada data tabel untuk diexport.");
        return;
    }

    const filename = getSanitizedFilename(info, 'xlsx');

    const formattedData = tableData.map(row => {
        const newRow = {};
        Object.keys(row).forEach(key => {
            const header = key.replace(/_/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase())
                .replace(/Ssa/g, 'SSA')
                .replace(/Lq/g, 'LQ');
            newRow[header] = row[key];
        });
        return newRow;
    });

    if (window.XLSX) {
        const worksheet = window.XLSX.utils.json_to_sheet(formattedData);
        const workbook = window.XLSX.utils.book_new();
        window.XLSX.utils.book_append_sheet(workbook, worksheet, "Hasil Analisis");

        const keys = Object.keys(formattedData[0] || {});
        worksheet['!cols'] = keys.map(k => ({ wch: Math.max(k.length + 5, 18) }));

        window.XLSX.writeFile(workbook, filename);
    } else {
        const keys = Object.keys(formattedData[0]);
        let csvContent = "\uFEFF";
        csvContent += keys.map(h => `"${h.replace(/"/g, '""')}"`).join(";") + "\r\n";

        formattedData.forEach(row => {
            const values = keys.map(k => {
                let val = row[k] ?? "";
                if (typeof val === "number") {
                    val = val.toString().replace('.', ',');
                }
                return `"${String(val).replace(/"/g, '""')}"`;
            });
            csvContent += values.join(";") + "\r\n";
        });

        const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.setAttribute("download", filename.replace('.xlsx', '.csv'));
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function handleExportJson(tableData, info) {
    if (!tableData || !tableData.length) {
        alert("Tidak ada data tabel untuk diexport.");
        return;
    }

    const filename = getSanitizedFilename(info, 'json');

    const simplifiedData = tableData.map(row => {
        const sektorName = row.nama_sektor || row.sektor || "";
        let val = row.nilai_lq ?? row.nilai_ssa ?? row.dij ?? row.cij ?? row.pertumbuhan_kabupaten ?? row.laju_pertumbuhan ?? 0;
        if (typeof val === 'number') {
            val = Number(val.toFixed(4));
        }

        return {
            sektor: sektorName,
            nilai: val
        };
    });

    const jsonStr = simplifiedData
        .map(item => JSON.stringify(item, null, 2))
        .join(",\n");

    const blob = new Blob([jsonStr], { type: "application/json;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/*
==========================================================
DYNAMIC PROVINSI - KABUPATEN DROPDOWN HELPER
==========================================================
*/
export function setupDynamicKabupatenDropdown(provinsiSelect, kabupatenSelect) {
    if (!provinsiSelect || !kabupatenSelect || provinsiSelect.dataset.dynamicLinked === "true") {
        return;
    }
    provinsiSelect.dataset.dynamicLinked = "true";

    const allOptions = Array.from(kabupatenSelect.querySelectorAll("option")).map(opt => ({
        value: opt.value,
        text: opt.textContent.trim(),
        provinsi: opt.getAttribute("data-provinsi") || "",
        selected: opt.selected,
        group: opt.parentElement?.tagName === "OPTGROUP" ? opt.parentElement.label : null
    }));

    function updateOptions(isInitial = false) {
        const selectedProv = String(provinsiSelect.value || "").trim();
        const currentKabVal = isInitial
            ? (allOptions.find(o => o.selected && o.value)?.value || kabupatenSelect.value)
            : kabupatenSelect.value;

        kabupatenSelect.innerHTML = "";

        const placeholderOpt = allOptions.find(o => !o.value) || { value: "", text: "Pilih Kabupaten / Kota / Provinsi" };
        const defaultOption = document.createElement("option");
        defaultOption.value = placeholderOpt.value;
        defaultOption.textContent = placeholderOpt.text;
        kabupatenSelect.appendChild(defaultOption);

        let matchFound = false;
        const groupsMap = new Map();

        allOptions.forEach(opt => {
            if (!opt.value) return;

            if (!selectedProv || String(opt.provinsi) === selectedProv) {
                const el = document.createElement("option");
                el.value = opt.value;
                el.textContent = opt.text;
                if (opt.provinsi) {
                    el.setAttribute("data-provinsi", opt.provinsi);
                }

                if (String(opt.value) === String(currentKabVal)) {
                    el.selected = true;
                    matchFound = true;
                }

                if (opt.group) {
                    if (!groupsMap.has(opt.group)) {
                        const optgroup = document.createElement("optgroup");
                        optgroup.label = opt.group;
                        groupsMap.set(opt.group, optgroup);
                        kabupatenSelect.appendChild(optgroup);
                    }
                    groupsMap.get(opt.group).appendChild(el);
                } else {
                    kabupatenSelect.appendChild(el);
                }
            }
        });

        if (!matchFound) {
            kabupatenSelect.value = "";
        }
    }

    provinsiSelect.addEventListener("change", () => updateOptions(false));
    updateOptions(true);
}