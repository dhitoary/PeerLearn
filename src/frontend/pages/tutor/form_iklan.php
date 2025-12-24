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
    <title>Buat Iklan Tutor - PeerLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    :root {
        --primary-color: #FF6B35;
        --primary-dark: #E55A2B;
        --secondary-color: #1B4965;
        --accent-color: #9FD3C7;
        --success-color: #48bb78;
        --warning-color: #FF6B35;
        --danger-color: #f56565;
        --dark-color: #0A1628;
        --light-bg: #f5f5f5;
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        --hover-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        --border-radius-lg: 1rem;
        --border-radius-md: 0.75rem;
    }

    body {
        background: var(--light-bg);
        font-family: 'Poppins', sans-serif;
    }

    .card-custom {
        background: white;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--card-shadow);
        padding: 30px;
        transition: 0.3s ease;
    }

    .card-custom:hover {
        box-shadow: var(--hover-shadow);
    }

    .btn-primary {
        background: var(--primary-color) !important;
        border: none;
        border-radius: var(--border-radius-md);
    }

    .btn-primary:hover {
        background: var(--primary-dark) !important;
    }

    label {
        font-weight: 600;
        color: var(--secondary-color);
    }

    .form-control,
    .form-select {
        border-radius: var(--border-radius-md);
        padding: 10px 14px;
    }

    .page-title {
        font-size: 26px;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 20px;
    }

    .alert {
        padding: 15px 20px;
        border-radius: var(--border-radius-md);
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

    /* Navbar styles */
    .sb-navbar {
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 100;
        margin-bottom: 20px;
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
        text-decoration: none;
    }

    .sb-brand .logo {
        width: 40px;
        height: 40px;
    }

    .btn-back {
        background: var(--secondary-color);
        color: white;
        padding: 10px 20px;
        border-radius: var(--border-radius-md);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-back:hover {
        background: #cc5500;
        color: white;
    }
</style>

</head>

<body>

<!-- NAVBAR -->
<nav class="sb-navbar">
    <div class="sb-nav-container">
        <a href="dashboard_tutor.php" class="sb-brand">
            <img src="../../../assets/img/logo.png" alt="PeerLearn Logo" class="logo">
            <span>PeerLearn</span>
        </a>

        <a href="dashboard_tutor.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</nav>

<div class="container py-5">
    
    <h2 class="page-title mb-4">Buat Iklan Tutor</h2>

    <?php if ($success == 'created'): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> Iklan tutor berhasil dibuat!
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="card-custom">

        <form action="../../../backend/tutor/create_iklan.php" method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label>Judul Iklan</label>
                <input type="text" name="judul" class="form-control" placeholder="Contoh: Tutor Matematika Berpengalaman" required>
            </div>

            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="5" placeholder="Tuliskan deskripsi lengkap tentang diri Anda..." required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Mata Pelajaran</label>
                    <select name="subject" class="form-select" required>
                        <option value="">-- pilih --</option>
                        <option value="Matematika">Matematika</option>
                        <option value="Bahasa Inggris">Bahasa Inggris</option>
                        <option value="IPA">IPA</option>
                        <option value="IPS">IPS</option>
                        <option value="Fisika">Fisika</option>
                        <option value="Kimia">Kimia</option>
                        <option value="Biologi">Biologi</option>
                        <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                        <option value="Programming">Programming</option>
                        <option value="Web Development">Web Development</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Jenjang</label>
                    <select name="jenjang" class="form-select" required>
                        <option value="">-- pilih --</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                        <option value="Umum">Umum/Kuliah</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Harga Per Sesi (Rp)</label>
                    <input type="number" name="harga" class="form-control" placeholder="50000" min="0" step="1000" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select" required>
                        <option value="available">Tersedia</option>
                        <option value="busy">Sibuk</option>
                        <option value="limited">Terbatas</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label>Lokasi / Kota</label>
                <input type="text" name="kota" class="form-control" placeholder="Contoh: Jakarta Selatan" required>
            </div>

            <div class="mb-3">
                <label>Pengalaman (Tahun)</label>
                <input type="number" name="pengalaman" class="form-control" placeholder="contoh: 2" min="0" required>
            </div>

            <div class="mb-3">
                <label>Foto Profil Tutor (Opsional)</label>
                <input type="file" name="foto" class="form-control" accept="image/*">
                <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 5MB</small>
            </div>

            <div class="mb-3">
                <label>Link Video Pengenalan (Opsional)</label>
                <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-save"></i> Buat Iklan
                </button>
                <a href="dashboard_tutor.php" class="btn btn-secondary px-4 py-2">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
