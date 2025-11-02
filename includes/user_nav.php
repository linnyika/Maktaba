<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="../../index.php">
      <img src="../../assets/img/sm.png" width="36" class="me-2"> Maktaba
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="my_orders.php">My Orders</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart 
            <?php if (!empty($_SESSION['cart'])): ?>
            <span class="badge bg-danger"><?php echo count($_SESSION['cart']); ?></span>
            <?php endif; ?>
        </a></li>
        <li class="nav-item"><a class="nav-link" href="browse_books.php">Browse</a></li>
        <li class="nav-item"><a class="nav-link" href="reservation.php">Reservation</a></li>
        <li class="nav-item"><a class="nav-link" href="reviews.php">Reviews</a></li>
        <li class="nav-item"><a class="nav-link" href="../../modules/auth/logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>