//MANAGE BOOKS ALSO MANAGES INVENTORY
<?php
require_once("../../database/config.php");
require_once("../../includes/admin_check.php");

$message = "";

// Delete book
if (isset($_GET['delete'])) {
    $book_id = intval($_GET['delete']);
    $conn->query("DELETE FROM books WHERE book_id = $book_id");
    $message = "Book deleted successfully.";
}

// Add or Edit book
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $book_id = $_POST['book_id'] ?? null;
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $genre = trim($_POST['genre']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);

    if ($book_id) {
        // Update
        $stmt = $conn->prepare("UPDATE books SET title=?, author=?, genre=?, price=?, stock=? WHERE book_id=?");
        $stmt->bind_param("sssddi", $title, $author, $genre, $price, $stock, $book_id);
        $message = $stmt->execute() ? "Book updated successfully." : "Failed to update book.";
    } else {
        // Add new
        $stmt = $conn->prepare("INSERT INTO books (title, author, genre, price, stock, date_added) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssdd", $title, $author, $genre, $price, $stock);
        $message = $stmt->execute() ? "Book added successfully." : "Failed to add book.";
    }
}

// Fetch books
$books = $conn->query("SELECT * FROM books ORDER BY date_added DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Inventory | Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include("../../includes/admin_nav.php"); ?>
<div class="container mt-5">
  <div class="card shadow p-4">
    <h3 class="text-success mb-3">Manage Inventory</h3>

    <?php if ($message): ?>
      <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <form method="POST" class="row g-3 mb-4">
      <input type="hidden" name="book_id" id="book_id">
      <div class="col-md-4">
        <input type="text" name="title" id="title" class="form-control" placeholder="Book Title" required>
      </div>
      <div class="col-md-3">
        <input type="text" name="author" id="author" class="form-control" placeholder="Author" required>
      </div>
      <div class="col-md-2">
        <input type="text" name="genre" id="genre" class="form-control" placeholder="Genre">
      </div>
      <div class="col-md-1">
        <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="Price" required>
      </div>
      <div class="col-md-1">
        <input type="number" name="stock" id="stock" class="form-control" placeholder="Stock" required>
      </div>
      <div class="col-md-1 d-grid">
        <button type="submit" class="btn btn-success">Save</button>
      </div>
    </form>

    <!-- Inventory Table -->
    <table class="table table-hover">
      <thead class="table-success">
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Author</th>
          <th>Genre</th>
          <th>Price (KSh)</th>
          <th>Stock</th>
          <th>Date Added</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($book = $books->fetch_assoc()): ?>
          <tr>
            <td><?= $book['book_id'] ?></td>
            <td><?= htmlspecialchars($book['title']) ?></td>
            <td><?= htmlspecialchars($book['author']) ?></td>
            <td><?= htmlspecialchars($book['genre']) ?></td>
            <td><?= number_format($book['price'], 2) ?></td>
            <td><?= isset($book['stock']) ? htmlspecialchars($book['stock']) : '0' ?></td>
            <td><?= $book['date_added'] ?></td>
            <td>
              <button class="btn btn-sm btn-warning" 
                onclick="editBook(<?= htmlspecialchars(json_encode($book)) ?>)">Edit</button>
              <a href="?delete=<?= $book['book_id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Delete this book?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function editBook(book) {
  document.getElementById('book_id').value = book.book_id;
  document.getElementById('title').value = book.title;
  document.getElementById('author').value = book.author;
  document.getElementById('genre').value = book.genre;
  document.getElementById('price').value = book.price;
  document.getElementById('stock').value = book.stock;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
</body>
</html>
