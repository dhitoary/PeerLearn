<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'learner') {
    header("Location: ../../frontend/pages/auth/login.php?error=unauthorized");
    exit();
}

$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

// Get siswa_id from email
$siswa_query = "SELECT id FROM siswa WHERE email = '$user_email' LIMIT 1";
$siswa_result = mysqli_query($conn, $siswa_query);
$siswa_data = mysqli_fetch_assoc($siswa_result);

if (!$siswa_data) {
    header("Location: ../../frontend/pages/auth/login.php");
    exit();
}

$learner_id = $siswa_data['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $review_text = htmlspecialchars(trim($_POST['review_text'] ?? ''));
    
    // Validasi input
    if (empty($booking_id) || $rating < 1 || $rating > 5) {
        header("Location: ../../frontend/pages/learner/riwayat.php?error=invalid_rating");
        exit();
    }
    
    if (empty($review_text)) {
        header("Location: ../../frontend/pages/learner/riwayat.php?error=empty_review&booking_id=" . $booking_id);
        exit();
    }
    
    $booking_check = "SELECT id, tutor_id, status FROM bookings 
                      WHERE id = '$booking_id' AND learner_id = '$learner_id' AND status = 'completed'";
    $booking_result = mysqli_query($conn, $booking_check);
    
    if (!$booking_result || mysqli_num_rows($booking_result) == 0) {
        header("Location: ../../frontend/pages/learner/riwayat.php?error=invalid_booking");
        exit();
    }
    
    $booking_data = mysqli_fetch_assoc($booking_result);
    $tutor_id = $booking_data['tutor_id'];
    
    $review_check = "SELECT id FROM reviews WHERE booking_id = '$booking_id'";
    $review_result = mysqli_query($conn, $review_check);
    
    if (mysqli_num_rows($review_result) > 0) {
        header("Location: ../../frontend/pages/learner/riwayat.php?error=review_exists");
        exit();
    }
    
    $review_text_escaped = mysqli_real_escape_string($conn, $review_text);
    $tutor_id_escaped = mysqli_real_escape_string($conn, $tutor_id);
    
    $query = "INSERT INTO reviews (booking_id, learner_id, tutor_id, rating, review_text) 
              VALUES ('$booking_id', '$learner_id', '$tutor_id_escaped', '$rating', '$review_text_escaped')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../../frontend/pages/learner/riwayat.php?status=review_success");
    } else {
        header("Location: ../../frontend/pages/learner/riwayat.php?error=db_error&booking_id=" . $booking_id);
    }
    exit();
    
} else {
    header("Location: ../../frontend/pages/learner/riwayat.php");
    exit();
}
?>

