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

$availability_status = mysqli_real_escape_string($conn, $_POST['availability_status']);
$availability_note = mysqli_real_escape_string($conn, $_POST['availability_note']);

// Update availability
$update_query = "UPDATE tutor SET 
    availability_status = '$availability_status',
    availability_note = '$availability_note'
WHERE email = '$user_email'";

if (mysqli_query($conn, $update_query)) {
    header("Location: ../../frontend/pages/tutor/pengaturan.php?success=availability_updated");
} else {
    header("Location: ../../frontend/pages/tutor/pengaturan.php?error=" . urlencode(mysqli_error($conn)));
}

exit();
?>
