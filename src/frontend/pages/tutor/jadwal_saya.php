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

// Get all bookings with filters
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

$bookings_query = "SELECT 
    b.*,
    s.nama_lengkap as student_name,
    s.jenjang,
    s.kelas,
    s.email as student_email,
    sub.subject_name
FROM bookings b
INNER JOIN siswa s ON b.learner_id = s.id
INNER JOIN subjects sub ON b.subject_id = sub.id
WHERE b.tutor_id = '$tutor_id'";

if ($filter_status != 'all') {
    $bookings_query .= " AND b.status = '$filter_status'";
}

if ($filter_date) {
    $bookings_query .= " AND b.booking_date = '$filter_date'";
}

$bookings_query .= " ORDER BY b.booking_date DESC, b.booking_time DESC";

$bookings_result = mysqli_query($conn, $bookings_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Saya - PeerLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .schedule-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .filter-bar {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            gap: 20px;
            align-items: end;
        }

        .filter-group {
            flex: 1;
        }

        .filter-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }

        .filter-select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn-filter {
            background: linear-gradient(135deg, #cc5500, #ff9329);
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            height: 42px;
        }

        .schedule-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .booking-row {
            padding: 20px;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 20px;
            align-items: center;
            transition: all 0.3s;
        }

        .booking-row:hover {
            border-color: #cc5500;
            background: #f8f9fa;
        }

        .booking-info-section h4 {
            font-size: 16px;
            font-weight: 700;
            color: #1a202c;
            margin: 0 0 5px 0;
        }

        .booking-info-section p {
            font-size: 13px;
            color: #666;
            margin: 0;
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
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

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-confirm {
            background: #28a745;
            color: white;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
        }

        .btn-view {
            background: #007bff;
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
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
            <li><a href="jadwal_saya.php" class="active">Jadwal Saya</a></li>
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

function updateBookingStatus(bookingId, status) {
    if (confirm('Apakah Anda yakin ingin ' + (status === 'confirmed' ? 'mengonfirmasi' : 'membatalkan') + ' booking ini?')) {
        window.location.href = '../../../backend/tutor/update_booking_status.php?id=' + bookingId + '&status=' + status;
    }
}
</script>

<!-- MAIN CONTENT -->
<div class="schedule-container">
    <h1 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 30px;">
        <i class="bi bi-calendar3"></i> Jadwal Saya
    </h1>

    <!-- Filter Bar -->
    <form class="filter-bar" method="GET">
        <div class="filter-group">
            <label class="filter-label">Status</label>
            <select name="status" class="filter-select">
                <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                <option value="confirmed" <?php echo $filter_status == 'confirmed' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Tanggal</label>
            <input type="date" name="date" class="filter-select" value="<?php echo htmlspecialchars($filter_date); ?>">
        </div>

        <button type="submit" class="btn-filter">
            <i class="bi bi-funnel"></i> Filter
        </button>
        <a href="jadwal_saya.php" class="btn-filter" style="background: #6c757d; text-decoration: none; display: flex; align-items: center;">
            <i class="bi bi-arrow-clockwise"></i> Reset
        </a>
    </form>

    <!-- Bookings List -->
    <div class="schedule-card">
        <h2 style="font-size: 20px; font-weight: 700; color: #1a202c; margin-bottom: 25px;">
            <i class="bi bi-list-ul"></i> Daftar Jadwal
        </h2>

        <?php if ($bookings_result && mysqli_num_rows($bookings_result) > 0): ?>
            <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                <div class="booking-row">
                    <div class="booking-info-section">
                        <h4><?php echo htmlspecialchars($booking['student_name']); ?></h4>
                        <p><i class="bi bi-mortarboard"></i> <?php echo htmlspecialchars($booking['jenjang']) . ' - ' . htmlspecialchars($booking['kelas']); ?></p>
                        <p><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($booking['student_email'] ?? '-'); ?></p>
                    </div>

                    <div class="booking-info-section">
                        <h4><?php echo htmlspecialchars($booking['subject_name']); ?></h4>
                        <p><i class="bi bi-calendar3"></i> <?php echo date('d M Y', strtotime($booking['booking_date'])); ?></p>
                        <p><i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($booking['booking_time'])); ?> (<?php echo $booking['duration']; ?> menit)</p>
                    </div>

                    <div class="booking-info-section">
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
                        <?php if (!empty($booking['notes'])): ?>
                            <p style="margin-top: 8px;"><i class="bi bi-chat-left-text"></i> <?php echo htmlspecialchars(substr($booking['notes'], 0, 50)); ?>...</p>
                        <?php endif; ?>
                    </div>

                    <div class="action-buttons">
                        <?php if ($booking['status'] == 'pending'): ?>
                            <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'confirmed')" class="btn-action btn-confirm">
                                <i class="bi bi-check-circle"></i> Konfirmasi
                            </button>
                            <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'cancelled')" class="btn-action btn-cancel">
                                <i class="bi bi-x-circle"></i> Tolak
                            </button>
                        <?php elseif ($booking['status'] == 'confirmed'): ?>
                            <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'completed')" class="btn-action btn-confirm">
                                <i class="bi bi-check-circle"></i> Selesai
                            </button>
                            <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'cancelled')" class="btn-action btn-cancel">
                                <i class="bi bi-x-circle"></i> Batal
                            </button>
                        <?php else: ?>
                            <button class="btn-action btn-view">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; color: #999;">
                <i class="bi bi-calendar-x" style="font-size: 64px; display: block; margin-bottom: 20px;"></i>
                <h3 style="color: #666; font-weight: 600;">Tidak Ada Jadwal</h3>
                <p>Belum ada booking yang sesuai dengan filter yang dipilih</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
