<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'learner') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : (isset($_SESSION['email']) ? $_SESSION['email'] : '');

$siswa_query = "SELECT * FROM siswa WHERE email = '$user_email' LIMIT 1";
$siswa_result = mysqli_query($conn, $siswa_query);
$siswa_data = mysqli_fetch_assoc($siswa_result);

if (!$siswa_data) {
    header("Location: ../auth/login.php?error=no_student_data");
    exit();
}

$siswa_id = $siswa_data['id'];

// Query untuk riwayat booking yang sudah selesai atau dibatalkan
$riwayat_query = "SELECT 
    b.*,
    t.nama_lengkap as tutor_name,
    t.keahlian,
    t.foto_profil,
    s.subject_name,
    s.price
FROM bookings b
INNER JOIN tutor t ON b.tutor_id = t.id
INNER JOIN subjects s ON b.subject_id = s.id
WHERE b.learner_id = '$siswa_id' AND b.status IN ('completed', 'cancelled')
ORDER BY b.booking_date DESC, b.created_at DESC";

$riwayat_result = mysqli_query($conn, $riwayat_query);

// Count statistik
$count_completed_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE learner_id = '$siswa_id' AND status = 'completed'");
$count_completed = $count_completed_result ? mysqli_fetch_assoc($count_completed_result)['total'] : 0;

$count_cancelled_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE learner_id = '$siswa_id' AND status = 'cancelled'");
$count_cancelled = $count_cancelled_result ? mysqli_fetch_assoc($count_cancelled_result)['total'] : 0;

$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : (isset($_SESSION['email']) ? $_SESSION['email'] : '');

$siswa_query = "SELECT * FROM siswa WHERE email = '$user_email' LIMIT 1";
$siswa_result = mysqli_query($conn, $siswa_query);
$siswa_data = mysqli_fetch_assoc($siswa_result);

if (!$siswa_data) {
    header("Location: ../auth/login.php?error=no_student_data");
    exit();
}

$siswa_id = $siswa_data['id'];

// Query untuk riwayat booking yang sudah selesai atau dibatalkan
$riwayat_query = "SELECT 
    b.*,
    t.nama_lengkap as tutor_name,
    t.keahlian,
    t.foto_profil,
    s.subject_name,
    s.price
FROM bookings b
INNER JOIN tutor t ON b.tutor_id = t.id
INNER JOIN subjects s ON b.subject_id = s.id
WHERE b.learner_id = '$siswa_id' AND b.status IN ('completed', 'cancelled')
ORDER BY b.booking_date DESC, b.created_at DESC";

$riwayat_result = mysqli_query($conn, $riwayat_query);

// Count statistik
$count_completed_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE learner_id = '$siswa_id' AND status = 'completed'");
$count_completed = $count_completed_result ? mysqli_fetch_assoc($count_completed_result)['total'] : 0;

$count_cancelled_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE learner_id = '$siswa_id' AND status = 'cancelled'");
$count_cancelled = $count_cancelled_result ? mysqli_fetch_assoc($count_cancelled_result)['total'] : 0;


$logoPath = "../../../assets/img/logo.png";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Booking - PeerLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<!-- NAVBAR LEARNER -->
<nav class="sb-navbar">
    <div class="sb-nav-container">
        <div class="sb-brand">
            <img src="<?php echo $logoPath; ?>" alt="PeerLearn Logo" class="logo">
            <span>PeerLearn</span>
        </div>
        <ul class="sb-menu">
            <li><a href="dashboard_siswa.php">Beranda</a></li>
            <li><a href="../public/search_result.php">Cari Tutor</a></li>
            <li><a href="sesi_saya.php">Sesi Saya</a></li>
            <li><a href="riwayat.php" class="active">Riwayat Booking</a></li>
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
.sb-navbar { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
.sb-nav-container { max-width: 1200px; margin: 0 auto; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
.sb-brand { display: flex; align-items: center; gap: 10px; font-size: 24px; font-weight: 700; color: #cc5500; }
.sb-brand .logo { height: 40px; width: auto; }
.sb-menu { list-style: none; display: flex; gap: 30px; margin: 0; padding: 0; }
.sb-menu a { text-decoration: none; color: #333; font-weight: 500; transition: color 0.3s; padding: 8px 0; border-bottom: 2px solid transparent; }
.sb-menu a:hover, .sb-menu a.active { color: #FF6B35; border-bottom-color: #FF6B35; }
.sb-daftar { padding: 10px 20px; border-radius: 25px; font-weight: 600; color: white; }
</style>

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

<div style="padding-top: 20px;">

<style>
.riwayat-container {
    padding: 30px 0;
}

.riwayat-header {
    margin-bottom: 30px;
}

.riwayat-header h1 {
    color: var(--color-text-dark);
    margin-bottom: 10px;
}

.filter-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 10px 20px;
    background: white;
    border: 2px solid var(--color-border);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 600;
    color: var(--color-text-dark);
}

.filter-tab:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
}

.filter-tab.active {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
}

.bookings-list {
    display: grid;
    gap: 20px;
}

.booking-card {
    background: white;
    padding: 25px;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    border-left: 4px solid var(--color-primary);
}

.booking-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
}

.booking-tutor-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.tutor-avatar-small {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    font-weight: bold;
}

.booking-details {
    margin-bottom: 15px;
}

.booking-details p {
    margin: 5px 0;
    color: var(--color-text-light);
    font-size: 14px;
}

.booking-details strong {
    color: var(--color-text-dark);
}

.booking-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-confirmed {
    background: #d1ecf1;
    color: #0c5460;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.btn-review {
    padding: 8px 16px;
    background: var(--color-secondary);
    color: var(--color-text-dark);
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-review:hover {
    background: #ffb300;
    transform: translateY(-2px);
}

.loading {
    text-align: center;
    padding: 40px;
    color: var(--color-text-light);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--color-text-light);
}

.empty-state h3 {
    margin-bottom: 10px;
    color: var(--color-text-dark);
}

/* Review Modal */
.review-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.review-modal.active {
    display: flex;
}

.review-modal-content {
    background: white;
    padding: 30px;
    border-radius: var(--border-radius);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.review-modal-header {
    margin-bottom: 20px;
}

.review-modal-header h2 {
    margin-bottom: 5px;
}

.rating-input {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    justify-content: center;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-input label {
    font-size: 30px;
    cursor: pointer;
    color: #ddd;
    transition: color 0.2s;
}

.rating-input label:hover,
.rating-input input[type="radio"]:checked ~ label,
.rating-input label:hover ~ label {
    color: #ffc107;
}

.rating-input input[type="radio"]:checked ~ label {
    color: #ffc107;
}

.close-modal {
    float: right;
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--color-text-light);
}
</style>

<div class="riwayat-container">
    <div class="container">
        <div class="riwayat-header">
            <h1>📚 Riwayat Booking</h1>
            <p>Lihat semua sesi belajar yang telah selesai atau dibatalkan</p>
            <div style="margin-top: 15px; display: flex; gap: 20px;">
                <span style="color: #10b981; font-weight: 600;">✅ Selesai: <?php echo $count_completed; ?></span>
                <span style="color: #ef4444; font-weight: 600;">❌ Dibatalkan: <?php echo $count_cancelled; ?></span>
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'review_success'): ?>
            <div class="alert alert-success">
                Review berhasil dikirim! Terima kasih atas feedback Anda.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php
                $error = $_GET['error'];
                if ($error == 'invalid_rating') echo "Rating harus antara 1-5.";
                elseif ($error == 'empty_review') echo "Harap isi review terlebih dahulu.";
                elseif ($error == 'invalid_booking') echo "Booking tidak valid atau tidak ditemukan.";
                elseif ($error == 'review_exists') echo "Anda sudah memberikan review untuk booking ini.";
                elseif ($error == 'db_error') echo "Terjadi kesalahan database.";
                else echo "Terjadi kesalahan. Silakan coba lagi.";
                ?>
            </div>
        <?php endif; ?>

        <div class="filter-tabs">
            <div class="filter-tab active" data-filter="all">Semua Riwayat</div>
            <div class="filter-tab" data-filter="completed">Selesai</div>
            <div class="filter-tab" data-filter="cancelled">Dibatalkan</div>
        </div>

        <div id="bookingsList" class="bookings-list">
            <?php if ($riwayat_result && mysqli_num_rows($riwayat_result) > 0): ?>
                <?php while ($booking = mysqli_fetch_assoc($riwayat_result)): 
                    $tutor_initial = strtoupper(substr($booking['tutor_name'], 0, 1));
                    $date = new DateTime($booking['booking_date']);
                    $date_formatted = $date->format('d M Y');
                    $time_formatted = date('H:i', strtotime($booking['booking_time']));
                    $price_formatted = 'Rp ' . number_format($booking['price'], 0, ',', '.');
                    
                    $status_class = $booking['status'];
                    $status_label = $booking['status'] == 'completed' ? 'Selesai' : 'Dibatalkan';
                    $status_color = $booking['status'] == 'completed' ? '#10b981' : '#ef4444';
                ?>
                <div class="booking-card" data-status="<?php echo $booking['status']; ?>">
                    <div class="booking-card-header">
                        <div class="booking-tutor-info">
                            <div class="tutor-avatar-small"><?php echo $tutor_initial; ?></div>
                            <div>
                                <h3 style="margin: 0; color: #cc5500;"><?php echo htmlspecialchars($booking['tutor_name']); ?></h3>
                                <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;"><?php echo htmlspecialchars($booking['subject_name']); ?></p>
                            </div>
                        </div>
                        <span style="background: <?php echo $status_color; ?>; color: white; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                            <?php echo $status_label; ?>
                        </span>
                    </div>
                    <div class="booking-details" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                        <div>
                            <p style="margin: 0; color: #666; font-size: 13px;">📅 Tanggal</p>
                            <p style="margin: 5px 0 0 0; font-weight: 600; color: #333;"><?php echo $date_formatted; ?></p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #666; font-size: 13px;">🕐 Waktu</p>
                            <p style="margin: 5px 0 0 0; font-weight: 600; color: #333;"><?php echo $time_formatted; ?> WIB</p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #666; font-size: 13px;">⏱️ Durasi</p>
                            <p style="margin: 5px 0 0 0; font-weight: 600; color: #333;"><?php echo $booking['duration']; ?> menit</p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #666; font-size: 13px;">💰 Harga</p>
                            <p style="margin: 5px 0 0 0; font-weight: 600; color: #FF6B35;"><?php echo $price_formatted; ?></p>
                        </div>
                    </div>
                    <?php if ($booking['notes']): ?>
                    <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <p style="margin: 0; color: #666; font-size: 13px; margin-bottom: 5px;">📝 Catatan:</p>
                        <p style="margin: 0; color: #333;"><?php echo htmlspecialchars($booking['notes']); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($booking['status'] == 'completed'): ?>
                    <div style="margin-top: 15px; display: flex; gap: 10px;">
                        <button class="btn-review" onclick="openReviewModal(<?php echo $booking['id']; ?>)">
                            ⭐ Beri Review
                        </button>
                        <a href="sesi_saya.php" style="padding: 10px 20px; background: #cc5500; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">
                            Booking Lagi
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 64px; margin-bottom: 20px;">📚</div>
                    <h3>Belum ada riwayat booking</h3>
                    <p>Riwayat booking yang sudah selesai atau dibatalkan akan muncul di sini</p>
                    <p style="margin-top: 20px;">
                        <a href="../public/search_result.php" style="color: #FF6B35; font-weight: 600; text-decoration: none;">
                            Cari Tutor Sekarang →
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

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
                    <label for="rating5" style="font-size: 40px; cursor: pointer; transition: all 0.3s; filter: grayscale(100%); opacity: 0.3;" onclick="selectRating(5)">⭐</label>
                    <input type="radio" name="rating" value="4" id="rating4" style="display: none;">
                    <label for="rating4" style="font-size: 40px; cursor: pointer; transition: all 0.3s; filter: grayscale(100%); opacity: 0.3;" onclick="selectRating(4)">⭐</label>
                    <input type="radio" name="rating" value="3" id="rating3" style="display: none;">
                    <label for="rating3" style="font-size: 40px; cursor: pointer; transition: all 0.3s; filter: grayscale(100%); opacity: 0.3;" onclick="selectRating(3)">⭐</label>
                    <input type="radio" name="rating" value="2" id="rating2" style="display: none;">
                    <label for="rating2" style="font-size: 40px; cursor: pointer; transition: all 0.3s; filter: grayscale(100%); opacity: 0.3;" onclick="selectRating(2)">⭐</label>
                    <input type="radio" name="rating" value="1" id="rating1" style="display: none;">
                    <label for="rating1" style="font-size: 40px; cursor: pointer; transition: all 0.3s; filter: grayscale(100%); opacity: 0.3;" onclick="selectRating(1)">⭐</label>
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
function openReviewModal(bookingId) {
    document.getElementById('reviewBookingId').value = bookingId;
    const modal = document.getElementById('reviewModal');
    modal.style.display = 'flex';
}

function closeReviewModal() {
    const modal = document.getElementById('reviewModal');
    modal.style.display = 'none';
    document.getElementById('reviewForm').reset();
    // Reset star ratings
    for (let i = 1; i <= 5; i++) {
        const label = document.querySelector(`label[for="rating${i}"]`);
        label.style.filter = 'grayscale(100%)';
        label.style.opacity = '0.3';
    }
}

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

// Filter bookings client-side
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const cards = document.querySelectorAll('.booking-card');
        
        cards.forEach(card => {
            if (filter === 'all' || card.dataset.status === filter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

document.getElementById('reviewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReviewModal();
    }
});
</script>

<?php require_once '../../layouts/footer.php'; ?>

