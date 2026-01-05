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
        createSiswa($conn);
        break;
    case 'update':
        updateSiswa($conn);
        break;
    case 'delete':
        deleteSiswa($conn);
        break;
    case 'read':
        readSiswa($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function createSiswa($conn) {
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $jenjang = $_POST['jenjang'];
    $sekolah = $_POST['sekolah'];
    $kelas = $_POST['kelas'];
    $minat = $_POST['minat'];
    $status = $_POST['status'] ?? 'Aktif';
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
        return;
    }
    
    // Cek apakah email sudah ada menggunakan prepared statement
    $checkEmail = "SELECT id FROM siswa WHERE email = ?";
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
    $query = "INSERT INTO siswa (nama_lengkap, email, jenjang, sekolah, kelas, minat, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssss", $nama, $email, $jenjang, $sekolah, $kelas, $minat, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'message' => 'Siswa berhasil ditambahkan']);
    } else {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan siswa!']);
    }
}

function updateSiswa($conn) {
    $id = intval($_POST['id']);
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $jenjang = $_POST['jenjang'];
    $sekolah = $_POST['sekolah'];
    $kelas = $_POST['kelas'];
    $minat = $_POST['minat'];
    $status = $_POST['status'];
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
        return;
    }
    
    // Cek apakah email sudah digunakan oleh siswa lain
    $checkEmail = "SELECT id FROM siswa WHERE email = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $checkEmail);
    mysqli_stmt_bind_param($stmt, "si", $email, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Email sudah digunakan oleh siswa lain']);
        return;
    }
    mysqli_stmt_close($stmt);
    
    $query = "UPDATE siswa SET 
              nama_lengkap = ?,
              email = ?,
              jenjang = ?,
              sekolah = ?,
              kelas = ?,
              minat = ?,
              status = ?
              WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssssi", $nama, $email, $jenjang, $sekolah, $kelas, $minat, $status, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'message' => 'Data siswa berhasil diupdate']);
    } else {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate siswa: ' . mysqli_error($conn)]);
    }
}

function deleteSiswa($conn) {
    $id = intval($_POST['id']);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        return;
    }
    
    // Cek apakah siswa memiliki booking aktif
    $checkBooking = "SELECT COUNT(*) as total FROM bookings WHERE learner_id = ? AND status IN ('pending', 'confirmed')";
    $stmt = mysqli_prepare($conn, $checkBooking);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($row['total'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus siswa yang memiliki booking aktif']);
        return;
    }
    
    $query = "DELETE FROM siswa WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'message' => 'Siswa berhasil dihapus']);
    } else {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus siswa!']);
    }
}

function readSiswa($conn) {
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        return;
    }
    
    $query = "SELECT * FROM siswa WHERE id = ?";
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
