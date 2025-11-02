<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/admin_check.php';
require_once __DIR__ . '/../../database/config.php';

// Handle Form Submissions
$message = '';
$message_type = '';

// Get publishers for dropdown
$publishers_result = $conn->query("SELECT publisher_id, name FROM publishers ORDER BY name");
$publishers = [];
while ($row = $publishers_result->fetch_assoc()) {
    $publishers[] = $row;
}

// ADD NEW BOOK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $publisher_id = !empty($_POST['publisher_id']) ? (int)$_POST['publisher_id'] : NULL;
    $price = (float)$_POST['price'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO books (title, author, publisher_id, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssidi", $title, $author, $publisher_id, $price, $stock_quantity);
        
        if ($stmt->execute()) {
            $message = "Book added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding book: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// EDIT BOOK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_book'])) {
    $book_id = (int)$_POST['book_id'];
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $publisher_id = !empty($_POST['publisher_id']) ? (int)$_POST['publisher_id'] : NULL;
    $price = (float)$_POST['price'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    
    try {
        $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, publisher_id = ?, price = ?, stock_quantity = ? WHERE book_id = ?");
        $stmt->bind_param("ssidii", $title, $author, $publisher_id, $price, $stock_quantity, $book_id);
        
        if ($stmt->execute()) {
            $message = "Book updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating book: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// DELETE BOOK
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $message = "Book deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting book: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// TOGGLE BOOK AVAILABILITY
if (isset($_GET['toggle_id'])) {
    $toggle_id = (int)$_GET['toggle_id'];
    
    try {
        $stmt = $conn->prepare("UPDATE books SET is_available = NOT is_available WHERE book_id = ?");
        $stmt->bind_param("i", $toggle_id);
        
        if ($stmt->execute()) {
            $message = "Book status updated!";
            $message_type = "success";
        }
        $stmt->close();
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Maktaba Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include("../../includes/admin_nav.php"); ?>

<!-- Book Management Content -->
<main class="container my-5 flex-grow-1">
    <!-- Message Alert -->
    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary">Manage Books</h3>
        <div>
            <a href="manage_publishers.php" class="btn btn-outline-primary me-2">
                <i class="bi bi-building"></i> Manage Publishers
            </a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBookModal">
                <i class="bi bi-plus-circle"></i> Add New Book
            </button>
        </div>
    </div>

    <!-- Books Table -->
    <div class="table-wrapper">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Publisher</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("
                    SELECT b.*, p.name as publisher_name 
                    FROM books b 
                    LEFT JOIN publishers p ON b.publisher_id = p.publisher_id 
                    ORDER BY b.title
                ");
                
                if ($result && $result->num_rows > 0) {
                    while ($book = $result->fetch_assoc()) {
                        $book_id = $book['book_id'] ?? $book['id'] ?? 0;
                        $title = htmlspecialchars($book['title'] ?? 'Unknown Title');
                        $author = htmlspecialchars($book['author'] ?? 'Unknown Author');
                        $publisher_name = htmlspecialchars($book['publisher_name'] ?? 'Not Set');
                        $price = number_format($book['price'] ?? 0, 2);
                        $stock = $book['stock_quantity'] ?? 0;
                        $status = $book['is_available'] ?? 1;
                        
                        echo '
                        <tr>
                            <td>'.$book_id.'</td>
                            <td>'.$title.'</td>
                            <td>'.$author.'</td>
                            <td>'.$publisher_name.'</td>
                            <td>KSh '.$price.'</td>
                            <td>'.$stock.'</td>
                            <td>
                                <span class="badge '.($status ? 'bg-success' : 'bg-secondary').'">
                                    '.($status ? 'Available' : 'Unavailable').'
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="?toggle_id='.$book_id.'" class="btn btn-outline-'.($status ? 'warning' : 'success').'" title="'.($status ? 'Disable' : 'Enable').'">
                                        <i class="bi bi-'.($status ? 'pause' : 'play').'"></i>
                                    </a>
                                    <button class="btn btn-outline-primary edit-book-btn" 
                                            data-book-id="'.$book_id.'"
                                            data-book-title="'.htmlspecialchars($book['title']).'"
                                            data-book-author="'.htmlspecialchars($book['author']).'"
                                            data-book-publisher="'.($book['publisher_id'] ?? '').'"
                                            data-book-price="'.$book['price'].'"
                                            data-book-stock="'.$book['stock_quantity'].'">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?delete_id='.$book_id.'" class="btn btn-outline-danger" onclick="return confirm(\'Are you sure you want to delete this book?\')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>';
                    }
                } else {
                    echo '<tr><td colspan="8" class="text-center text-muted">No books found in database.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="add_book" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter book title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Author *</label>
                        <input type="text" name="author" class="form-control" placeholder="Enter author name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Publisher</label>
                        <select name="publisher_id" class="form-select">
                            <option value="">Select Publisher</option>
                            <?php foreach ($publishers as $publisher): ?>
                            <option value="<?php echo $publisher['publisher_id']; ?>">
                                <?php echo htmlspecialchars($publisher['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            <a href="manage_publishers.php" class="text-decoration-none">
                                <i class="bi bi-plus-circle"></i> Add new publisher
                            </a>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price (KSh) *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock Quantity *</label>
                            <input type="number" name="stock_quantity" class="form-control" min="0" placeholder="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Book Modal -->
<div class="modal fade" id="editBookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="edit_book" value="1">
                    <input type="hidden" name="book_id" id="edit_book_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Author *</label>
                        <input type="text" name="author" id="edit_author" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Publisher</label>
                        <select name="publisher_id" id="edit_publisher" class="form-select">
                            <option value="">Select Publisher</option>
                            <?php foreach ($publishers as $publisher): ?>
                            <option value="<?php echo $publisher['publisher_id']; ?>">
                                <?php echo htmlspecialchars($publisher['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            <a href="manage_publishers.php" class="text-decoration-none">
                                <i class="bi bi-plus-circle"></i> Add new publisher
                            </a>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price (KSh) *</label>
                            <input type="number" name="price" id="edit_price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock Quantity *</label>
                            <input type="number" name="stock_quantity" id="edit_stock" class="form-control" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="bg-primary text-white text-center py-3 mt-auto">
    <small>&copy; <?php echo date('Y'); ?> Maktaba Bookstore | Admin Panel</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Edit Book Functionality
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-book-btn');
    const editModal = new bootstrap.Modal(document.getElementById('editBookModal'));
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Get book data from button attributes
            const bookId = this.getAttribute('data-book-id');
            const bookTitle = this.getAttribute('data-book-title');
            const bookAuthor = this.getAttribute('data-book-author');
            const bookPublisher = this.getAttribute('data-book-publisher');
            const bookPrice = this.getAttribute('data-book-price');
            const bookStock = this.getAttribute('data-book-stock');
            
            // Fill the edit form
            document.getElementById('edit_book_id').value = bookId;
            document.getElementById('edit_title').value = bookTitle;
            document.getElementById('edit_author').value = bookAuthor;
            document.getElementById('edit_publisher').value = bookPublisher;
            document.getElementById('edit_price').value = bookPrice;
            document.getElementById('edit_stock').value = bookStock;
            
            // Show the modal
            editModal.show();
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const title = form.querySelector('input[name="title"]')?.value.trim();
            const author = form.querySelector('input[name="author"]')?.value.trim();
            const price = form.querySelector('input[name="price"]')?.value;
            const stock = form.querySelector('input[name="stock_quantity"]')?.value;
            
            if (!title || !author || !price || !stock) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            if (price < 0 || stock < 0) {
                e.preventDefault();
                alert('Price and stock cannot be negative.');
                return false;
            }
        });
    });
});
</script>
</body>
</html>