<?php
session_start();
require_once("../../database/config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle review submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $book_id = intval($_POST['book_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if($book_id && $rating && !empty($comment)){
        // Prevent duplicate reviews
        $check = $conn->prepare("SELECT review_id FROM reviews WHERE user_id=? AND book_id=?");
        $check->bind_param("ii",$user_id,$book_id);
        $check->execute();
        $res = $check->get_result();

        if($res->num_rows>0){
            $message = "<div class='alert alert-warning'>You already reviewed this book.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO reviews (user_id, book_id, rating, comment, is_approved) VALUES (?,?,?,?,0)");
            $stmt->bind_param("iiis",$user_id,$book_id,$rating,$comment);
            if($stmt->execute()){
                $message = "<div class='alert alert-success'>Review submitted successfully (pending approval).</div>";
            } else {
                $message = "<div class='alert alert-danger'>Failed to submit review.</div>";
            }
            $stmt->close();
        }
        $check->close();
    } else {
        $message = "<div class='alert alert-warning'>Fill all fields.</div>";
    }
}

// Fetch books for dropdown
$books = $conn->query("SELECT book_id, title FROM books ORDER BY title ASC");

// Fetch approved reviews with average rating
$reviews = $conn->query("
    SELECT r.review_id, b.title, r.rating, r.comment, r.review_date, u.full_name
    FROM reviews r
    JOIN books b ON r.book_id=b.book_id
    JOIN users u ON r.user_id=u.user_id
    WHERE r.is_approved=1
    ORDER BY r.review_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maktaba | Book Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include("../../includes/user_nav.php"); ?>

<div class="container mt-5">
    <h2 class="text-center text-primary mb-4">Book Reviews</h2>
    <?php echo $message; ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title text-success">Leave a Review</h5>
            <form method="POST">
                <div class="mb-3">
                    <label>Select a Book</label>
                    <select name="book_id" class="form-select" required>
                        <option value="">-- Choose Book --</option>
                        <?php while($book=$books->fetch_assoc()): ?>
                            <option value="<?php echo $book['book_id']; ?>"><?php echo htmlspecialchars($book['title']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Rating (1-5)</label>
                    <select name="rating" class="form-select" required>
                        <option value="">-- Select Rating --</option>
                        <option value="1">1 - Poor</option>
                        <option value="2">2 - Fair</option>
                        <option value="3">3 - Good</option>
                        <option value="4">4 - Very Good</option>
                        <option value="5">5 - Excellent</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Your Comment</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Write your thoughts..." required></textarea>
                </div>
                <button class="btn btn-success w-100">Submit Review</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title text-primary">Approved Reviews</h5>
            <?php if($reviews->num_rows>0): ?>
                <?php while($rev=$reviews->fetch_assoc()): ?>
                    <div class="border-bottom pb-3 mb-3">
                        <h6><?php echo htmlspecialchars($rev['title']); ?></h6>
                        <small class="text-muted">
                            by <?php echo htmlspecialchars($rev['full_name']); ?> |
                            Rated: <?php echo $rev['rating']; ?>/5 |
                            <?php echo date("M d, Y", strtotime($rev['review_date'])); ?>
                        </small>
                        <p><?php echo htmlspecialchars($rev['comment']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No reviews yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
