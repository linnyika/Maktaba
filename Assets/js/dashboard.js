document.addEventListener("DOMContentLoaded", () => {
  const scroller = document.getElementById("bookScroller");
  const buttons = document.querySelectorAll(".scroll-btn");
  const scrollAmount = 260;

  // --- Book scroller ---
  buttons.forEach(btn => {
    btn.addEventListener("click", () => {
      const dir = btn.dataset.action === "left" ? -scrollAmount : scrollAmount;
      scroller.scrollBy({ left: dir, behavior: "smooth" });
    });
  });

  // Touch swipe support
  let startX = 0;
  scroller.addEventListener("touchstart", e => (startX = e.touches[0].clientX));
  scroller.addEventListener("touchmove", e => {
    const dx = startX - e.touches[0].clientX;
    if (Math.abs(dx) > 10) scroller.scrollBy({ left: dx, behavior: "auto" });
  });

  // --- Dynamic dashboard stats update ---
  function updateDashboardStats() {
    const statsUrl = '/modules/user/get_dashboard_stats.php';

    fetch(statsUrl)
      .then(response => response.json())
      .then(data => {
        // Total Orders
        const ordersElem = document.querySelector('.btn-outline-primary strong');
        if (ordersElem && data.total_orders !== undefined) {
          ordersElem.textContent = data.total_orders;
        }

        // Total Reviews
        const reviewsElem = document.querySelector('.btn-outline-success strong');
        if (reviewsElem && data.total_reviews !== undefined) {
          reviewsElem.textContent = data.total_reviews;
        }

        // Total Spent
        const spentElem = document.querySelector('.btn-outline-info strong');
        if (spentElem && data.total_spent !== undefined) {
          spentElem.textContent = 'KSh ' + data.total_spent.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
      })
      .catch(err => console.error('Error fetching dashboard stats:', err));
  }

  // Initial fetch
  updateDashboardStats();

  // Auto-refresh every 5 seconds
  setInterval(updateDashboardStats, 5000);
});
