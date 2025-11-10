CREATE OR REPLACE VIEW book_performance_view AS
SELECT 
    b.book_id,
    b.title,
    b.genre,
    COALESCE(AVG(r.rating), 0) AS avg_rating,
    COALESCE(SUM(oi.quantity), 0) AS total_sold,
    COALESCE(SUM(oi.quantity * oi.price), 0) AS total_revenue
FROM books b
LEFT JOIN reviews r ON b.book_id = r.book_id
LEFT JOIN order_items oi ON b.book_id = oi.book_id
GROUP BY b.book_id, b.title, b.genre;
