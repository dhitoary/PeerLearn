<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'learner') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

$siswa_query = "SELECT * FROM siswa WHERE email = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $siswa_query);
mysqli_stmt_bind_param($stmt, "s", $user_email);
mysqli_stmt_execute($stmt);
$siswa_result = mysqli_stmt_get_result($stmt);
$siswa_data = mysqli_fetch_assoc($siswa_result);
mysqli_stmt_close($stmt);

if (!$siswa_data) {
    header("Location: ../auth/login.php");
    exit();
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Password Change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $check_query = "SELECT password FROM siswa WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $user_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_pwd_query = "UPDATE siswa SET password = ? WHERE email = ?";
                    $stmt = mysqli_prepare($conn, $update_pwd_query);
                    mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $user_email);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $success_message = 'Password berhasil diubah!';
                    } else {
                        $error_message = 'Gagal mengubah password.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error_message = 'Password baru minimal 6 karakter!';
                }
            } else {
                $error_message = 'Password baru dan konfirmasi tidak cocok!';
            }
        } else {
            $error_message = 'Password saat ini salah!';
        }
    } 
    // Handle Profile Update
    else {
        $nama_lengkap = $_POST['nama_lengkap'];
        $nim = $_POST['nim'];
        $jenjang = $_POST['jenjang'];
        $kelas = $_POST['kelas'];
        $sekolah = $_POST['sekolah'];
        $minat = $_POST['minat'];
        
        // Handle Photo Upload
        $foto_profil = $siswa_data['foto_profil'];
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (in_array($_FILES['foto_profil']['type'], $allowed_types)) {
                if ($_FILES['foto_profil']['size'] <= $max_size) {
                    $upload_dir = '../../../uploads/profiles/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $upload_path)) {
                        // Delete old photo if exists
                        if ($siswa_data['foto_profil'] && file_exists('../../../' . $siswa_data['foto_profil'])) {
                            unlink('../../../' . $siswa_data['foto_profil']);
                        }
                        $foto_profil = 'uploads/profiles/' . $new_filename;
                    } else {
                        $error_message = 'Gagal upload foto profil.';
                    }
                } else {
                    $error_message = 'Ukuran foto maksimal 2MB!';
                }
            } else {
                $error_message = 'Format foto harus JPG, JPEG, PNG, atau GIF!';
            }
        }
        
        if (empty($error_message)) {
            $update_query = "UPDATE siswa SET 
                nama_lengkap = ?,
                nim = ?,
                jenjang = ?,
                kelas = ?,
                sekolah = ?,
                minat = ?,
                foto_profil = ?
                WHERE email = ?";
            
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ssssssss", $nama_lengkap, $nim, $jenjang, $kelas, $sekolah, $minat, $foto_profil, $user_email);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = 'Profil berhasil diperbarui!';
                // Refresh data
                $stmt2 = mysqli_prepare($conn, "SELECT * FROM siswa WHERE email = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt2, "s", $user_email);
                mysqli_stmt_execute($stmt2);
                $siswa_result = mysqli_stmt_get_result($stmt2);
                $siswa_data = mysqli_fetch_assoc($siswa_result);
                mysqli_stmt_close($stmt2);
            } else {
                $error_message = 'Gagal memperbarui profil!';
            }
            mysqli_stmt_close($stmt);
        }
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
            <img src="../../../assets/img/logo.png" alt="PeerLearn Logo" class="logo">
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
        <div class="profile-avatar" style="<?php echo !empty($siswa_data['foto_profil']) ? 'padding: 0; overflow: hidden;' : ''; ?>">
            <?php if (!empty($siswa_data['foto_profil']) && file_exists('../../../' . $siswa_data['foto_profil'])): ?>
                <img src="../../../<?php echo htmlspecialchars($siswa_data['foto_profil']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <i class="bi bi-person-fill"></i>
            <?php endif; ?>
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

        <form method="POST" action="" enctype="multipart/form-data" id="profileForm">
            <div class="form-section">
                <h3><i class="bi bi-person-badge"></i> Informasi Pribadi</h3>
                <div class="form-group full-width">
                    <label class="form-label">Foto Profil</label>
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; border: 3px solid #ff9329; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($siswa_data['foto_profil']) && file_exists('../../../' . $siswa_data['foto_profil'])): ?>
                                <img id="preview-image" src="../../../<?php echo htmlspecialchars($siswa_data['foto_profil']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i id="preview-icon" class="bi bi-person-fill" style="font-size: 40px; color: #999;"></i>
                                <img id="preview-image" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                            <?php endif; ?>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="foto_profil" id="foto_profil" class="form-input" accept="image/*" onchange="previewImage(this)">
                            <small style="color: #666; margin-top: 5px; font-size: 12px; display: block;">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                        </div>
                    </div>
                </div>
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

        <!-- Password Change Section -->
        <div class="profile-card" style="margin-top: 30px;">
            <form method="POST" action="" id="passwordForm">
                <div class="form-section">
                    <h3><i class="bi bi-shield-lock"></i> Ubah Password</h3>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Password Saat Ini</label>
                            <input type="password" name="current_password" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" id="new_password" class="form-input" minlength="6" required>
                            <small style="color: #666; margin-top: 5px; font-size: 12px;">Minimal 6 karakter</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-input" required>
                        </div>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="bi bi-key"></i> Ubah Password
                    </button>
                </div>
            </form>
        </div>
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

function previewImage(input) {
    const previewImg = document.getElementById('preview-image');
    const previewIcon = document.getElementById('preview-icon');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            if (previewIcon) previewIcon.style.display = 'none';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Password validation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Password baru dan konfirmasi password tidak cocok!');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('Password minimal 6 karakter!');
        return false;
    }
});
</script>

<?php require_once '../../layouts/footer.php'; ?>
