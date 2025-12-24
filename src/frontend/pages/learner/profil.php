<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'learner') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

$siswa_query = "SELECT * FROM siswa WHERE email = '$user_email' LIMIT 1";
$siswa_result = mysqli_query($conn, $siswa_query);
$siswa_data = mysqli_fetch_assoc($siswa_result);

if (!$siswa_data) {
    header("Location: ../auth/login.php");
    exit();
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $jenjang = mysqli_real_escape_string($conn, $_POST['jenjang']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    $sekolah = mysqli_real_escape_string($conn, $_POST['sekolah']);
    $minat = mysqli_real_escape_string($conn, $_POST['minat']);
    
    $update_query = "UPDATE siswa SET 
        nama_lengkap = '$nama_lengkap',
        nim = '$nim',
        jenjang = '$jenjang',
        kelas = '$kelas',
        sekolah = '$sekolah',
        minat = '$minat'
        WHERE email = '$user_email'";
    
    if (mysqli_query($conn, $update_query)) {
        $success_message = 'Profil berhasil diperbarui!';
        // Refresh data
        $siswa_result = mysqli_query($conn, $siswa_query);
        $siswa_data = mysqli_fetch_assoc($siswa_result);
    } else {
        $error_message = 'Gagal memperbarui profil: ' . mysqli_error($conn);
    }
}
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

        .sb-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            padding: 8px 0;
            transition: all 0.3s;
            border-bottom: 2px solid transparent;
        }

        .sb-menu a:hover, .sb-menu a.active {
            color: #FF6B35;
            border-bottom-color: #FF6B35;
        }

        .sb-daftar {
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .sb-daftar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .profile-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 0 30px;
        }

        .profile-header {
            background: linear-gradient(135deg, #cc5500 0%, #ff9329 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: white;
            color: #cc5500;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin: 0 auto 20px;
            border: 5px solid rgba(255,255,255,0.3);
        }

        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: #cc5500;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff9329;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-input {
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #ff9329;
            box-shadow: 0 0 0 3px rgba(154, 212, 214, 0.1);
        }

        .form-input:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- NAVBAR KHUSUS LEARNER -->
<nav class="sb-navbar">
    <div class="sb-nav-container">
        <div class="sb-brand">
            <img src="/kelompok/kelompok_21/src/assets/img/logo.png" alt="PeerLearn Logo" class="logo">
            <span>PeerLearn</span>
        </div>

        <ul class="sb-menu">
            <li><a href="dashboard_siswa.php">Beranda</a></li>
            <li><a href="../public/search_result.php">Cari Tutor</a></li>
            <li><a href="sesi_saya.php">Sesi Saya</a></li>
            <li><a href="riwayat.php">Riwayat Booking</a></li>
        </ul>

        <div style="display: flex; gap: 10px; align-items: center;">
            <div style="position: relative;">
                <button onclick="toggleDropdown()" class="sb-daftar" style="display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($siswa_data['nama_lengkap']); ?>
                </button>
                <div id="userDropdown" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 200px; z-index: 1000;">
                    <div style="padding: 12px 16px; border-bottom: 1px solid #eee;">
                        <p style="margin: 0; font-weight: 600; color: #333;"><?php echo htmlspecialchars($siswa_data['nama_lengkap']); ?></p>
                        <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;"><?php echo $siswa_data['jenjang'] . ' - ' . $siswa_data['kelas']; ?></p>
                    </div>
                    <a href="profil.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
                        <i class="bi bi-person"></i> Profil Saya
                    </a>
                    <a href="sesi_saya.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
                        <i class="bi bi-calendar-check"></i> Sesi Belajar
                    </a>
                    <a href="../../../backend/auth/logout.php" style="display: block; padding: 12px 16px; color: #dc3545; text-decoration: none;">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <i class="bi bi-person-fill"></i>
        </div>
        <h1 style="margin: 0; font-size: 32px;"><?php echo htmlspecialchars($siswa_data['nama_lengkap']); ?></h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 16px;"><?php echo htmlspecialchars($siswa_data['email']); ?></p>
    </div>

    <div class="profile-card">
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-section">
                <h3><i class="bi bi-person-badge"></i> Informasi Pribadi</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-input" 
                               value="<?php echo htmlspecialchars($siswa_data['nama_lengkap']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">NIM/NIS</label>
                        <input type="text" name="nim" class="form-input" 
                               value="<?php echo htmlspecialchars($siswa_data['nim']); ?>" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" 
                               value="<?php echo htmlspecialchars($siswa_data['email']); ?>" disabled>
                        <small style="color: #666; margin-top: 5px; font-size: 12px;">Email tidak dapat diubah</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="bi bi-building"></i> Informasi Pendidikan</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Jenjang</label>
                        <select name="jenjang" class="form-input" required>
                            <option value="SD" <?php echo $siswa_data['jenjang'] == 'SD' ? 'selected' : ''; ?>>SD</option>
                            <option value="SMP" <?php echo $siswa_data['jenjang'] == 'SMP' ? 'selected' : ''; ?>>SMP</option>
                            <option value="SMA" <?php echo $siswa_data['jenjang'] == 'SMA' ? 'selected' : ''; ?>>SMA</option>
                            <option value="Kuliah" <?php echo $siswa_data['jenjang'] == 'Kuliah' ? 'selected' : ''; ?>>Kuliah</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kelas</label>
                        <input type="text" name="kelas" class="form-input" 
                               value="<?php echo htmlspecialchars($siswa_data['kelas']); ?>" 
                               placeholder="Contoh: 12 IPA 1" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Nama Sekolah/Universitas</label>
                        <input type="text" name="sekolah" class="form-input" 
                               value="<?php echo htmlspecialchars($siswa_data['sekolah']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="bi bi-heart-fill"></i> Minat & Preferensi</h3>
                <div class="form-group">
                    <label class="form-label">Minat Belajar</label>
                    <textarea name="minat" class="form-input" rows="4" 
                              placeholder="Contoh: Matematika, Fisika, Pemrograman"><?php echo htmlspecialchars($siswa_data['minat']); ?></textarea>
                    <small style="color: #666; margin-top: 5px; font-size: 12px;">Pisahkan dengan koma untuk minat yang berbeda</small>
                </div>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                    <i class="bi bi-arrow-left"></i> Kembali
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

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

<?php require_once '../../layouts/footer.php'; ?>
