<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../frontend/pages/auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = intval($_POST['user_id']);

    if ($user_id > 0) {
        $query = "DELETE FROM users WHERE id = $user_id";
        if (mysqli_query($conn, $query)) {
            header("Location: ../../frontend/pages/admin/dashboard.php?msg=User_berhasil_dihapus");
            exit();
        } else {
            header("Location: ../../frontend/pages/admin/dashboard.php?msg=Gagal_menghapus_user");
            exit();
        }
    }
} else {
    header("Location: ../../frontend/pages/admin/dashboard.php");
    exit();
}
?>