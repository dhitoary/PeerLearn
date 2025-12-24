<?php
session_start();

require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'tutor') {
    header("Location: ../../frontend/pages/auth/login.php?error=access_denied");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $subject_name = htmlspecialchars($_POST['subject_name'] ?? '');
    $price        = htmlspecialchars($_POST['price'] ?? '');
    $description  = htmlspecialchars($_POST['description'] ?? '');
    $tutor_id     = $_SESSION['user_id']; 

    if (empty($subject_name) || empty($price)) {
        header("Location: ../../frontend/pages/tutor/dashboard_tutor.php?error=empty_fields");
        exit();
    }

    // [TODO: ANGGOTA 3 TULIS QUERY SQL DISINI]
    $query_simpan = "INSERT INTO subjects ..."; 

    // Eksekusi Query
    // if (mysqli_query($conn, $query_simpan)) {
    //     header("Location: ../../frontend/pages/tutor/dashboard_tutor.php?success=class_created");
    // } else {
    //     header("Location: ../../frontend/pages/tutor/dashboard_tutor.php?error=db_error");
    // }

    // --- BATAS KERJAAN ANGGOTA 3 ---

    header("Location: ../../frontend/pages/tutor/dashboard_tutor.php?status=simulated_success");
    exit();

} else {
    header("Location: ../../frontend/pages/tutor/dashboard_tutor.php");
    exit();
}
?>