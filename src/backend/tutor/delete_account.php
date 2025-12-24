<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor') {
    header("Location: ../../frontend/pages/auth/login.php?error=unauthorized");
    exit();
}

$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

// Get tutor ID
$tutor_query = "SELECT id FROM tutor WHERE email = '$user_email' LIMIT 1";
$tutor_result = mysqli_query($conn, $tutor_query);
$tutor_data = mysqli_fetch_assoc($tutor_result);
$tutor_id = $tutor_data['id'];

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Delete all bookings
    $delete_bookings = "DELETE FROM bookings WHERE tutor_id = '$tutor_id'";
    mysqli_query($conn, $delete_bookings);
    
    // Delete all subjects
    $delete_subjects = "DELETE FROM subjects WHERE tutor_id = '$tutor_id'";
    mysqli_query($conn, $delete_subjects);
    
    // Delete tutor_mapel
    $delete_mapel = "DELETE FROM tutor_mapel WHERE tutor_id = '$tutor_id'";
    mysqli_query($conn, $delete_mapel);
    
    // Delete iklan_tutor if exists
    $delete_iklan = "DELETE FROM iklan_tutor WHERE tutor_id = '$tutor_id'";
    mysqli_query($conn, $delete_iklan);
    
    // Finally delete tutor account
    $delete_tutor = "DELETE FROM tutor WHERE id = '$tutor_id'";
    mysqli_query($conn, $delete_tutor);
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Destroy session
    session_destroy();
    
    header("Location: ../../frontend/pages/public/landing_page.php?success=account_deleted");
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    header("Location: ../../frontend/pages/tutor/pengaturan.php?error=" . urlencode("Gagal menghapus akun"));
}

exit();
?>
