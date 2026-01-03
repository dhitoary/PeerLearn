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
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $jenjang = mysqli_real_escape_string($conn, $_POST['jenjang']);
    $sekolah = mysqli_real_escape_string($conn, $_POST['sekolah']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    $minat = mysqli_real_escape_string($conn, $_POST['minat']);
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Aktif');
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
        return;
    }
    
    // Cek apakah email sudah ada
    $checkEmail = "SELECT id FROM siswa WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmail);
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        return;
    }
    
    $query = "INSERT INTO siswa (nama_lengkap, email, jenjang, sekolah, kelas, minat, status, created_at) 
              VALUES ('$nama', '$email', '$jenjang', '$sekolah', '$kelas', '$minat', '$status', NOW())";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Siswa berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan siswa: ' . mysqli_error($conn)]);
    }
}

function updateSiswa($conn) {
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $jenjang = mysqli_real_escape_string($conn, $_POST['jenjang']);
    $sekolah = mysqli_real_escape_string($conn, $_POST['sekolah']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    $minat = mysqli_real_escape_string($conn, $_POST['minat']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
        return;
    }
    
    // Cek apakah email sudah digunakan oleh siswa lain
    $checkEmail = "SELECT id FROM siswa WHERE email = '$email' AND id != $id";
    $result = mysqli_query($conn, $checkEmail);
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah digunakan oleh siswa lain']);
        return;
    }
    
    $query = "UPDATE siswa SET 
              nama_lengkap = '$nama',
              email = '$email',
              jenjang = '$jenjang',
              sekolah = '$sekolah',
              kelas = '$kelas',
              minat = '$minat',
              status = '$status'
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Data siswa berhasil diupdate']);
    } else {
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
    $checkBooking = "SELECT COUNT(*) as total FROM bookings WHERE learner_id = $id AND status IN ('pending', 'confirmed')";
    $result = mysqli_query($conn, $checkBooking);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['total'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus siswa yang memiliki booking aktif']);
        return;
    }
    
    $query = "DELETE FROM siswa WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Siswa berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus siswa: ' . mysqli_error($conn)]);
    }
}

function readSiswa($conn) {
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        return;
    }
    
    $query = "SELECT * FROM siswa WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    }
}
?>
