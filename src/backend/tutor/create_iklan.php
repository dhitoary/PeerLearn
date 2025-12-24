<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor') {
    header("Location: ../../frontend/pages/auth/login.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../../frontend/pages/tutor/form_iklan.php?error=invalid_method");
    exit();
}

$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

// Get tutor ID
$tutor_query = "SELECT id FROM tutor WHERE email = '$user_email' LIMIT 1";
$tutor_result = mysqli_query($conn, $tutor_query);
$tutor_data = mysqli_fetch_assoc($tutor_result);
$tutor_id = $tutor_data['id'];

// Get form data
$judul = mysqli_real_escape_string($conn, $_POST['judul']);
$deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
$subject = mysqli_real_escape_string($conn, $_POST['subject']);
$jenjang = mysqli_real_escape_string($conn, $_POST['jenjang']);
$harga = intval($_POST['harga']);
$kota = mysqli_real_escape_string($conn, $_POST['kota']);
$pengalaman = intval($_POST['pengalaman']);
$status = mysqli_real_escape_string($conn, $_POST['status']);
$video_url = mysqli_real_escape_string($conn, $_POST['video_url'] ?? '');

// Handle file upload
$foto_name = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($_FILES['foto']['type'], $allowed_types)) {
        header("Location: ../../frontend/pages/tutor/form_iklan.php?error=" . urlencode("Format file tidak didukung. Gunakan JPG, PNG, atau GIF"));
        exit();
    }
    
    if ($_FILES['foto']['size'] > $max_size) {
        header("Location: ../../frontend/pages/tutor/form_iklan.php?error=" . urlencode("Ukuran file terlalu besar. Maksimal 5MB"));
        exit();
    }
    
    $upload_dir = '../../../uploads/iklan/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $foto_name = 'iklan_' . $tutor_id . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $foto_name;
    
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
        header("Location: ../../frontend/pages/tutor/form_iklan.php?error=" . urlencode("Gagal mengupload foto"));
        exit();
    }
}

// Insert iklan into database (assuming iklan_tutor table exists)
$insert_query = "INSERT INTO iklan_tutor (
    tutor_id, 
    judul, 
    deskripsi, 
    subject, 
    jenjang, 
    harga, 
    kota, 
    pengalaman, 
    status,
    foto,
    video_url,
    created_at
) VALUES (
    '$tutor_id',
    '$judul',
    '$deskripsi',
    '$subject',
    '$jenjang',
    '$harga',
    '$kota',
    '$pengalaman',
    '$status',
    '$foto_name',
    '$video_url',
    NOW()
)";

if (mysqli_query($conn, $insert_query)) {
    // Also add to subjects table if not exists
    $subject_check = "SELECT id FROM subjects WHERE tutor_id = '$tutor_id' AND subject_name = '$subject'";
    $subject_result = mysqli_query($conn, $subject_check);
    
    if (mysqli_num_rows($subject_result) == 0) {
        $subject_insert = "INSERT INTO subjects (tutor_id, subject_name, description, price) 
                          VALUES ('$tutor_id', '$subject', '$deskripsi', '$harga')";
        mysqli_query($conn, $subject_insert);
    }
    
    // Also insert into tutor_mapel if not exists
    $mapel_check = "SELECT id FROM tutor_mapel WHERE tutor_id = '$tutor_id' AND nama_mapel = '$subject' AND jenjang = '$jenjang'";
    $mapel_result = mysqli_query($conn, $mapel_check);
    
    if (mysqli_num_rows($mapel_result) == 0) {
        $mapel_insert = "INSERT INTO tutor_mapel (tutor_id, nama_mapel, jenjang) 
                        VALUES ('$tutor_id', '$subject', '$jenjang')";
        mysqli_query($conn, $mapel_insert);
    }
    
    header("Location: ../../frontend/pages/tutor/form_iklan.php?success=created");
} else {
    header("Location: ../../frontend/pages/tutor/form_iklan.php?error=" . urlencode(mysqli_error($conn)));
}

exit();
?>
