<?php 
// Start session first before including header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$assetPath = "../../assets/";
include '../../layouts/header.php';

// Koneksi database
require_once '../../../config/database.php';

// Get tutor ID from URL - support both 'tutor_id' and 'id' parameters for backward compatibility
$tutor_id = 0;
if (isset($_GET['tutor_id'])) {
    $tutor_id = intval($_GET['tutor_id']);
} elseif (isset($_GET['id'])) {
    $tutor_id = intval($_GET['id']);
}

// Query tutor data
$tutorData = null;
if ($tutor_id > 0) {
    $query = "SELECT t.*, u.email 
              FROM tutor t 
              LEFT JOIN users u ON t.email = u.email
              WHERE t.id = ? AND t.status = 'Aktif'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $tutor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tutorData = mysqli_fetch_assoc($result);
}

// If no tutor_id provided or tutor not found, try to find by name (for old links)
if (!$tutorData && isset($_GET['nama'])) {
    $nama = $_GET['nama'];
    $query = "SELECT t.*, u.email 
              FROM tutor t 
              LEFT JOIN users u ON t.email = u.email
              WHERE t.nama_lengkap LIKE ? AND t.status = 'Aktif'
              LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    $searchNama = "%" . $nama . "%";
    mysqli_stmt_bind_param($stmt, "s", $searchNama);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tutorData = mysqli_fetch_assoc($result);
    if ($tutorData) {
        $tutor_id = $tutorData['id'];
    }
}

// Jika tutor masih tidak ditemukan, tampilkan pesan error
if (!$tutorData) {
    echo '<div style="max-width: 800px; margin: 100px auto; text-align: center; padding: 40px;">';
    echo '<h2 style="color: #FF6B35;">Tutor Tidak Ditemukan</h2>';
    echo '<p style="color: #666; margin: 20px 0;">Maaf, data tutor yang Anda cari tidak tersedia atau sudah tidak aktif.</p>';
    echo '<a href="search_result.php" style="display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Cari Tutor Lain</a>';
    echo '</div>';
    include '../../layouts/footer.php';
    exit;
}

// Get subjects taught by tutor from tutor_mapel
$subjectsQuery = "SELECT DISTINCT nama_mapel, jenjang FROM tutor_mapel WHERE tutor_id = ? ORDER BY nama_mapel ASC";
$stmt = mysqli_prepare($conn, $subjectsQuery);
mysqli_stmt_bind_param($stmt, "i", $tutor_id);
mysqli_stmt_execute($stmt);
$subjectsResult = mysqli_stmt_get_result($stmt);
$subjects = [];
while ($row = mysqli_fetch_assoc($subjectsResult)) {
    $subjects[] = $row;
}

// Get reviews (dummy for now since no review table properly set up)
$reviews = [
    ['nama' => 'Nadia Putri', 'rating' => 5, 'text' => 'Tutor sangat sabar dan metode pengajarannya mudah dipahami!'],
    ['nama' => 'Raka Firmansyah', 'rating' => 5, 'text' => 'Materi yang diajarkan sangat terstruktur dan to the point.'],
    ['nama' => 'Sinta Dewi', 'rating' => 4, 'text' => 'Pengalaman belajar yang menyenangkan, tutor sangat profesional.']
];

// Generate initial
$initial = strtoupper(substr($tutorData['nama_lengkap'], 0, 2));

// Parse pendidikan (assuming format: "Institusi | Jurusan | Tahun")
$pendidikanParts = explode('|', $tutorData['pendidikan'] ?? 'Institut Teknologi Sumatera | S1 Fisika | 2018 - 2022');
$institusi = trim($pendidikanParts[0] ?? 'Institut Teknologi');
$jurusan = trim($pendidikanParts[1] ?? 'S1 Fisika');
$tahunPendidikan = trim($pendidikanParts[2] ?? '2018 - 2022');

// Calculate IPK from pendidikan or default
$ipk = '3.85';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'];
$userRole = $_SESSION['user_role'] ?? '';
?>

<style>
    body {
        background: #f5f7fa;
    }

    .detail-container {
        max-width: 1400px;
        margin: 40px auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 30px;
    }

    /* Sidebar Tutor Card */
    .tutor-sidebar {
        background: white;
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        position: sticky;
        top: 100px;
        height: fit-content;
    }

    .tutor-avatar {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
        border-radius: 50%;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: 800;
        color: white;
        box-shadow: 0 10px 25px rgba(255,107,53,0.3);
    }

    .tutor-name {
        font-size: 24px;
        font-weight: 700;
        color: #1a5f7a;
        text-align: center;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .verified-badge {
        color: #28a745;
        font-size: 20px;
    }

    .tutor-specialty {
        text-align: center;
        color: #666;
        font-size: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .tutor-stats {
        display: flex;
        justify-content: space-around;
        margin-bottom: 25px;
        padding: 20px 0;
        border-top: 2px solid #f0f0f0;
        border-bottom: 2px solid #f0f0f0;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #FF6B35;
        display: block;
    }

    .stat-label {
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 25px;
    }

    .btn-request {
        padding: 15px;
        background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .btn-request:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255,107,53,0.4);
        color: white;
    }

    .btn-whatsapp {
        padding: 15px;
        background: #25D366;
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .btn-whatsapp:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37,211,102,0.4);
        color: white;
    }

    .info-section {
        margin-bottom: 25px;
    }

    .info-title {
        font-weight: 700;
        color: #1a5f7a;
        margin-bottom: 12px;
        font-size: 16px;
    }

    .info-item {
        display: flex;
        align-items: start;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 14px;
        color: #666;
    }

    .info-item i {
        color: #FF6B35;
        margin-top: 2px;
    }

    .price-range {
        background: #fff5f0;
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid #FF6B35;
    }

    .price-range .label {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }

    .price-range .amount {
        font-size: 20px;
        font-weight: 700;
        color: #FF6B35;
    }

    .price-note {
        font-size: 12px;
        color: #999;
        margin-top: 8px;
    }

    /* Main Content Area */
    .tutor-content {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .stats-header {
        display: flex;
        justify-content: space-around;
        margin-bottom: 40px;
        padding-bottom: 30px;
        border-bottom: 2px solid #f0f0f0;
    }

    .stat-box {
        text-align: center;
    }

    .stat-box .number {
        font-size: 42px;
        font-weight: 800;
        background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-box .label {
        font-size: 14px;
        color: #999;
        margin-top: 8px;
    }

    .content-section {
        margin-bottom: 35px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #1a5f7a;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #FF6B35;
    }

    .about-text {
        line-height: 1.8;
        color: #555;
        font-size: 15px;
    }

    .subject-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .subject-badge {
        padding: 10px 20px;
        background: #fff5f0;
        color: #FF6B35;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: 2px solid #ffe0d0;
    }

    .education-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 15px;
        border-left: 4px solid #FF6B35;
    }

    .education-title {
        font-size: 18px;
        font-weight: 700;
        color: #1a5f7a;
        margin-bottom: 5px;
    }

    .education-details {
        color: #666;
        font-size: 14px;
    }

    .education-year {
        color: #999;
        font-size: 13px;
        margin-top: 5px;
    }

    .experience-list {
        list-style: none;
        padding: 0;
    }

    .experience-list li {
        padding: 12px 0;
        padding-left: 30px;
        position: relative;
        color: #555;
        line-height: 1.6;
    }

    .experience-list li:before {
        content: "•";
        position: absolute;
        left: 10px;
        color: #FF6B35;
        font-size: 20px;
        font-weight: 700;
    }

    @media (max-width: 1024px) {
        .detail-container {
            grid-template-columns: 1fr;
        }

        .tutor-sidebar {
            position: relative;
            top: 0;
        }
    }
</style>

<div class="detail-container">
    <!-- Sidebar -->
    <div class="tutor-sidebar">
        <div class="tutor-avatar"><?php echo $initial; ?></div>
        
        <div class="tutor-name">
            <?php echo htmlspecialchars($tutorData['nama_lengkap'] ?? 'Tutor'); ?>
            <i class="bi bi-patch-check-fill verified-badge"></i>
        </div>
        
        <div class="tutor-specialty">
            <i class="bi bi-briefcase"></i>
            <?php echo htmlspecialchars($tutorData['keahlian'] ?? 'Fisika Itera'); ?>
        </div>

        <div class="tutor-stats">
            <div class="stat-item">
                <span class="stat-value"><?php echo number_format($tutorData['rating'], 1); ?></span>
                <span class="stat-label">Rating</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?php echo count($reviews); ?></span>
                <span class="stat-label">Review</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?php echo $tutorData['pengalaman'] ?? '3'; ?></span>
                <span class="stat-label">Tahun</span>
            </div>
        </div>

        <div class="action-buttons">
            <?php if ($isLoggedIn && $userRole === 'learner'): ?>
                <a href="../learner/booking.php?tutor_id=<?php echo $tutor_id; ?>" class="btn-request">
                    <i class="bi bi-send-fill"></i> Ajukan Permintaan
                </a>
            <?php else: ?>
                <a href="../auth/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn-request">
                    <i class="bi bi-send-fill"></i> Ajukan Permintaan
                </a>
            <?php endif; ?>
            
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $tutorData['no_telp'] ?? '6281234567890'); ?>?text=Halo%20saya%20tertarik%20dengan%20les%20Anda" 
               class="btn-whatsapp" target="_blank">
                <i class="bi bi-whatsapp"></i> Chat WhatsApp
            </a>
        </div>

        <div class="info-section">
            <div class="info-title">Informasi Kontak</div>
            <div class="info-item">
                <i class="bi bi-envelope-fill"></i>
                <span><?php echo htmlspecialchars($tutorData['email'] ?? 'email@example.com'); ?></span>
            </div>
            <div class="info-item">
                <i class="bi bi-telephone-fill"></i>
                <span><?php echo htmlspecialchars($tutorData['no_telp'] ?? '+62 812-3456-7890'); ?></span>
            </div>
            <div class="info-item">
                <i class="bi bi-geo-alt-fill"></i>
                <span><?php echo htmlspecialchars($tutorData['alamat'] ?? 'Bandar Lampung'); ?></span>
            </div>
            <div class="info-item">
                <i class="bi bi-clock-fill"></i>
                <span>Hubungi langsung untuk diskusi jadwal & biaya</span>
            </div>
        </div>

        <div class="info-section">
            <div class="info-title">Estimasi Biaya</div>
            <div class="price-range">
                <div class="label">Per Sesi (60-90 menit)</div>
                <div class="amount">Rp <?php 
                    $minPrice = !empty($subjects) ? min(array_column($subjects, 'price')) : 50000;
                    $maxPrice = !empty($subjects) ? max(array_column($subjects, 'price')) : 100000;
                    echo number_format($minPrice, 0, ',', '.');
                    if ($minPrice != $maxPrice) {
                        echo ' - ' . number_format($maxPrice, 0, ',', '.');
                    }
                ?></div>
                <div class="price-note">
                    <i class="bi bi-info-circle"></i> Biaya dapat dinegosiasikan langsung dengan tutor
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="tutor-content">
        <!-- Stats Header -->
        <div class="stats-header">
            <div class="stat-box">
                <div class="number">150+</div>
                <div class="label">Siswa Diajar</div>
            </div>
            <div class="stat-box">
                <div class="number">95%</div>
                <div class="label">Tingkat Keberhasilan</div>
            </div>
            <div class="stat-box">
                <div class="number">500+</div>
                <div class="label">Jam Mengajar</div>
            </div>
        </div>

        <!-- Tentang Saya -->
        <div class="content-section">
            <div class="section-title">
                <i class="bi bi-person-fill"></i> Tentang Saya
            </div>
            <p class="about-text">
                <?php 
                $about = $tutorData['bio'] ?? "Saya adalah lulusan {$institusi} dengan pengalaman mengajar lebih dari 3 tahun. Saya berspesialisasi dalam mengajar {$tutorData['keahlian']} untuk tingkat SMA dengan fokus pada pemahaman konsep yang mendalam dan penerapan praktis. Metode pengajaran saya disesuaikan dengan kebutuhan dan gaya belajar setiap siswa.";
                
                if (strlen($about) < 100) {
                    $about .= "\n\nSaya telah membantu lebih dari 150 siswa meningkatkan nilai mereka dan berhasil masuk ke universitas impian. Tingkat keberhasilan siswa saya masuk PTN 95%. Spesialisasi saya meliputi persiapan UTBK dan ujian sekolah.";
                }
                
                echo nl2br(htmlspecialchars($about));
                ?>
            </p>
        </div>

        <!-- Mata Pelajaran -->
        <div class="content-section">
            <div class="section-title">
                <i class="bi bi-journal-text"></i> Mata Pelajaran
            </div>
            <div class="subject-badges">
                <?php 
                if (!empty($subjects)) {
                    foreach ($subjects as $subject) {
                        echo '<span class="subject-badge">' . htmlspecialchars($subject['nama_mapel']) . ' <small>(' . htmlspecialchars($subject['jenjang']) . ')</small></span>';
                    }
                } else {
                    $defaultSubjects = ['Fisika', 'Matematika', 'Kimia', 'Biologi', 'Bahasa Inggris'];
                    foreach ($defaultSubjects as $subj) {
                        echo '<span class="subject-badge">' . $subj . '</span>';
                    }
                }
                ?>
            </div>
        </div>

        <!-- Pendidikan -->
        <div class="content-section">
            <div class="section-title">
                <i class="bi bi-mortarboard-fill"></i> Pendidikan
            </div>
            <div class="education-card">
                <div class="education-title">S1 Fisika</div>
                <div class="education-details"><?php echo htmlspecialchars($institusi); ?></div>
                <div class="education-details"><?php echo htmlspecialchars($jurusan); ?></div>
                <div class="education-year"><?php echo htmlspecialchars($tahunPendidikan); ?> | IPK: <?php echo $ipk; ?></div>
            </div>
            
            <?php if (stripos($tutorData['pendidikan'] ?? '', 'SMA') !== false): ?>
            <div class="education-card">
                <div class="education-title">SMA Negeri 1 Bandar Lampung</div>
                <div class="education-details">Jurusan IPA</div>
                <div class="education-year">Lulus tahun 2018</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pengalaman -->
        <div class="content-section">
            <div class="section-title">
                <i class="bi bi-briefcase-fill"></i> Pengalaman
            </div>
            <div class="education-card">
                <div class="education-title">Tutor Privat Fisika & Matematika</div>
                <div class="education-details">PeerLearn | 2022 - Sekarang</div>
                <ul class="experience-list">
                    <li>Mengajar lebih dari 150 siswa tingkat SMA</li>
                    <li>Tingkat keberhasilan siswa masuk PTN 95%</li>
                    <li>Spesialisasi persiapan UTBK dan ujian sekolah</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../../layouts/footer.php'; ?>
