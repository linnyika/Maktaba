async function fetchJSON(url) {
  const res = await fetch(url);
  return await res.json();
}

// ===== ADMIN DASHBOARD FUNCTIONS =====
async function loadDashboardStats() {
  const data = await fetchJSON('../../api/data_api.php?type=summary');
  document.getElementById('salesCount').innerText = `KES ${data.total_sales}`;
  document.getElementById('orderCount').innerText = data.total_orders;
  document.getElementById('userCount').innerText = data.total_users;
  document.getElementById('bookCount').innerText = data.total_books;
}

async function loadSalesChart() {
  const data = await fetchJSON('../../api/data_api.php?type=sales_trend');
  new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
      labels: data.labels,
      datasets: [{ label: 'Sales (KES)', data: data.values, borderWidth: 2 }]
    }
  });
}

async function loadOrdersRevenueChart() {
  const data = await fetchJSON('../../api/data_api.php?type=orders_revenue');
  new Chart(document.getElementById('ordersRevenueChart'), {
    type: 'bar',
    data: {
      labels: data.labels,
      datasets: [
        { label: 'Orders', data: data.orders, backgroundColor: 'rgba(54,162,235,0.5)' },
        { label: 'Revenue', data: data.revenue, backgroundColor: 'rgba(75,192,192,0.5)' }
      ]
    }
  });
}

async function loadPopularBooksChart() {
  const data = await fetchJSON('../../api/data_api.php?type=popular_books');
  new Chart(document.getElementById('popularBooksChart'), {
    type: 'doughnut',
    data: {
      labels: data.labels,
      datasets: [{ data: data.values }]
    }
  });
}

// ===== USER ANALYTICS =====
async function loadUserOrdersChart(userId) {
  const data = await fetchJSON(`../../api/data_api.php?type=user_orders&id=${userId}`);
  new Chart(document.getElementById('myOrdersChart'), {
    type: 'line',
    data: {
      labels: data.labels,
      datasets: [{ label: 'Orders', data: data.values, borderWidth: 2 }]
    }
  });
}

async function loadUserSpendingChart(userId) {
  const data = await fetchJSON(`../../api/data_api.php?type=user_spending&id=${userId}`);
  new Chart(document.getElementById('mySpendingChart'), {
    type: 'pie',
    data: {
      labels: data.labels,
      datasets: [{ data: data.values }]
    }
  });
}
// ===== INITIALIZATION =====