<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'learner') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $_SESSION['email'];

// Get siswa data
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

$learner_id = $siswa_data['id'];

// Get completed bookings without review
$bookings_query = "SELECT b.*, t.nama_lengkap as tutor_nama, s.subject_name,
                   (SELECT COUNT(*) FROM reviews WHERE booking_id = b.id) as has_review
                   FROM bookings b
                   JOIN tutor t ON b.tutor_id = t.id
                   JOIN subjects s ON b.subject_id = s.id
                   WHERE b.learner_id = ? AND b.status = 'completed'
                   ORDER BY b.booking_date DESC";
$stmt = mysqli_prepare($conn, $bookings_query);
mysqli_stmt_bind_param($stmt, "i", $learner_id);
mysqli_stmt_execute($stmt);
$bookings_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// Get my reviews
$reviews_query = "SELECT r.*, t.nama_lengkap as tutor_nama, s.subject_name, b.booking_date
                  FROM reviews r
                  JOIN tutor t ON r.tutor_id = t.id
                  JOIN bookings b ON r.booking_id = b.id
                  JOIN subjects s ON b.subject_id = s.id
                  WHERE r.learner_id = ?
                  ORDER BY r.created_at DESC";
$stmt = mysqli_prepare($conn, $reviews_query);
mysqli_stmt_bind_param($stmt, "i", $learner_id);
mysqli_stmt_execute($stmt);
$reviews_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$success_message = '';
$error_message = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'review_success') {
        $success_message = 'Testimoni berhasil ditambahkan!';
    }
}

if (isset($_GET['error'])) {
    if ($_GET['error'] == 'invalid_rating') {
        $error_message = 'Rating harus antara 1-5!';
    } elseif ($_GET['error'] == 'empty_review') {
        $error_message = 'Testimoni tidak boleh kosong!';
    } elseif ($_GET['error'] == 'invalid_booking') {
        $error_message = 'Booking tidak valid atau belum selesai!';
    } elseif ($_GET['error'] == 'review_exists') {
        $error_message = 'Anda sudah memberikan testimoni untuk sesi ini!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimoni - PeerLearn</title>
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

        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 30px;
        }

        .page-header {
            background: linear-gradient(135deg, #cc5500 0%, #ff9329 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 30px;
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

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .tab-btn {
            padding: 12px 30px;
            border: none;
            background: white;
            color: #666;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .booking-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .tutor-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .tutor-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
        }

        .rating-stars {
            display: flex;
            gap: 5px;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .star {
            color: #ddd;
            cursor: pointer;
            transition: all 0.2s;
        }

        .star.active,
        .star:hover {
            color: #ffd700;
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

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #ff9329;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .review-item {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .review-rating {
            color: #ffd700;
            font-size: 18px;
        }

        .review-date {
            color: #999;
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 80px;
            margin-bottom: 20px;
            color: #ddd;
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
                    <a href="testimoni.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
                        <i class="bi bi-star"></i> Testimoni
                    </a>
                    <a href="../../../backend/auth/logout.php" style="display: block; padding: 12px 16px; color: #dc3545; text-decoration: none;">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <h1 style="margin: 0; font-size: 36px;"><i class="bi bi-star-fill"></i> Testimoni & Ulasan</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Bagikan pengalaman belajar Anda dengan tutor</p>
    </div>

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

    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('beri-testimoni')">
            <i class="bi bi-pencil-square"></i> Beri Testimoni
        </button>
        <button class="tab-btn" onclick="switchTab('testimoni-saya')">
            <i class="bi bi-chat-left-text"></i> Testimoni Saya
        </button>
    </div>

    <!-- Tab: Beri Testimoni -->
    <div id="beri-testimoni" class="tab-content active">
        <?php if (mysqli_num_rows($bookings_result) > 0): ?>
            <?php while($booking = mysqli_fetch_assoc($bookings_result)): ?>
                <?php if ($booking['has_review'] == 0): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="tutor-info">
                            <div class="tutor-avatar">
                                <?php echo strtoupper(substr($booking['tutor_nama'], 0, 1)); ?>
                            </div>
                            <div>
                                <h3 style="margin: 0; color: #333;"><?php echo htmlspecialchars($booking['tutor_nama']); ?></h3>
                                <p style="margin: 5px 0 0 0; color: #666;">
                                    <?php echo htmlspecialchars($booking['subject_name']); ?> • 
                                    <?php echo date('d M Y', strtotime($booking['booking_date'])); ?>
                                </p>
                            </div>
                        </div>
                        <span style="background: #28a745; color: white; padding: 6px 15px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            <i class="bi bi-check-circle"></i> Selesai
                        </span>
                    </div>

                    <form action="../../../backend/learner/submit_review.php" method="POST" onsubmit="return validateReview(this)">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Rating</label>
                            <div class="rating-stars" data-rating="0">
                                <span class="star" data-value="1">★</span>
                                <span class="star" data-value="2">★</span>
                                <span class="star" data-value="3">★</span>
                                <span class="star" data-value="4">★</span>
                                <span class="star" data-value="5">★</span>
                            </div>
                            <input type="hidden" name="rating" value="0" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Testimoni Anda</label>
                            <textarea name="review_text" class="form-input" rows="5" 
                                      placeholder="Ceritakan pengalaman belajar Anda dengan tutor ini..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Kirim Testimoni
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h3>Belum Ada Sesi yang Selesai</h3>
                <p>Selesaikan sesi belajar Anda untuk memberikan testimoni</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tab: Testimoni Saya -->
    <div id="testimoni-saya" class="tab-content">
        <?php if (mysqli_num_rows($reviews_result) > 0): ?>
            <?php while($review = mysqli_fetch_assoc($reviews_result)): ?>
            <div class="review-item">
                <div class="review-header">
                    <div>
                        <h4 style="margin: 0; color: #333;">
                            <i class="bi bi-person-circle"></i> 
                            <?php echo htmlspecialchars($review['tutor_nama']); ?>
                        </h4>
                        <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">
                            <?php echo htmlspecialchars($review['subject_name']); ?> • 
                            <?php echo date('d M Y', strtotime($review['booking_date'])); ?>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <div class="review-rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php if($i <= $review['rating']): ?>
                                    <i class="bi bi-star-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></div>
                    </div>
                </div>
                <p style="margin: 15px 0 0 0; color: #555; line-height: 1.6;">
                    "<?php echo htmlspecialchars($review['review_text']); ?>"
                </p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-chat-left-text"></i>
                <h3>Belum Ada Testimoni</h3>
                <p>Anda belum memberikan testimoni untuk tutor manapun</p>
            </div>
        <?php endif; ?>
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

function switchTab(tabId) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabId).classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

// Rating stars functionality
document.querySelectorAll('.rating-stars').forEach(ratingContainer => {
    const stars = ratingContainer.querySelectorAll('.star');
    const input = ratingContainer.parentElement.querySelector('input[name="rating"]');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            input.value = value;
            
            stars.forEach(s => {
                if (s.getAttribute('data-value') <= value) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
        
        star.addEventListener('mouseenter', function() {
            const value = this.getAttribute('data-value');
            stars.forEach(s => {
                if (s.getAttribute('data-value') <= value) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
    });
    
    ratingContainer.addEventListener('mouseleave', function() {
        const currentValue = input.value;
        stars.forEach(s => {
            if (s.getAttribute('data-value') <= currentValue) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    });
});

function validateReview(form) {
    const rating = form.querySelector('input[name="rating"]').value;
    const reviewText = form.querySelector('textarea[name="review_text"]').value.trim();
    
    if (rating == 0) {
        alert('Silakan pilih rating bintang!');
        return false;
    }
    
    if (reviewText == '') {
        alert('Silakan tulis testimoni Anda!');
        return false;
    }
    
    return true;
}
</script>

<?php require_once '../../layouts/footer.php'; ?>
