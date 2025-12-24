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
$tutor_id = $tutor_data['id'];

// Get all subjects taught by this tutor
$subjects_query = "SELECT * FROM subjects WHERE tutor_id = '$tutor_id' ORDER BY subject_name ASC";
$subjects_result = mysqli_query($conn, $subjects_query);

// Get subjects with booking count
$subjects_with_stats_query = "SELECT 
    s.id,
    s.subject_name,
    s.description,
    s.price,
    COUNT(b.id) as total_bookings
FROM subjects s
LEFT JOIN bookings b ON s.id = b.subject_id
WHERE s.tutor_id = '$tutor_id'
GROUP BY s.id
ORDER BY s.subject_name ASC";

$subjects_with_stats_result = mysqli_query($conn, $subjects_with_stats_query);

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Pelajaran - PeerLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .subjects-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .add-subject-card {
            background: linear-gradient(135deg, #cc5500, #ff9329);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-add {
            background: white;
            color: #cc5500;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .subject-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .subject-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #cc5500;
        }

        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .subject-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
        }

        .subject-price {
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
        }

        .subject-description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
            min-height: 60px;
        }

        .subject-stats {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 800;
            color: #cc5500;
        }

        .stat-label {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }

        .subject-actions {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .btn-edit {
            background: #007bff;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 40px;
            border-radius: 15px;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
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
            <li><a href="mata_pelajaran.php" class="active">Mata Pelajaran</a></li>
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

function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}

function deleteSubject(id, name) {
    if (confirm('Apakah Anda yakin ingin menghapus mata pelajaran "' + name + '"?')) {
        window.location.href = '../../../backend/tutor/delete_subject.php?id=' + id;
    }
}
</script>

<!-- MAIN CONTENT -->
<div class="subjects-container">
    <h1 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px;">
        <i class="bi bi-book"></i> Mata Pelajaran Saya
    </h1>

    <?php if ($success == 'added'): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> Mata pelajaran berhasil ditambahkan!
        </div>
    <?php elseif ($success == 'deleted'): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> Mata pelajaran berhasil dihapus!
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Add Subject Card -->
    <div class="add-subject-card">
        <div>
            <h2 style="margin: 0 0 10px 0; font-size: 24px;">Tambah Mata Pelajaran Baru</h2>
            <p style="margin: 0; opacity: 0.9;">Kelola mata pelajaran yang Anda ajarkan</p>
        </div>
        <button onclick="openAddModal()" class="btn-add">
            <i class="bi bi-plus-circle"></i> Tambah Mata Pelajaran
        </button>
    </div>

    <!-- Subjects Grid -->
    <?php if ($subjects_with_stats_result && mysqli_num_rows($subjects_with_stats_result) > 0): ?>
        <div class="subjects-grid">
            <?php while ($subject = mysqli_fetch_assoc($subjects_with_stats_result)): ?>
                <div class="subject-card">
                    <div class="subject-header">
                        <h3 class="subject-title"><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                        <span class="subject-price">
                            Rp <?php echo number_format($subject['price'], 0, ',', '.'); ?>
                        </span>
                    </div>

                    <div class="subject-description">
                        <?php echo htmlspecialchars($subject['description'] ?: 'Tidak ada deskripsi'); ?>
                    </div>

                    <div class="subject-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $subject['total_bookings']; ?></div>
                            <div class="stat-label">Total Booking</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">Rp <?php echo number_format($subject['price'], 0, ',', '.'); ?></div>
                            <div class="stat-label">Harga/Sesi</div>
                        </div>
                    </div>

                    <div class="subject-actions">
                        <button onclick="window.location.href='edit_subject.php?id=<?php echo $subject['id']; ?>'" class="btn-action btn-edit">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button onclick="deleteSubject(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars($subject['subject_name'], ENT_QUOTES); ?>')" class="btn-action btn-delete">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 80px 20px; color: #999; background: white; border-radius: 15px;">
            <i class="bi bi-book" style="font-size: 80px; display: block; margin-bottom: 20px;"></i>
            <h3 style="color: #666; font-weight: 600;">Belum Ada Mata Pelajaran</h3>
            <p>Tambahkan mata pelajaran yang Anda ajarkan</p>
        </div>
    <?php endif; ?>
</div>

<!-- Add Subject Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: #1a202c;">Tambah Mata Pelajaran</h2>
            <button onclick="closeAddModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">×</button>
        </div>

        <form action="../../../backend/tutor/add_subject.php" method="POST">
            <div class="form-group">
                <label class="form-label">Nama Mata Pelajaran</label>
                <input type="text" name="subject_name" class="form-control" placeholder="Contoh: Matematika SMA" required>
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Jelaskan tentang mata pelajaran ini..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Harga Per Sesi (Rp)</label>
                <input type="number" name="price" class="form-control" min="0" step="1000" placeholder="50000" required>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="btn-save">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <button type="button" onclick="closeAddModal()" style="padding: 12px 30px; border: 2px solid #e0e0e0; border-radius: 8px; background: white; color: #666; font-weight: 600; cursor: pointer;">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
