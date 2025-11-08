// chart_config.js – Maktaba Analytics (Member 5)
// Handles real-time data updates for analytics_dashboard.php

// Load Google Charts
google.charts.load('current', { packages: ['corechart'] });
google.charts.setOnLoadCallback(initCharts);

function initCharts() {
  fetchDashboardData();
}

async function fetchDashboardData() {
  try {
    const response = await fetch('../../api/data_api.php?action=getAnalytics');
    const data = await response.json();

    renderSalesTrend(data.sales);
    renderOrdersPie(data.orders);
    renderBookPerformance(data.books);
    renderUserActivity(data.users);
    renderRevenueBar(data.revenue);

  } catch (err) {
    console.error("Error loading analytics:", err);
  }
}

/* ----------------- CHART.JS SECTION ------------------ */

// Sales Trend (Chart.js Line)
function renderSalesTrend(salesData) {
  const ctx = document.getElementById('salesTrendChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: salesData.labels,
      datasets: [{
        label: 'Sales (KES)',
        data: salesData.values,
        borderColor: '#007bff',
        backgroundColor: 'rgba(0,123,255,0.2)',
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      scales: { y: { beginAtZero: true } }
    }
  });
}

// Book Performance (Chart.js Bar)
function renderBookPerformance(bookData) {
  const ctx = document.getElementById('bookPerformanceChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: bookData.labels,
      datasets: [{
        label: 'Books Sold',
        data: bookData.values,
        backgroundColor: '#28a745'
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      scales: { x: { beginAtZero: true } }
    }
  });
}

// Revenue (Chart.js Bar)
function renderRevenueBar(revData) {
  const ctx = document.getElementById('revenueBarChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: revData.labels,
      datasets: [{
        label: 'Revenue (KES)',
        data: revData.values,
        backgroundColor: '#ffc107'
      }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
  });
}

/* ----------------- GOOGLE CHARTS SECTION ------------------ */

// Order Status Pie
function renderOrdersPie(orderData) {
  const data = google.visualization.arrayToDataTable([
    ['Status', 'Count'],
    ...orderData
  ]);
  const options = {
    title: 'Order Status Overview',
    pieHole: 0.4,
    colors: ['#007bff', '#28a745', '#ffc107', '#dc3545']
  };
  const chart = new google.visualization.PieChart(document.getElementById('orderStatusPieChart'));
  chart.draw(data, options);
}

// User Activity Column Chart
function renderUserActivity(userData) {
  const data = google.visualization.arrayToDataTable([
    ['Month', 'Active Users'],
    ...userData
  ]);
  const options = {
    title: 'Monthly Active Users',
    colors: ['#17a2b8'],
    hAxis: { title: 'Month' },
    vAxis: { title: 'Users', minValue: 0 }
  };
  const chart = new google.visualization.ColumnChart(document.getElementById('userActivityChart'));
  chart.draw(data, options);
}
