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

// Get all unique students who have booked this tutor
$students_query = "SELECT DISTINCT
    s.id,
    s.nama_lengkap,
    s.email,
    s.jenjang,
    s.kelas,
    COUNT(b.id) as total_bookings,
    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_sessions,
    MAX(b.booking_date) as last_booking_date
FROM bookings b
INNER JOIN siswa s ON b.learner_id = s.id
WHERE b.tutor_id = '$tutor_id'
GROUP BY s.id
ORDER BY last_booking_date DESC";

$students_result = mysqli_query($conn, $students_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa Saya - PeerLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .students-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .student-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #cc5500;
        }

        .student-header {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FF6B35, #F7931E);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .student-info h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin: 0 0 5px 0;
        }

        .student-info p {
            font-size: 13px;
            color: #666;
            margin: 0;
        }

        .student-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 800;
            color: #cc5500;
            display: block;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .student-contact {
            display: flex;
            gap: 10px;
        }

        .btn-contact {
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

        .btn-email {
            background: #007bff;
            color: white;
        }

        .btn-whatsapp {
            background: #25D366;
            color: white;
        }

        .btn-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .stats-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
        }

        .summary-item {
            text-align: center;
        }

        .summary-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .icon-blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .icon-orange {
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            color: white;
        }

        .icon-green {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
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
            <li><a href="siswa_saya.php" class="active">Siswa Saya</a></li>
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
<div class="students-container">
    <h1 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px;">
        <i class="bi bi-people"></i> Siswa Saya
    </h1>

    <!-- Summary Stats -->
    <?php 
    $total_students = mysqli_num_rows($students_result);
    $total_all_bookings = 0;
    $total_all_completed = 0;
    
    mysqli_data_seek($students_result, 0);
    while ($row = mysqli_fetch_assoc($students_result)) {
        $total_all_bookings += $row['total_bookings'];
        $total_all_completed += $row['completed_sessions'];
    }
    mysqli_data_seek($students_result, 0);
    ?>

    <div class="stats-summary">
        <div class="summary-item">
            <div class="summary-icon icon-blue">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value"><?php echo $total_students; ?></div>
            <div class="stat-label">Total Siswa</div>
        </div>

        <div class="summary-item">
            <div class="summary-icon icon-orange">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo $total_all_bookings; ?></div>
            <div class="stat-label">Total Booking</div>
        </div>

        <div class="summary-item">
            <div class="summary-icon icon-green">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $total_all_completed; ?></div>
            <div class="stat-label">Sesi Selesai</div>
        </div>
    </div>

    <!-- Students Grid -->
    <?php if ($students_result && mysqli_num_rows($students_result) > 0): ?>
        <div class="students-grid">
            <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                <div class="student-card">
                    <div class="student-header">
                        <div class="student-avatar">
                            <?php echo strtoupper(substr($student['nama_lengkap'], 0, 1)); ?>
                        </div>
                        <div class="student-info">
                            <h3><?php echo htmlspecialchars($student['nama_lengkap']); ?></h3>
                            <p><i class="bi bi-mortarboard"></i> <?php echo htmlspecialchars($student['jenjang']) . ' - ' . htmlspecialchars($student['kelas']); ?></p>
                            <p style="margin-top: 5px;"><i class="bi bi-calendar3"></i> Terakhir: <?php echo date('d M Y', strtotime($student['last_booking_date'])); ?></p>
                        </div>
                    </div>

                    <div class="student-stats">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $student['total_bookings']; ?></span>
                            <div class="stat-label">Total Booking</div>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $student['completed_sessions']; ?></span>
                            <div class="stat-label">Sesi Selesai</div>
                        </div>
                    </div>

                    <div class="student-contact">
                        <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>" class="btn-contact btn-email">
                            <i class="bi bi-envelope"></i> Email
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 80px 20px; color: #999; background: white; border-radius: 15px;">
            <i class="bi bi-people" style="font-size: 80px; display: block; margin-bottom: 20px;"></i>
            <h3 style="color: #666; font-weight: 600;">Belum Ada Siswa</h3>
            <p>Siswa yang melakukan booking akan muncul di sini</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
