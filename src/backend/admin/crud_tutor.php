<?php
session_start();
require_once '../../config/database.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Ambil aksi dari request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        createTutor($conn);
        break;
    case 'update':
        updateTutor($conn);
        break;
    case 'delete':
        deleteTutor($conn);
        break;
    case 'read':
        readTutor($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function createTutor($conn) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
    $pendidikan = mysqli_real_escape_string($conn, $_POST['pendidikan']);
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Aktif');
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
        return;
    }
    
    // Cek apakah email sudah ada
    $checkEmail = "SELECT id FROM tutor WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmail);
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        return;
    }
    
    $query = "INSERT INTO tutor (nama_lengkap, email, keahlian, pendidikan, status, created_at) 
              VALUES ('$nama', '$email', '$keahlian', '$pendidikan', '$status', NOW())";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Tutor berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan tutor: ' . mysqli_error($conn)]);
    }
}

function updateTutor($conn) {
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
    $pendidikan = mysqli_real_escape_string($conn, $_POST['pendidikan']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
        return;
    }
    
    // Cek apakah email sudah digunakan oleh tutor lain
    $checkEmail = "SELECT id FROM tutor WHERE email = '$email' AND id != $id";
    $result = mysqli_query($conn, $checkEmail);
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah digunakan oleh tutor lain']);
        return;
    }
    
    $query = "UPDATE tutor SET 
              nama_lengkap = '$nama',
              email = '$email',
              keahlian = '$keahlian',
              pendidikan = '$pendidikan',
              status = '$status'
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Data tutor berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate tutor: ' . mysqli_error($conn)]);
    }
}

function deleteTutor($conn) {
    $id = intval($_POST['id']);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        return;
    }
    
    // Cek apakah tutor memiliki kelas aktif
    $checkBooking = "SELECT COUNT(*) as total FROM bookings WHERE tutor_id = $id AND status IN ('pending', 'confirmed')";
    $result = mysqli_query($conn, $checkBooking);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['total'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus tutor yang memiliki kelas aktif']);
        return;
    }
    
    $query = "DELETE FROM tutor WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Tutor berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus tutor: ' . mysqli_error($conn)]);
    }
}

function readTutor($conn) {
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        return;
    }
    
    $query = "SELECT * FROM tutor WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    }
}
?>
