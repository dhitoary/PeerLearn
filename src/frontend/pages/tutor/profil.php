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
    <title>Profil Saya - PeerLearn</title>
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

        .profile-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #cc5500, #ff9329);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin: 0 auto 20px;
        }

        .form-group {
            margin-bottom: 25px;
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
<div class="profile-container">
    <?php if ($success == 'updated'): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> Profil berhasil diperbarui!
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="bi bi-person"></i>
            </div>
            <h2 style="margin: 0 0 10px 0; color: #1a202c;">Profil Tutor</h2>
            <p style="color: #666; margin: 0;"><?php echo htmlspecialchars($tutor_data['email']); ?></p>
        </div>

        <form action="../../../backend/tutor/update_profile.php" method="POST">
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($tutor_data['nama_lengkap'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($tutor_data['email']); ?>" disabled style="background: #f5f5f5;">
                <small style="color: #666;">Email tidak dapat diubah</small>
            </div>

            <div class="form-group">
                <label class="form-label">Keahlian / Spesialisasi</label>
                <input type="text" name="keahlian" class="form-control" value="<?php echo htmlspecialchars($tutor_data['keahlian'] ?? ''); ?>" placeholder="Contoh: Matematika & Fisika">
            </div>

            <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0; color: #666; font-size: 14px;">
                    <i class="bi bi-info-circle"></i> <strong>Catatan:</strong> Untuk saat ini, hanya Nama Lengkap dan Keahlian yang dapat diubah. Fitur lengkap profil akan segera tersedia.
                </p>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="btn-save">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="dashboard_tutor.php" style="padding: 12px 30px; border: 2px solid #e0e0e0; border-radius: 8px; text-decoration: none; color: #666; font-weight: 600;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
