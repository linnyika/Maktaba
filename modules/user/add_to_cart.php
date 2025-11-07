<?php
require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../database/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$book_id = intval($_GET['book_id'] ?? 0);

if ($book_id <= 0) {
    header("Location: browse_books.php");
    exit;
}

// Fetch the book from DB
$stmt = $conn->prepare("SELECT book_id, title, author, price, stock_quantity, book_cover FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($book = $result->fetch_assoc()) {
    // Initialize cart array if it doesn’t exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // If book already in cart, just increase quantity
    if (isset($_SESSION['cart'][$book_id])) {
        $_SESSION['cart'][$book_id]['quantity']++;
    } else {
        $_SESSION['cart'][$book_id] = [
            'title' => $book['title'],
            'author' => $book['author'],
            'price' => $book['price'],
            'quantity' => 1,
            'stock' => $book['stock_quantity'],
            'image' => !empty($book['book_cover']) 
                ? "../../assets/img/" . $book['book_cover'] 
                : "../../assets/img/shout.jpg"
        ];
    }

    // Redirect to cart
    header("Location: cart.php");
    exit;
} else {
    echo "<script>alert('Book not found'); window.location.href='browse_books.php';</script>";
}
