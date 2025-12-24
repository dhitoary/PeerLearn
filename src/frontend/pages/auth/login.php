<?php
session_set_cookie_params(0, '/'); 
session_start();

$dbPath = dirname(__DIR__, 3) . '/config/database.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    die("System Error: Database configuration not found at: " . $dbPath);
}

if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else if ($_SESSION['user_role'] === 'learner') {
        header("Location: ../learner/dashboard_siswa.php");
    } else if ($_SESSION['user_role'] === 'tutor') {
        header("Location: ../public/landing_page.php");
    }
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === 'admin@peerlearn.com' && $password === 'admin123') {
        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_role'] = 'admin'; 
        $_SESSION['user_name'] = 'Administrator';
        $_SESSION['user_email'] = $email;
        $_SESSION['user_id'] = 0;

        header("Location: ../admin/dashboard.php");
        exit;
    }

    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi.";
    } else {
        $query = "SELECT id, name, email, password, role, status FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                
                if ($user['status'] === 'banned') {
                    $error = "Akun Anda telah dinonaktifkan. Hubungi admin untuk informasi lebih lanjut.";
                } else if ($user['status'] === 'pending' && $user['role'] === 'tutor') {
                    $error = "Akun tutor Anda masih menunggu verifikasi dari admin.";
                } else {
                    $_SESSION['is_logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_status'] = $user['status'];

                    if ($user['role'] === 'admin') {
                        header("Location: ../admin/dashboard.php");
                    } else if ($user['role'] === 'learner') {
                        header("Location: ../learner/dashboard_siswa.php");
                    } else if ($user['role'] === 'tutor') {
                        header("Location: ../public/landing_page.php");
                    } else {
                        header("Location: ../public/landing_page.php");
                    }
                    exit;
                }
            } else {
                $error = "Email atau password yang Anda masukkan salah.";
            }
        } else {
            $error = "Email atau password yang Anda masukkan salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - PeerLearn</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 3L1 9L5 11.18V17.18L12 21L19 17.18V11.18L21 10.09V17H23V9L12 3M18.82 9L12 12.72L5.18 9L12 5.28L18.82 9M17 16L12 18.72L7 16V12.27L12 15L17 12.27V16Z"/>
                </svg>
            </div>

            <h1 class="auth-title">PeerLearn</h1>
            <p class="auth-subtitle">Platform Bimbingan Belajar Terpercaya</p>

            <!-- Tabs -->
            <div class="auth-tabs">
                <button class="auth-tab active">Masuk</button>
                <a href="register.php" class="auth-tab">Daftar</a>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                        if ($_GET['success'] == 'registered') {
                            echo "Pendaftaran berhasil! Silakan masuk dengan akun Anda.";
                        } else if ($_GET['success'] == 'registered_pending') {
                            echo "Pendaftaran berhasil! Akun tutor Anda menunggu verifikasi admin.";
                        }
                    ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-input" id="email" name="email" placeholder="nama@email.com" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-toggle">
                        <input type="password" class="form-input" id="password" name="password" placeholder="Masukkan password" required>
                        <span class="toggle-icon" onclick="togglePassword()">👁️</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Masuk</button>
            </form>

            <div class="auth-footer">
                Belum punya akun? <a href="register.php">Daftar</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        }
    </script>
</body>
</html>