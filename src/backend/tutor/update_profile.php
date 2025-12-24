<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor') {
    header("Location: ../../frontend/pages/auth/login.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../../frontend/pages/tutor/profil.php?error=invalid_method");
    exit();
}

$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

$nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
$keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);

$update_query = "UPDATE tutor SET 
    nama_lengkap = '$nama_lengkap',
    keahlian = '$keahlian'
WHERE email = '$user_email'";

if (mysqli_query($conn, $update_query)) {
    // Update session name if changed
    $_SESSION['user_name'] = $nama_lengkap;
    $_SESSION['name'] = $nama_lengkap;
    
    header("Location: ../../frontend/pages/tutor/profil.php?success=updated");
} else {
    header("Location: ../../frontend/pages/tutor/profil.php?error=" . urlencode(mysqli_error($conn)));
}

exit();
?>
