<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'learner') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$learner_id = $_SESSION['user_id'];
$filter = isset($_GET['filter']) ? htmlspecialchars($_GET['filter']) : 'all';

// Build query dengan JOIN untuk mengambil data lengkap
$query = "SELECT 
    b.id,
    b.booking_date,
    b.booking_time,
    b.status,
    b.notes,
    b.created_at,
    u.name as tutor_name,
    u.email as tutor_email,
    s.subject_name,
    s.price,
    r.id as review_id,
    r.rating,
    r.review_text
FROM bookings b
INNER JOIN users u ON b.tutor_id = u.id
INNER JOIN subjects s ON b.subject_id = s.id
LEFT JOIN reviews r ON b.id = r.booking_id
WHERE b.learner_id = '$learner_id'";

// Filter berdasarkan status
if ($filter != 'all') {
    $filter_escaped = mysqli_real_escape_string($conn, $filter);
    $query .= " AND b.status = '$filter_escaped'";
}

$query .= " ORDER BY b.booking_date DESC, b.booking_time DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

$bookings = [];
while ($row = mysqli_fetch_assoc($result)) {
    $bookings[] = [
        'id' => $row['id'],
        'booking_date' => $row['booking_date'],
        'booking_time' => $row['booking_time'],
        'status' => $row['status'],
        'notes' => $row['notes'],
        'created_at' => $row['created_at'],
        'tutor_name' => $row['tutor_name'],
        'tutor_email' => $row['tutor_email'],
        'subject_name' => $row['subject_name'],
        'price' => $row['price'],
        'has_review' => !empty($row['review_id']),
        'rating' => $row['rating'],
        'review_text' => $row['review_text']
    ];
}

echo json_encode(['success' => true, 'bookings' => $bookings]);
?>

