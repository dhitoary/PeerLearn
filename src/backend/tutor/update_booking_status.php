<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor') {
    header("Location: ../../frontend/pages/auth/login.php?error=unauthorized");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header("Location: ../../frontend/pages/tutor/jadwal_saya.php?error=missing_params");
    exit();
}

$booking_id = intval($_GET['id']);
$status = mysqli_real_escape_string($conn, $_GET['status']);

// Validate status
$allowed_statuses = ['confirmed', 'cancelled', 'completed'];
if (!in_array($status, $allowed_statuses)) {
    header("Location: ../../frontend/pages/tutor/jadwal_saya.php?error=invalid_status");
    exit();
}

// Get tutor email
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

// Get tutor ID
$tutor_query = "SELECT id FROM tutor WHERE email = '$user_email' LIMIT 1";
$tutor_result = mysqli_query($conn, $tutor_query);
$tutor_data = mysqli_fetch_assoc($tutor_result);
$tutor_id = $tutor_data['id'];

// Verify booking belongs to this tutor
$verify_query = "SELECT id FROM bookings WHERE id = '$booking_id' AND tutor_id = '$tutor_id'";
$verify_result = mysqli_query($conn, $verify_query);

if (mysqli_num_rows($verify_result) == 0) {
    header("Location: ../../frontend/pages/tutor/jadwal_saya.php?error=unauthorized_booking");
    exit();
}

// Update booking status
$update_query = "UPDATE bookings SET status = '$status' WHERE id = '$booking_id'";

if (mysqli_query($conn, $update_query)) {
    header("Location: ../../frontend/pages/tutor/jadwal_saya.php?success=status_updated");
} else {
    header("Location: ../../frontend/pages/tutor/jadwal_saya.php?error=" . urlencode(mysqli_error($conn)));
}

exit();
?>
