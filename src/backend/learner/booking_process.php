<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'learner') {
    header("Location: ../../frontend/pages/auth/login.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tutor_id   = htmlspecialchars($_POST['tutor_id'] ?? '');
    $subject_id = htmlspecialchars($_POST['subject_id'] ?? '');
    $date       = htmlspecialchars($_POST['booking_date'] ?? '');
    $learner_id = htmlspecialchars($_POST['learner_id'] ?? ''); // Dari form, bukan session user_id

    if (empty($tutor_id) || empty($subject_id) || empty($date)) {
        header("Location: ../../frontend/pages/learner/dashboard_siswa.php?error=empty_fields");
        exit();
    }

    // Validasi tambahan untuk date dan time
    $time = htmlspecialchars($_POST['booking_time'] ?? '');
    $duration = intval($_POST['duration'] ?? 60); // Default 60 menit
    $notes = htmlspecialchars($_POST['notes'] ?? '');
    
    if (empty($time)) {
        header("Location: ../../frontend/pages/learner/booking.php?error=empty_time&tutor_id=" . $tutor_id . "&subject_id=" . $subject_id);
        exit();
    }
    
    // Validasi format tanggal
    $date_obj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $date) {
        header("Location: ../../frontend/pages/learner/booking.php?error=invalid_date&tutor_id=" . $tutor_id . "&subject_id=" . $subject_id);
        exit();
    }
    
    // Pastikan tanggal tidak di masa lalu
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    if ($date_obj < $today) {
        header("Location: ../../frontend/pages/learner/booking.php?error=past_date&tutor_id=" . $tutor_id . "&subject_id=" . $subject_id);
        exit();
    }
    
    // Query INSERT ke tabel bookings
    $tutor_id_escaped = mysqli_real_escape_string($conn, $tutor_id);
    $subject_id_escaped = mysqli_real_escape_string($conn, $subject_id);
    $date_escaped = mysqli_real_escape_string($conn, $date);
    $time_escaped = mysqli_real_escape_string($conn, $time);
    $notes_escaped = mysqli_real_escape_string($conn, $notes);
    $learner_id_escaped = mysqli_real_escape_string($conn, $learner_id);
    
    $query = "INSERT INTO bookings (learner_id, tutor_id, subject_id, booking_date, booking_time, duration, status, notes) 
              VALUES ('$learner_id_escaped', '$tutor_id_escaped', '$subject_id_escaped', '$date_escaped', '$time_escaped', '$duration', 'pending', '$notes_escaped')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../../frontend/pages/learner/dashboard_siswa.php?status=booking_success");
    } else {
        $error_msg = mysqli_error($conn);
        header("Location: ../../frontend/pages/learner/booking.php?error=db_error&msg=" . urlencode($error_msg) . "&tutor_id=" . $tutor_id . "&subject_id=" . $subject_id);
    }
    exit();

} else {
    header("Location: ../../frontend/pages/learner/dashboard_siswa.php");
    exit();
}
?>