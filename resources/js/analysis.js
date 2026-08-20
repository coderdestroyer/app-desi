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
        selected: opt.selected
    }));

    function updateOptions(isInitial = false) {
        const selectedProv = String(provinsiSelect.value || "").trim();
        const currentKabVal = isInitial
            ? (allOptions.find(o => o.selected && o.value)?.value || kabupatenSelect.value)
            : kabupatenSelect.value;

        kabupatenSelect.innerHTML = "";

        const placeholderOpt = allOptions.find(o => !o.value) || { value: "", text: "Pilih Kabupaten / Kota" };
        const defaultOption = document.createElement("option");
        defaultOption.value = placeholderOpt.value;
        defaultOption.textContent = placeholderOpt.text;
        kabupatenSelect.appendChild(defaultOption);

        let matchFound = false;

        allOptions.forEach(opt => {
            if (!opt.value) return;

            if (!selectedProv || String(opt.provinsi) === selectedProv) {
                const el = document.createElement("option");
                el.value = opt.value;
                el.textContent = opt.text;
                el.setAttribute("data-provinsi", opt.provinsi);

                if (String(opt.value) === String(currentKabVal)) {
                    el.selected = true;
                    matchFound = true;
                }

                kabupatenSelect.appendChild(el);
            }
        });

        if (!matchFound) {
            kabupatenSelect.value = "";
        }
    }

    provinsiSelect.addEventListener("change", () => updateOptions(false));
    updateOptions(true);
}