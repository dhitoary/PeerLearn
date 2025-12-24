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
$old_password = $_POST['old_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Validate new password matches confirmation
if ($new_password !== $confirm_password) {
    header("Location: ../../frontend/pages/tutor/pengaturan.php?error=" . urlencode("Password baru tidak cocok"));
    exit();
}

// Get current password hash
$query = "SELECT password FROM tutor WHERE email = '$user_email' LIMIT 1";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);

if (!$user_data) {
    header("Location: ../../frontend/pages/tutor/pengaturan.php?error=" . urlencode("User tidak ditemukan"));
    exit();
}

// Verify old password
if (!password_verify($old_password, $user_data['password'])) {
    header("Location: ../../frontend/pages/tutor/pengaturan.php?error=" . urlencode("Password lama salah"));
    exit();
}

// Hash new password
$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update password
$update_query = "UPDATE tutor SET password = '$new_password_hash' WHERE email = '$user_email'";

if (mysqli_query($conn, $update_query)) {
    header("Location: ../../frontend/pages/tutor/pengaturan.php?success=password_updated");
} else {
    header("Location: ../../frontend/pages/tutor/pengaturan.php?error=" . urlencode(mysqli_error($conn)));
}

exit();
?>
