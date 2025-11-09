<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/index.php">
      <img src="../../assets/img/sm.png" width="36" class="me-2"> Maktaba Admin
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navAdmin">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_users.php">Users</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_books.php">Books</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_orders.php">Orders</a></li>
        <li class="nav-item"><a class="nav-link" href="system_reports.php">Reports</a></li>
        <li class="nav-item"><a class="nav-link" href="payment_logs.php">Logs</a></li>
        <li class="nav-item">
 <li class="nav-item">
  <a class="nav-link" href="book_performance.php">Book Performance</a>
</li>


        <li class="nav-item"><a class="nav-link" href="moodle_sync.php">Moodle Sync</a></li>
        <li class="nav-item"><a class="nav-link" href="manage_reviews.php">Reviews</a></li>
        <li class="nav-item"><a class="nav-link" href="../../modules/auth/logout.php">Logout</a></li>
        <li class="nav-item"><a class="nav-link" href="user_activity_report.php">User Activity Report</a></li>
        <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
        <i class="bi bi-bell"></i>
        <span id="notif-count" class="badge bg-danger rounded-pill">0</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" id="notif-list">
        <li><span class="dropdown-item-text">Loading...</span></li>
    </ul>
</li>

<script>
function loadNotifications() {
    fetch('/modules/admin/notifications.php')
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('notif-list');
            const count = document.getElementById('notif-count');
            list.innerHTML = '';
            if(data.length === 0){
                list.innerHTML = '<li><span class="dropdown-item-text">No notifications</span></li>';
                count.textContent = '0';
            } else {
                count.textContent = data.length;
                data.forEach(n => {
                    const li = document.createElement('li');
                    li.innerHTML = '<a class="dropdown-item" href="#">'+n.message+'<br><small class="text-muted">'+n.created_at+'</small></a>';
                    list.appendChild(li);
                });
            }
        });
}

// Refresh every 30s
loadNotifications();
setInterval(loadNotifications, 30000);
</script>


      </ul>
    </div>
  </div>
</nav>