<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'learner') {
    header("Location: ../../frontend/pages/auth/login.php?error=unauthorized");
    exit();
}

$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

// Get siswa_id from email using prepared statement
$siswa_query = "SELECT id FROM siswa WHERE email = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $siswa_query);
mysqli_stmt_bind_param($stmt, "s", $user_email);
mysqli_stmt_execute($stmt);
$siswa_result = mysqli_stmt_get_result($stmt);
$siswa_data = mysqli_fetch_assoc($siswa_result);
mysqli_stmt_close($stmt);

if (!$siswa_data) {
    header("Location: ../../frontend/pages/auth/login.php");
    exit();
}

$learner_id = $siswa_data['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $review_text = trim($_POST['review_text'] ?? '');
    
    // Validasi input
    if (empty($booking_id) || $rating < 1 || $rating > 5) {
        header("Location: ../../frontend/pages/learner/testimoni.php?error=invalid_rating");
        exit();
    }
    
    if (empty($review_text)) {
        header("Location: ../../frontend/pages/learner/testimoni.php?error=empty_review&booking_id=" . $booking_id);
        exit();
    }
    
    // Cek booking dengan prepared statement
    $booking_check = "SELECT id, tutor_id, status FROM bookings 
                      WHERE id = ? AND learner_id = ? AND status = 'completed'";
    $stmt = mysqli_prepare($conn, $booking_check);
    mysqli_stmt_bind_param($stmt, "ii", $booking_id, $learner_id);
    mysqli_stmt_execute($stmt);
    $booking_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($booking_result) == 0) {
        mysqli_stmt_close($stmt);
        header("Location: ../../frontend/pages/learner/testimoni.php?error=invalid_booking");
        exit();
    }
    
    $booking_data = mysqli_fetch_assoc($booking_result);
    $tutor_id = $booking_data['tutor_id'];
    mysqli_stmt_close($stmt);
    
    // Cek apakah sudah ada review
    $review_check = "SELECT id FROM reviews WHERE booking_id = ?";
    $stmt = mysqli_prepare($conn, $review_check);
    mysqli_stmt_bind_param($stmt, "i", $booking_id);
    mysqli_stmt_execute($stmt);
    $review_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($review_result) > 0) {
        mysqli_stmt_close($stmt);
        header("Location: ../../frontend/pages/learner/testimoni.php?error=review_exists");
        exit();
    }
    mysqli_stmt_close($stmt);
    
    // Insert review dengan prepared statement
    $query = "INSERT INTO reviews (booking_id, learner_id, tutor_id, rating, review_text) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iiiis", $booking_id, $learner_id, $tutor_id, $rating, $review_text);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        header("Location: ../../frontend/pages/learner/testimoni.php?status=review_success");
    } else {
        mysqli_stmt_close($stmt);
        header("Location: ../../frontend/pages/learner/testimoni.php?error=db_error&booking_id=" . $booking_id);
    }
    exit();
    
} else {
    header("Location: ../../frontend/pages/learner/testimoni.php");
    exit();
}
?>

