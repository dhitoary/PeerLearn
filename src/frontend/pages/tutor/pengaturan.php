<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'tutor') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

$tutor_query = "SELECT * FROM tutor WHERE email = '$user_email' LIMIT 1";
$tutor_result = mysqli_query($conn, $tutor_query);
$tutor_data = mysqli_fetch_assoc($tutor_result);

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - PeerLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .sb-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .sb-nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sb-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            color: #cc5500;
        }

        .sb-brand .logo {
            width: 40px;
            height: 40px;
        }

        .sb-menu {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .sb-menu li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .sb-menu li a:hover, .sb-menu li a.active {
            color: #cc5500;
        }

        .sb-daftar {
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #cc5500, #ff9329);
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .settings-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #cc5500;
        }

        .btn-save {
            background: linear-gradient(135deg, #cc5500, #ff9329);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(12, 74, 96, 0.3);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 26px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 26px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #cc5500;
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="sb-navbar">
    <div class="sb-nav-container">
        <div class="sb-brand">
            <img src="../../../assets/img/logo.png" alt="PeerLearn Logo" class="logo">
            <span>PeerLearn</span>
        </div>

        <ul class="sb-menu">
            <li><a href="dashboard_tutor.php">Beranda</a></li>
            <li><a href="jadwal_saya.php">Jadwal Saya</a></li>
            <li><a href="siswa_saya.php">Siswa Saya</a></li>
            <li><a href="mata_pelajaran.php">Mata Pelajaran</a></li>
        </ul>

        <div style="display: flex; gap: 10px; align-items: center;">
            <div style="position: relative;">
                <button onclick="toggleDropdown()" class="sb-daftar" style="display: flex; align-items: center; gap: 8px; cursor: pointer; border: none; background: linear-gradient(135deg, #cc5500, #ff9329);">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($tutor_data['nama_lengkap']); ?>
                </button>
                <div id="userDropdown" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 200px; z-index: 1000;">
                    <div style="padding: 12px 16px; border-bottom: 1px solid #eee;">
                        <p style="margin: 0; font-weight: 600; color: #333;"><?php echo htmlspecialchars($tutor_data['nama_lengkap']); ?></p>
                        <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;"><?php echo htmlspecialchars($tutor_data['keahlian']); ?></p>
                    </div>
                    <a href="profil.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
                        <i class="bi bi-person"></i> Profil Saya
                    </a>
                    <a href="pengaturan.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
                        <i class="bi bi-gear"></i> Pengaturan
                    </a>
                    <a href="../../../backend/auth/logout.php" style="display: block; padding: 12px 16px; color: #dc3545; text-decoration: none;">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

window.onclick = function(event) {
    if (!event.target.matches('.sb-daftar') && !event.target.closest('.sb-daftar')) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        }
    }
}
</script>

<!-- MAIN CONTENT -->
<div class="settings-container">
    <h1 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px;">
        <i class="bi bi-gear"></i> Pengaturan Akun
    </h1>

    <?php if ($success == 'password_updated'): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> Password berhasil diperbarui!
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Change Password Section -->
    <div class="settings-card">
        <div class="section-title">
            <i class="bi bi-lock"></i> Ubah Password
        </div>

        <form action="../../../backend/tutor/change_password.php" method="POST">
            <div class="form-group">
                <label class="form-label">Password Lama</label>
                <input type="password" name="old_password" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" name="new_password" class="form-control" minlength="6" required>
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" class="form-control" minlength="6" required>
            </div>

            <button type="submit" class="btn-save">
                <i class="bi bi-save"></i> Ubah Password
            </button>
        </form>
    </div>

    <!-- Notification Settings -->
    <div class="settings-card">
        <div class="section-title">
            <i class="bi bi-bell"></i> Notifikasi
        </div>

        <form action="../../../backend/tutor/update_notifications.php" method="POST">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #f0f0f0;">
                <div>
                    <div style="font-weight: 600; color: #333;">Notifikasi Email</div>
                    <div style="font-size: 13px; color: #666;">Terima notifikasi booking via email</div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="email_notification" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #f0f0f0;">
                <div>
                    <div style="font-weight: 600; color: #333;">Notifikasi Booking Baru</div>
                    <div style="font-size: 13px; color: #666;">Terima notifikasi saat ada booking baru</div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="booking_notification" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0;">
                <div>
                    <div style="font-weight: 600; color: #333;">Pengingat Sesi</div>
                    <div style="font-size: 13px; color: #666;">Terima pengingat 1 jam sebelum sesi</div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="reminder_notification" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <button type="submit" class="btn-save" style="margin-top: 20px;">
                <i class="bi bi-save"></i> Simpan Pengaturan
            </button>
        </form>
    </div>

    <!-- Availability Settings -->
    <div class="settings-card">
        <div class="section-title">
            <i class="bi bi-calendar-check"></i> Ketersediaan
        </div>

        <form action="../../../backend/tutor/update_availability.php" method="POST">
            <div class="form-group">
                <label class="form-label">Status Ketersediaan</label>
                <select name="availability_status" class="form-control">
                    <option value="available">Tersedia untuk Booking</option>
                    <option value="busy">Sibuk - Tidak Menerima Booking</option>
                    <option value="limited">Ketersediaan Terbatas</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan Ketersediaan</label>
                <textarea name="availability_note" class="form-control" rows="3" placeholder="Contoh: Hanya tersedia di akhir pekan"></textarea>
            </div>

            <button type="submit" class="btn-save">
                <i class="bi bi-save"></i> Perbarui Ketersediaan
            </button>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="settings-card" style="border: 2px solid #dc3545;">
        <div class="section-title" style="color: #dc3545;">
            <i class="bi bi-exclamation-triangle"></i> Zona Berbahaya
        </div>

        <p style="color: #666; margin-bottom: 20px;">
            Tindakan di bawah ini bersifat permanen dan tidak dapat dibatalkan.
        </p>

        <button onclick="confirmDelete()" class="btn-danger">
            <i class="bi bi-trash"></i> Hapus Akun
        </button>
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm('Apakah Anda yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan!')) {
        if (confirm('Konfirmasi sekali lagi. Semua data Anda akan hilang permanen!')) {
            window.location.href = '../../../backend/tutor/delete_account.php';
        }
    }
}
</script>

</body>
</html>
