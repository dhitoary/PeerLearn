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
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $keahlian = $_POST['keahlian'];
    $pendidikan = $_POST['pendidikan'];
    $status = $_POST['status'] ?? 'Aktif';
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
        return;
    }
    
    // Cek apakah email sudah ada menggunakan prepared statement
    $checkEmail = "SELECT id FROM tutor WHERE email = ?";
    $stmt = mysqli_prepare($conn, $checkEmail);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        return;
    }
    mysqli_stmt_close($stmt);
    
    // Insert dengan prepared statement
    $query = "INSERT INTO tutor (nama_lengkap, email, keahlian, pendidikan, status, created_at) 
              VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $nama, $email, $keahlian, $pendidikan, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'message' => 'Tutor berhasil ditambahkan']);
    } else {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan tutor!']);
    }
}

function updateTutor($conn) {
    $id = intval($_POST['id']);
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $keahlian = $_POST['keahlian'];
    $pendidikan = $_POST['pendidikan'];
    $status = $_POST['status'];
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
        return;
    }
    
    // Cek apakah email sudah digunakan oleh tutor lain
    $checkEmail = "SELECT id FROM tutor WHERE email = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $checkEmail);
    mysqli_stmt_bind_param($stmt, "si", $email, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Email sudah digunakan oleh tutor lain']);
        return;
    }
    mysqli_stmt_close($stmt);
    
    $query = "UPDATE tutor SET 
              nama_lengkap = ?,
              email = ?,
              keahlian = ?,
              pendidikan = ?,
              status = ?
              WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssi", $nama, $email, $keahlian, $pendidikan, $status, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'message' => 'Data tutor berhasil diupdate']);
    } else {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate tutor!']);
    }
}

function deleteTutor($conn) {
    $id = intval($_POST['id']);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        return;
    }
    
    // Cek apakah tutor memiliki kelas aktif
    $checkBooking = "SELECT COUNT(*) as total FROM bookings WHERE tutor_id = ? AND status IN ('pending', 'confirmed')";
    $stmt = mysqli_prepare($conn, $checkBooking);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($row['total'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus tutor yang memiliki kelas aktif']);
        return;
    }
    
    $query = "DELETE FROM tutor WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'message' => 'Tutor berhasil dihapus']);
    } else {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus tutor!']);
    }
}

function readTutor($conn) {
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        return;
    }
    
    $query = "SELECT * FROM tutor WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    }
}
?>
