// dashboard.js
document.addEventListener("DOMContentLoaded", () => {
  const scroller = document.getElementById("bookScroller");
  const buttons = document.querySelectorAll(".scroll-btn");
  const scrollAmount = 260;

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
});
