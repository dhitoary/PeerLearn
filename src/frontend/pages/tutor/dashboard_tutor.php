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

if (!$tutor_data) {
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h2>Data tutor tidak ditemukan</h2>";
    echo "<p>Email: " . htmlspecialchars($user_email) . "</p>";
    echo "<p><a href='../auth/login.php'>Kembali ke Login</a></p>";
    echo "</body></html>";
    exit();
}

$tutor_id = $tutor_data['id'];

// Stats query untuk tutor
$stats_query = "SELECT 
    COALESCE(COUNT(*), 0) as total_booking,
    COALESCE(SUM(CASE WHEN status IN ('pending', 'confirmed') THEN 1 ELSE 0 END), 0) as active_booking,
    COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed_booking
FROM bookings 
WHERE tutor_id = '$tutor_id'";

$stats_result = mysqli_query($conn, $stats_query);
$stats = $stats_result ? mysqli_fetch_assoc($stats_result) : ['total_booking' => 0, 'active_booking' => 0, 'completed_booking' => 0];

// Total siswa unik yang pernah booking
$students_query = "SELECT COUNT(DISTINCT learner_id) as total FROM bookings WHERE tutor_id = '$tutor_id'";
$students_result = mysqli_query($conn, $students_query);
$total_students = mysqli_fetch_assoc($students_result)['total'];

// Booking terbaru
$recent_bookings_query = "SELECT 
    b.*,
    s.nama_lengkap as student_name,
    s.jenjang,
    s.kelas,
    sub.subject_name
FROM bookings b
INNER JOIN siswa s ON b.learner_id = s.id
INNER JOIN subjects sub ON b.subject_id = sub.id
WHERE b.tutor_id = '$tutor_id'
ORDER BY b.created_at DESC
LIMIT 5";

$recent_bookings_result = mysqli_query($conn, $recent_bookings_query);

// Sesi mendatang
$upcoming_query = "SELECT 
    b.*,
    s.nama_lengkap as student_name,
    s.jenjang,
    sub.subject_name
FROM bookings b
INNER JOIN siswa s ON b.learner_id = s.id
INNER JOIN subjects sub ON b.subject_id = sub.id
WHERE b.tutor_id = '$tutor_id' 
    AND b.status IN ('pending', 'confirmed')
    AND (b.booking_date > CURDATE() OR (b.booking_date = CURDATE() AND b.booking_time > CURTIME()))
ORDER BY b.booking_date ASC, b.booking_time ASC
LIMIT 1";

$upcoming_result = mysqli_query($conn, $upcoming_query);
$upcoming_booking = mysqli_fetch_assoc($upcoming_result);

// Mata pelajaran yang diajarkan
$subjects_query = "SELECT * FROM subjects WHERE tutor_id = '$tutor_id'";
$subjects_result = mysqli_query($conn, $subjects_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tutor - PeerLearn</title>
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

/* Dashboard Content Styles */
.dashboard-container {
    max-width: 1400px;
    margin: 40px auto;
    padding: 0 30px;
}

.welcome-section {
    background: linear-gradient(135deg, #cc5500 0%, #ff9329 100%);
    border-radius: 20px;
    padding: 40px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 15px;
}

.stat-icon.blue {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-icon.green {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.stat-icon.orange {
    background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
    color: white;
}

.stat-icon.purple {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.stat-value {
    font-size: 32px;
    font-weight: 800;
    color: #1a202c;
    margin: 10px 0 5px 0;
}

.stat-label {
    font-size: 14px;
    color: #718096;
    font-weight: 500;
}

.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.section-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.booking-item {
    padding: 20px;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    margin-bottom: 15px;
    transition: all 0.3s;
}

.booking-item:hover {
    border-color: #cc5500;
    background: #f8f9fa;
}

.booking-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 10px;
}

.student-name {
    font-weight: 700;
    font-size: 16px;
    color: #1a202c;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-confirmed {
    background: #d4edda;
    color: #155724;
}

.status-completed {
    background: #d1ecf1;
    color: #0c5460;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.booking-details {
    display: flex;
    gap: 20px;
    font-size: 14px;
    color: #666;
    margin-top: 10px;
}

.booking-details i {
    color: #cc5500;
}

.upcoming-card {
    background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
    border-radius: 15px;
    padding: 25px;
    color: white;
}

.subject-item {
    padding: 15px;
    border: 2px solid #f0f0f0;
    border-radius: 10px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.subject-name {
    font-weight: 600;
    color: #1a202c;
}

.subject-price {
    color: #FF6B35;
    font-weight: 700;
}

@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>

<!-- NAVBAR TUTOR -->
<nav class="sb-navbar">
    <div class="sb-nav-container">
        <div class="sb-brand">
            <img src="../../../assets/img/logo.png" alt="PeerLearn Logo" class="logo">
            <span>PeerLearn</span>
        </div>

        <ul class="sb-menu">
            <li><a href="dashboard_tutor.php" class="active">Beranda</a></li>
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
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 10px;">
            Selamat Datang, <?php echo htmlspecialchars($tutor_data['nama_lengkap']); ?>!
        </h1>
        <p style="font-size: 16px; opacity: 0.9; margin: 0;">
            Semangat mengajar hari ini! Anda memiliki <?php echo $stats['active_booking']; ?> sesi aktif yang perlu dikelola.
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_booking']; ?></div>
            <div class="stat-label">Total Booking</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-value"><?php echo $stats['active_booking']; ?></div>
            <div class="stat-label">Booking Aktif</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $stats['completed_booking']; ?></div>
            <div class="stat-label">Sesi Selesai</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value"><?php echo $total_students; ?></div>
            <div class="stat-label">Total Siswa</div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Recent Bookings -->
        <div class="section-card">
            <div class="section-title">
                <i class="bi bi-calendar3"></i> Booking Terbaru
            </div>

            <?php if ($recent_bookings_result && mysqli_num_rows($recent_bookings_result) > 0): ?>
                <?php while ($booking = mysqli_fetch_assoc($recent_bookings_result)): ?>
                    <div class="booking-item">
                        <div class="booking-header">
                            <div>
                                <div class="student-name"><?php echo htmlspecialchars($booking['student_name']); ?></div>
                                <div style="font-size: 14px; color: #666; margin-top: 3px;">
                                    <?php echo htmlspecialchars($booking['jenjang']) . ' - ' . htmlspecialchars($booking['kelas']); ?>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                <?php 
                                    $status_text = [
                                        'pending' => 'Menunggu',
                                        'confirmed' => 'Dikonfirmasi',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan'
                                    ];
                                    echo $status_text[$booking['status']] ?? $booking['status'];
                                ?>
                            </span>
                        </div>
                        
                        <div style="font-weight: 600; color: #cc5500; margin: 8px 0;">
                            <?php echo htmlspecialchars($booking['subject_name']); ?>
                        </div>

                        <div class="booking-details">
                            <span><i class="bi bi-calendar3"></i> <?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></span>
                            <span><i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($booking['booking_time'])); ?></span>
                            <span><i class="bi bi-hourglass-split"></i> <?php echo $booking['duration']; ?> menit</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 40px 0;">
                    <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                    Belum ada booking yang masuk
                </p>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Upcoming Session -->
            <?php if ($upcoming_booking): ?>
                <div class="upcoming-card" style="margin-bottom: 25px;">
                    <div style="font-size: 14px; font-weight: 600; margin-bottom: 15px; opacity: 0.9;">
                        SESI MENDATANG
                    </div>
                    <div style="font-size: 20px; font-weight: 700; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($upcoming_booking['student_name']); ?>
                    </div>
                    <div style="margin-bottom: 15px; font-size: 15px;">
                        <?php echo htmlspecialchars($upcoming_booking['subject_name']); ?>
                    </div>
                    <div style="border-top: 1px solid rgba(255,255,255,0.3); padding-top: 15px; margin-top: 15px;">
                        <div style="display: flex; gap: 15px; font-size: 14px;">
                            <span><i class="bi bi-calendar3"></i> <?php echo date('d M Y', strtotime($upcoming_booking['booking_date'])); ?></span>
                            <span><i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($upcoming_booking['booking_time'])); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Mata Pelajaran -->
            <div class="section-card">
                <div class="section-title">
                    <i class="bi bi-book"></i> Mata Pelajaran Saya
                </div>

                <?php if ($subjects_result && mysqli_num_rows($subjects_result) > 0): ?>
                    <?php while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                        <div class="subject-item">
                            <div>
                                <div class="subject-name"><?php echo htmlspecialchars($subject['subject_name']); ?></div>
                                <?php if (!empty($subject['description'])): ?>
                                    <div style="font-size: 12px; color: #666; margin-top: 3px;">
                                        <?php echo htmlspecialchars(substr($subject['description'], 0, 50)); ?><?php echo strlen($subject['description']) > 50 ? '...' : ''; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="subject-price">
                                Rp <?php echo number_format($subject['price'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 20px 0;">
                        Belum ada mata pelajaran
                    </p>
                <?php endif; ?>

                <a href="mata_pelajaran.php" style="display: block; text-align: center; margin-top: 15px; color: #cc5500; font-weight: 600; text-decoration: none;">
                    <i class="bi bi-plus-circle"></i> Kelola Mata Pelajaran
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
