// assets/js/data_filter.js

document.addEventListener("DOMContentLoaded", () => {
  const filterForm = document.querySelector("#filterForm");
  const bookContainer = document.querySelector("#bookContainer");
  const loader = document.querySelector("#loadingSpinner");

  // Function to fetch filtered data
  const fetchBooks = async () => {
    loader.classList.remove("d-none");
    const formData = new FormData(filterForm);
    const queryString = new URLSearchParams(formData).toString();

    try {
      const response = await fetch(`browse_books.php?ajax=1&${queryString}`);
      const html = await response.text();
      bookContainer.innerHTML = html;
    } catch (err) {
      console.error("Error loading books:", err);
      bookContainer.innerHTML = `<div class='alert alert-danger'>Failed to load books.</div>`;
    } finally {
      loader.classList.add("d-none");
    }
  };

  // Trigger on form input changes
  filterForm.querySelectorAll("input, select").forEach((el) => {
    el.addEventListener("change", fetchBooks);
    if (el.tagName === "INPUT") {
      el.addEventListener("keyup", () => {
        clearTimeout(el.timer);
        el.timer = setTimeout(fetchBooks, 500);
      });
    }
  });

  // Initial load
  fetchBooks();
});
