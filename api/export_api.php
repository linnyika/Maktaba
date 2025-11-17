// Export Reservations Log
if ($type === "reservations") {

    $query = "
        SELECT 
            r.reservation_id,
            c.full_name,
            b.title,
            r.pickup_date,
            r.status,
            r.payment_status,
            r.reservation_date
        FROM reservations r
        JOIN customers c ON r.user_id = c.customer_id
        JOIN books b ON r.book_id = b.book_id
        ORDER BY r.reservation_date DESC
    ";

    $result = $conn->query($query);

    $header = [
        "Reservation ID",
        "User",
        "Book Title",
        "Pickup Date",
        "Status",
        "Payment Status",
        "Reservation Date"
    ];

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['reservation_id'],
            $row['full_name'],
            $row['title'],
            $row['pickup_date'],
            $row['status'],
            $row['payment_status'] ?: 'Pending',
            $row['reservation_date']
        ];
    }

    exportToCSV("reservation_logs.csv", $header, $data);
}