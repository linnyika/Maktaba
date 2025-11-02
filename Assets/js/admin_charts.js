document.addEventListener("DOMContentLoaded", () => {
    const data = window.reportData || {};

    const mintyColors = ['#78C2AD', '#6CC3D5', '#F3969A', '#FFCE67', '#A597E7'];

    const ordersCtx = document.getElementById("ordersStatusChart");
    const paymentsCtx = document.getElementById("paymentsStatusChart");

    if (ordersCtx && data.order_status_summary) {
        new Chart(ordersCtx, {
            type: "doughnut",
            data: {
                labels: Object.keys(data.order_status_summary),
                datasets: [{
                    data: Object.values(data.order_status_summary),
                    backgroundColor: mintyColors,
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'Orders by Status', color: '#333' }
                }
            }
        });
    }

    if (paymentsCtx && data.payment_status_summary) {
        new Chart(paymentsCtx, {
            type: "doughnut",
            data: {
                labels: Object.keys(data.payment_status_summary),
                datasets: [{
                    data: Object.values(data.payment_status_summary),
                    backgroundColor: mintyColors.slice(0, 4),
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'Payments by Status', color: '#333' }
                }
            }
        });
    }
});
