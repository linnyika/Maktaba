// assets/js/report_generator.js

function reloadChart(url, chartInstance) {
    fetch(url)
    .then(response => response.json())
    .then(data => {
        chartInstance.data.labels = data.labels;
        chartInstance.data.datasets[0].data = data.values;
        chartInstance.update();
    })
    .catch(err => console.log("Chart Reload Error:", err));
}
