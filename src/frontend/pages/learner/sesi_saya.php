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

$siswa_id = $siswa_data['id'];

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'semua';

$where_clause = "WHERE b.learner_id = '$siswa_id'";
if ($active_tab == 'akan_datang') {
    $where_clause .= " AND b.status IN ('pending', 'confirmed') AND b.booking_date >= CURDATE()";
} elseif ($active_tab == 'selesai') {
    $where_clause .= " AND b.status = 'completed'";
} elseif ($active_tab == 'dibatalkan') {
    $where_clause .= " AND b.status = 'cancelled'";
}

$bookings_query = "SELECT 
    b.*,
    t.nama_lengkap as tutor_name,
    t.keahlian,
    t.foto_profil,
    s.subject_name,
    s.price
FROM bookings b
INNER JOIN tutor t ON b.tutor_id = t.id
INNER JOIN subjects s ON b.subject_id = s.id
$where_clause
ORDER BY b.booking_date DESC, b.booking_time DESC";

$bookings_result = mysqli_query($conn, $bookings_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesi Belajar Saya - PeerLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
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
            <li><a href="dashboard_siswa.php">Beranda</a></li>
            <li><a href="../public/search_result.php">Cari Tutor</a></li>
            <li><a href="sesi_saya.php" class="active">Sesi Saya</a></li>
            <li><a href="riwayat.php">Riwayat Booking</a></li>
        </ul>
        <div style="display: flex; gap: 10px; align-items: center;">
            <div style="position: relative;">
                <button onclick="toggleDropdown()" class="sb-daftar" style="display: flex; align-items: center; gap: 8px; cursor: pointer; border: none; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);">
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

<style>
.sb-navbar {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}
.sb-nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.sb-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 24px;
    font-weight: 700;
    color: #cc5500;
}
.sb-brand .logo {
    height: 40px;
    width: auto;
}
.sb-menu {
    list-style: none;
    display: flex;
    gap: 30px;
    margin: 0;
    padding: 0;
}
.sb-menu a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: color 0.3s;
    padding: 8px 0;
    border-bottom: 2px solid transparent;
}
.sb-menu a:hover, .sb-menu a.active {
    color: #FF6B35;
    border-bottom-color: #FF6B35;
}
.sb-daftar {
    padding: 10px 20px;
    border-radius: 25px;
    font-weight: 600;
    color: white;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 32px;
    color: #cc5500;
    margin-bottom: 10px;
}

.tabs {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #eee;
    margin-bottom: 30px;
}

.tab {
    padding: 12px 24px;
    text-decoration: none;
    color: #666;
    font-weight: 500;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.3s;
}

.tab:hover {
    color: #FF6B35;
}

.tab.active {
    color: #FF6B35;
    border-bottom-color: #FF6B35;
}

.session-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border-left: 4px solid #cc5500;
}

.session-card.pending { border-left-color: #FFC107; }
.session-card.confirmed { border-left-color: #cc5500; }
.session-card.completed { border-left-color: #28A745; }
.session-card.cancelled { border-left-color: #DC3545; }

.session-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
}

.tutor-info {
    display: flex;
    gap: 15px;
}

.tutor-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #cc5500, #ff9329);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: bold;
}

.tutor-details h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
    color: #333;
}

.tutor-details p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-pending { background: #FFF3CD; color: #856404; }
.status-confirmed { background: #D1ECF1; color: #0C5460; }
.status-completed { background: #D4EDDA; color: #155724; }
.status-cancelled { background: #F8D7DA; color: #721C24; }

.session-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    padding: 15px;
    background: #F8F9FA;
    border-radius: 8px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.detail-item i {
    color: #FF6B35;
    font-size: 18px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.empty-state i {
    font-size: 64px;
    color: #ccc;
    margin-bottom: 20px;
}
</style>

<div class="container">
    <div class="page-header">
        <h1><i class="bi bi-calendar-check"></i> Sesi Belajar Saya</h1>
        <p>Kelola semua jadwal belajar Anda di sini</p>
    </div>

    <div class="tabs">
        <a href="?tab=semua" class="tab <?php echo $active_tab == 'semua' ? 'active' : ''; ?>">
            Semua (<?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bookings WHERE learner_id = '$siswa_id'")); ?>)
        </a>
        <a href="?tab=akan_datang" class="tab <?php echo $active_tab == 'akan_datang' ? 'active' : ''; ?>">
            Akan Datang
        </a>
        <a href="?tab=selesai" class="tab <?php echo $active_tab == 'selesai' ? 'active' : ''; ?>">
            Selesai
        </a>
        <a href="?tab=dibatalkan" class="tab <?php echo $active_tab == 'dibatalkan' ? 'active' : ''; ?>">
            Dibatalkan
        </a>
    </div>

    <?php if ($bookings_result && mysqli_num_rows($bookings_result) > 0): ?>
        <?php while ($booking = mysqli_fetch_assoc($bookings_result)): 
            $tutor_initial = strtoupper(substr($booking['tutor_name'], 0, 1));
            $date = new DateTime($booking['booking_date']);
            $date_formatted = $date->format('l, d M Y');
            $time_formatted = date('H:i', strtotime($booking['booking_time']));
            $price_formatted = 'Rp ' . number_format($booking['price'], 0, ',', '.');
        ?>
            <div class="session-card <?php echo $booking['status']; ?>">
                <div class="session-header">
                    <div class="tutor-info">
                        <div class="tutor-avatar"><?php echo $tutor_initial; ?></div>
                        <div class="tutor-details">
                            <h3><?php echo htmlspecialchars($booking['tutor_name']); ?></h3>
                            <p><?php echo htmlspecialchars($booking['subject_name']); ?></p>
                            <p style="color: #FF6B35; font-weight: 600; margin-top: 5px;"><?php echo $price_formatted; ?></p>
                        </div>
                    </div>
                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                        <?php 
                        $status_text = [
                            'pending' => '🕐 Akan Datang',
                            'confirmed' => '✅ Selesai',
                            'completed' => '✅ Selesai',
                            'cancelled' => '❌ Dibatalkan'
                        ];
                        echo $status_text[$booking['status']] ?? ucfirst($booking['status']);
                        ?>
                    </span>
                </div>

                <div class="session-details">
                    <div class="detail-item">
                        <i class="bi bi-calendar-event"></i>
                        <div>
                            <small style="color: #666;">Tanggal</small>
                            <p style="margin: 0; font-weight: 600;"><?php echo $date_formatted; ?></p>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="bi bi-clock"></i>
                        <div>
                            <small style="color: #666;">Waktu</small>
                            <p style="margin: 0; font-weight: 600;"><?php echo $time_formatted; ?> WIB</p>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="bi bi-hourglass-split"></i>
                        <div>
                            <small style="color: #666;">Durasi</small>
                            <p style="margin: 0; font-weight: 600;"><?php echo $booking['duration']; ?> menit</p>
                        </div>
                    </div>
                    <?php if ($booking['status'] == 'confirmed' || $booking['status'] == 'pending'): ?>
                    <div class="detail-item">
                        <i class="bi bi-camera-video"></i>
                        <div>
                            <small style="color: #666;">Platform</small>
                            <p style="margin: 0; font-weight: 600;">Online (Zoom)</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($booking['notes']): ?>
                <div style="margin-top: 15px; padding: 12px; background: white; border-left: 3px solid #ff9329; border-radius: 4px;">
                    <small style="color: #666; font-weight: 600;">Catatan:</small>
                    <p style="margin: 5px 0 0 0;"><?php echo htmlspecialchars($booking['notes']); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($booking['status'] == 'completed'): ?>
                <div style="margin-top: 15px; text-align: right;">
                    <button onclick="openReviewModal(<?php echo $booking['id']; ?>)" class="btn" style="background: linear-gradient(135deg, #FF6B35, #F7931E); color: white; border: none; padding: 8px 20px; border-radius: 20px; cursor: pointer;">
                        <i class="bi bi-star"></i> Beri Review
                    </button>
                </div>
                <?php elseif ($booking['status'] == 'confirmed' || $booking['status'] == 'pending'): ?>
                <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: flex-end;">
                    <a href="https://wa.me/6281368410208?text=Halo,%20saya%20ingin%20bertanya%20tentang%20sesi%20belajar%20<?php echo urlencode($booking['subject_name']); ?>%20pada%20<?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?>%20jam%20<?php echo substr($booking['booking_time'], 0, 5); ?>" 
                       target="_blank" class="btn" style="background: #28A745; color: white; border: none; padding: 8px 20px; border-radius: 20px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="bi bi-whatsapp"></i> Chat Admin
                    </a>
                </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <h3>Belum Ada Sesi</h3>
            <p>Anda belum memiliki jadwal belajar. Mulai cari tutor dan booking sesi belajar!</p>
            <a href="../public/search_result.php" style="display: inline-block; margin-top: 20px; background: linear-gradient(135deg, #FF6B35, #F7931E); color: white; padding: 12px 30px; border-radius: 25px; text-decoration: none; font-weight: 600;">
                <i class="bi bi-search"></i> Cari Tutor
            </a>
        </div>
    <?php endif; ?>
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

function openReviewModal(bookingId) {
    document.getElementById('reviewBookingId').value = bookingId;
    document.getElementById('reviewModal').classList.add('active');
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.remove('active');
    document.getElementById('reviewForm').reset();
}
</script>

<!-- Review Modal -->
<div class="review-modal" id="reviewModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div class="review-modal-content" style="background: white; padding: 40px; border-radius: 20px; max-width: 500px; width: 90%; position: relative;">
        <button onclick="closeReviewModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 30px; cursor: pointer; color: #999;">&times;</button>
        <div style="margin-bottom: 30px;">
            <h2 style="color: #cc5500; margin: 0 0 10px 0;">Beri Rating & Review</h2>
            <p style="color: #666; margin: 0;">Bagikan pengalaman belajar Anda</p>
        </div>
        <form id="reviewForm" action="../../../backend/learner/submit_review.php" method="POST">
            <input type="hidden" name="booking_id" id="reviewBookingId">
            
            <div style="margin-bottom: 25px; text-align: center;">
                <div class="rating-input" style="display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px;">
                    <input type="radio" name="rating" value="5" id="rating5" required style="display: none;">
                    <label for="rating5" style="font-size: 40px; cursor: pointer; transition: all 0.3s;" onclick="selectRating(5)">⭐</label>
                    <input type="radio" name="rating" value="4" id="rating4" style="display: none;">
                    <label for="rating4" style="font-size: 40px; cursor: pointer; transition: all 0.3s;" onclick="selectRating(4)">⭐</label>
                    <input type="radio" name="rating" value="3" id="rating3" style="display: none;">
                    <label for="rating3" style="font-size: 40px; cursor: pointer; transition: all 0.3s;" onclick="selectRating(3)">⭐</label>
                    <input type="radio" name="rating" value="2" id="rating2" style="display: none;">
                    <label for="rating2" style="font-size: 40px; cursor: pointer; transition: all 0.3s;" onclick="selectRating(2)">⭐</label>
                    <input type="radio" name="rating" value="1" id="rating1" style="display: none;">
                    <label for="rating1" style="font-size: 40px; cursor: pointer; transition: all 0.3s;" onclick="selectRating(1)">⭐</label>
                </div>
            </div>
            
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Review</label>
                <textarea name="review_text" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px; font-family: inherit;" rows="5" 
                          placeholder="Tuliskan pengalaman belajar Anda dengan tutor ini..." required></textarea>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: flex-end;">
                <button type="button" onclick="closeReviewModal()" style="padding: 12px 30px; background: #f0f0f0; color: #333; border: none; border-radius: 25px; cursor: pointer; font-weight: 600;">Batal</button>
                <button type="submit" style="padding: 12px 30px; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); color: white; border: none; border-radius: 25px; cursor: pointer; font-weight: 600;">Kirim Review</button>
            </div>
        </form>
    </div>
</div>

<script>
function selectRating(rating) {
    document.getElementById('rating' + rating).checked = true;
    // Highlight selected stars
    for (let i = 1; i <= 5; i++) {
        const label = document.querySelector(`label[for="rating${i}"]`);
        if (i <= rating) {
            label.style.filter = 'none';
            label.style.opacity = '1';
        } else {
            label.style.filter = 'grayscale(100%)';
            label.style.opacity = '0.3';
        }
    }
}

// Show/hide modal
document.getElementById('reviewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReviewModal();
    }
});
</script>

<?php require_once '../../layouts/footer.php'; ?>
