<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor') {
    header("Location: ../../frontend/pages/auth/login.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../../frontend/pages/tutor/mata_pelajaran.php?error=invalid_method");
    exit();
}

$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

// Get tutor ID
$tutor_query = "SELECT id FROM tutor WHERE email = '$user_email' LIMIT 1";
$tutor_result = mysqli_query($conn, $tutor_query);
$tutor_data = mysqli_fetch_assoc($tutor_result);
$tutor_id = $tutor_data['id'];

$subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$price = intval($_POST['price']);

$insert_query = "INSERT INTO subjects (tutor_id, subject_name, description, price) 
                 VALUES ('$tutor_id', '$subject_name', '$description', '$price')";

if (mysqli_query($conn, $insert_query)) {
    header("Location: ../../frontend/pages/tutor/mata_pelajaran.php?success=added");
} else {
    header("Location: ../../frontend/pages/tutor/mata_pelajaran.php?error=" . urlencode(mysqli_error($conn)));
}

exit();
?>
