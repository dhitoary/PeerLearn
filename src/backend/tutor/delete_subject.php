<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor') {
    header("Location: ../../frontend/pages/auth/login.php?error=unauthorized");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../../frontend/pages/tutor/mata_pelajaran.php?error=missing_id");
    exit();
}

$subject_id = intval($_GET['id']);
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

// Get tutor ID
$tutor_query = "SELECT id FROM tutor WHERE email = '$user_email' LIMIT 1";
$tutor_result = mysqli_query($conn, $tutor_query);
$tutor_data = mysqli_fetch_assoc($tutor_result);
$tutor_id = $tutor_data['id'];

// Verify subject belongs to this tutor
$verify_query = "SELECT id FROM subjects WHERE id = '$subject_id' AND tutor_id = '$tutor_id'";
$verify_result = mysqli_query($conn, $verify_query);

if (mysqli_num_rows($verify_result) == 0) {
    header("Location: ../../frontend/pages/tutor/mata_pelajaran.php?error=unauthorized_subject");
    exit();
}

// Delete subject
$delete_query = "DELETE FROM subjects WHERE id = '$subject_id'";

if (mysqli_query($conn, $delete_query)) {
    header("Location: ../../frontend/pages/tutor/mata_pelajaran.php?success=deleted");
} else {
    header("Location: ../../frontend/pages/tutor/mata_pelajaran.php?error=" . urlencode(mysqli_error($conn)));
}

exit();
?>
