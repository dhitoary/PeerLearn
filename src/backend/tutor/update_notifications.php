<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor') {
    header("Location: ../../frontend/pages/auth/login.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../../frontend/pages/tutor/pengaturan.php?error=invalid_method");
    exit();
}

$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

// Get notification settings
$email_notification = isset($_POST['email_notification']) ? 1 : 0;
$booking_notification = isset($_POST['booking_notification']) ? 1 : 0;
$reminder_notification = isset($_POST['reminder_notification']) ? 1 : 0;

// Update notification preferences (assuming settings table exists)
// For now, we'll store in tutor table as JSON or separate fields
$update_query = "UPDATE tutor SET 
    email_notification = '$email_notification',
    booking_notification = '$booking_notification',
    reminder_notification = '$reminder_notification'
WHERE email = '$user_email'";

if (mysqli_query($conn, $update_query)) {
    header("Location: ../../frontend/pages/tutor/pengaturan.php?success=notifications_updated");
} else {
    header("Location: ../../frontend/pages/tutor/pengaturan.php?error=" . urlencode(mysqli_error($conn)));
}

exit();
?>
