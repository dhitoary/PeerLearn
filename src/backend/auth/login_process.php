<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: ../../frontend/pages/auth/login.php?error=empty_fields");
        exit();
    }

    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['user_role'] = $row['role'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['is_logged_in'] = true;

            if ($row['role'] == 'admin') {
                header("Location: ../../frontend/pages/admin/dashboard.php");
            } elseif ($row['role'] == 'tutor') {
                header("Location: ../../frontend/pages/tutor/dashboard_tutor.php");
            } elseif ($row['role'] == 'learner') {
                header("Location: ../../frontend/pages/learner/dashboard_siswa.php");
            } else {
                header("Location: ../../frontend/pages/public/landing_page.php");
            }
            exit();
        } else {
            header("Location: ../../frontend/pages/auth/login.php?error=wrong_password");
            exit();
        }
    } else {
        header("Location: ../../frontend/pages/auth/login.php?error=user_not_found");
        exit();
    }
} else {
    header("Location: ../../frontend/pages/auth/login.php");
    exit();
}
?>