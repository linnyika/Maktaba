<?php if ($result && $result->num_rows > 0): ?>
    <div class="row g-4">
        <?php while ($book = $result->fetch_assoc()): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <?php
                    // Base path for your subdirectory setup (/Maktaba/)
                    $basePath = '/Maktaba';
                    
                    // Determine the image path (absolute, with subdirectory)
                    $imagePath = !empty($book['book_cover']) 
                        ? $basePath . '/Assets/img/' . htmlspecialchars($book['book_cover']) 
                        : $basePath . '/Assets/img/shout.jpg';
                    
                    // Debug: Check if file exists and log issues
                    $fullServerPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
                    if (!file_exists($fullServerPath)) {
                        error_log("Image not found: $fullServerPath (Book Cover: " . ($book['book_cover'] ?? 'N/A') . ")");
                        $imagePath = $basePath . '/Assets/img/shout.jpg';  // Fallback
                    }
                    ?>
                    <img src="<?php echo $imagePath; ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>" 
                         onerror="this.src='<?php echo $basePath; ?>/Assets/img/shout.jpg'; this.alt='Image not available';">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-success"><?php echo htmlspecialchars($book['title']); ?></h5>
                        <p class="card-text mb-1 text-muted">by <?php echo htmlspecialchars($book['author']); ?></p>
                        <p class="card-text small"><?php echo htmlspecialchars($book['publisher_name']); ?></p>
                        <p class="mb-1">
                            <small class="<?php echo $book['stock_quantity'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $book['stock_quantity'] > 0 
                                    ? $book['stock_quantity'] . ' in stock' 
                                    : 'Out of stock'; ?>
                            </small>
                        </p>
                        <p class="mb-2"><strong>KSh <?php echo number_format($book['price'], 2); ?></strong></p>
                        <p class="mb-2">⭐ <?php echo number_format($book['avg_rating'], 1); ?> / 5</p>
                        <div class="mt-auto d-flex justify-content-between">
                            <a href="../user/book_details.php?book_id=<?php echo $book['book_id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <?php if ($book['stock_quantity'] > 0): ?>
                                <a href="../user/add_to_cart.php?book_id=<?php echo $book['book_id']; ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-cart-plus"></i> Add
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center">No books found.</div>
<?php endif; ?>
