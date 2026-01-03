<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validasi input
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        header("Location: ../../frontend/pages/auth/register.php?error=empty_fields");
        exit();
    }

    // Validasi email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../frontend/pages/auth/register.php?error=invalid_email");
        exit();
    }

    // Cek apakah email sudah terdaftar di tabel users
    $check_query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($check_result) > 0) {
        header("Location: ../../frontend/pages/auth/register.php?error=email_taken");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Begin transaction
    mysqli_begin_transaction($conn);

    try {
        // Insert ke tabel users (tanpa kolom status karena tidak ada di tabel users)
        $user_query = "INSERT INTO users (nama_lengkap, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $role);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal insert ke tabel users");
        }

        $user_id = mysqli_insert_id($conn);

        // Insert ke tabel siswa atau tutor sesuai role
        if ($role == 'learner') {
            // Ambil data siswa dari form
            $jenjang = isset($_POST['jenjang']) && !empty($_POST['jenjang']) ? $_POST['jenjang'] : 'SMA';
            $sekolah = isset($_POST['sekolah']) && !empty($_POST['sekolah']) ? htmlspecialchars(trim($_POST['sekolah'])) : 'Belum Diisi';
            $kelas = isset($_POST['kelas']) && !empty($_POST['kelas']) ? htmlspecialchars(trim($_POST['kelas'])) : '-';
            $minat = isset($_POST['minat']) && !empty($_POST['minat']) ? htmlspecialchars(trim($_POST['minat'])) : 'Belum Diisi';
            
            // Generate NIM
            $nim = 'SIS' . date('Y') . str_pad($user_id, 4, '0', STR_PAD_LEFT);
            
            // Insert ke tabel siswa
            $siswa_query = "INSERT INTO siswa (nim, nama_lengkap, email, jenjang, sekolah, kelas, minat, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'Aktif')";
            $stmt2 = mysqli_prepare($conn, $siswa_query);
            mysqli_stmt_bind_param($stmt2, "sssssss", $nim, $name, $email, $jenjang, $sekolah, $kelas, $minat);
            
            if (!mysqli_stmt_execute($stmt2)) {
                $error_msg = mysqli_stmt_error($stmt2);
                error_log("Siswa insert error: " . $error_msg);
                throw new Exception("Gagal insert ke tabel siswa: " . $error_msg);
            }
            
        } else if ($role == 'tutor') {
            // Ambil data tutor dari form
            $telepon = isset($_POST['telepon']) && !empty($_POST['telepon']) ? htmlspecialchars(trim($_POST['telepon'])) : '-';
            $keahlian = isset($_POST['keahlian']) && !empty($_POST['keahlian']) ? htmlspecialchars(trim($_POST['keahlian'])) : 'Belum Diisi';
            $pendidikan = isset($_POST['pendidikan']) && !empty($_POST['pendidikan']) ? htmlspecialchars(trim($_POST['pendidikan'])) : 'Belum Diisi';
            $pengalaman = isset($_POST['pengalaman_mengajar']) && !empty($_POST['pengalaman_mengajar']) ? (int)$_POST['pengalaman_mengajar'] : 1;
            $harga = isset($_POST['harga_per_sesi']) && !empty($_POST['harga_per_sesi']) ? (int)$_POST['harga_per_sesi'] : 100000;
            $deskripsi = isset($_POST['deskripsi']) && !empty($_POST['deskripsi']) ? htmlspecialchars(trim($_POST['deskripsi'])) : 'Tutor baru menunggu verifikasi';
            
            // Insert ke tabel tutor (status Non-Aktif karena perlu verifikasi)
            $tutor_query = "INSERT INTO tutor (nama_lengkap, email, telepon, keahlian, pendidikan, pengalaman_mengajar, harga_per_sesi, deskripsi, status, rating) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Non-Aktif', 0.0)";
            $stmt2 = mysqli_prepare($conn, $tutor_query);
            mysqli_stmt_bind_param($stmt2, "sssssiss", $name, $email, $telepon, $keahlian, $pendidikan, $pengalaman, $harga, $deskripsi);
            
            if (!mysqli_stmt_execute($stmt2)) {
                $error_msg = mysqli_stmt_error($stmt2);
                error_log("Tutor insert error: " . $error_msg);
                throw new Exception("Gagal insert ke tabel tutor: " . $error_msg);
            }
        }

        // Commit transaction
        mysqli_commit($conn);
        
        // Redirect berdasarkan role
        if ($role == 'tutor') {
            header("Location: ../../frontend/pages/auth/login.php?success=registered_pending");
        } else {
            header("Location: ../../frontend/pages/auth/login.php?success=registered");
        }
        
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        
        // Log error detail untuk debugging
        $error_detail = $e->getMessage();
        error_log("=== REGISTRATION ERROR ===");
        error_log("Email: " . $email);
        error_log("Role: " . $role);
        error_log("Error: " . $error_detail);
        error_log("========================");
        
        // Redirect dengan pesan error
        header("Location: ../../frontend/pages/auth/register.php?error=db_error&msg=" . urlencode($error_detail));
    }

    mysqli_close($conn);
    
} else {
    header("Location: ../../frontend/pages/auth/register.php");
    exit();
}
?>