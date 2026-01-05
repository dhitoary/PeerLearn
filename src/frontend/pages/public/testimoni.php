<?php
$assetPath = "../../assets/";
include '../../layouts/header.php';

// Koneksi database
require_once '../../../config/database.php';

// Get testimoni dari database dengan prepared statement
$query = "SELECT r.*, s.nama_lengkap as siswa_nama, s.jenjang, s.kelas, 
          t.nama_lengkap as tutor_nama, sub.subject_name
          FROM reviews r
          JOIN siswa s ON r.learner_id = s.id
          JOIN tutor t ON r.tutor_id = t.id
          JOIN bookings b ON r.booking_id = b.id
          JOIN subjects sub ON b.subject_id = sub.id
          WHERE r.rating >= 4
          ORDER BY r.created_at DESC
          LIMIT 50";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$testimoniData = [];
$gradients = [
    'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
    'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
    'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
    'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
    'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)',
    'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
    'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'
];

$counter = 0;
while($row = mysqli_fetch_assoc($result)) {
    $inisial = strtoupper(substr($row['siswa_nama'], 0, 1));
    if (strpos($row['siswa_nama'], ' ') !== false) {
        $namaParts = explode(' ', $row['siswa_nama']);
        $inisial = strtoupper(substr($namaParts[0], 0, 1) . substr($namaParts[1], 0, 1));
    }
    
    $jenjangInfo = $row['jenjang'];
    if ($row['kelas']) {
        $jenjangInfo .= ' Kelas ' . $row['kelas'];
    }
    
    $testimoniData[] = [
        'nama' => $row['siswa_nama'],
        'inisial' => $inisial,
        'jenjang' => $jenjangInfo,
        'mapel' => $row['subject_name'],
        'rating' => $row['rating'],
        'testimoni' => $row['review_text'],
        'gradient' => $gradients[$counter % count($gradients)]
    ];
    $counter++;
}

mysqli_stmt_close($stmt);

// Jika tidak ada testimoni dari database, gunakan dummy data
if (empty($testimoniData)) {
    $testimoniData = [
        [
            'nama' => 'Alya Natasya',
            'inisial' => 'AN',
            'jenjang' => 'Siswa SMA Kelas 12',
            'mapel' => 'Matematika',
            'rating' => 5,
            'testimoni' => 'Tutor Matematika yang mengajar saya sangat sabar dan detail dalam menjelaskan. Nilai saya meningkat drastis dari 70 menjadi 95! Terima kasih PeerLearn.',
            'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
        ],
        [
            'nama' => 'Rizki Pratama',
            'inisial' => 'RP',
            'jenjang' => 'Alumni SMA - Mahasiswa ITB',
            'mapel' => 'Persiapan UTBK',
            'rating' => 5,
            'testimoni' => 'Persiapan UTBK jadi lebih terarah dengan tutor dari PeerLearn. Metode belajarnya efektif dan jadwal fleksibel. Akhirnya lolos PTN impian!',
            'gradient' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)'
        ],
        [
            'nama' => 'Dinda Maharani',
            'inisial' => 'DM',
            'jenjang' => 'Siswa SMP Kelas 9',
            'mapel' => 'Bahasa Inggris SMP',
            'rating' => 4.5,
            'testimoni' => 'Belajar Bahasa Inggris jadi lebih fun dan nggak membosankan. Tutor bisa bikin suasana nyaman dan materi mudah dipahami. Rekomendasi banget!',
            'gradient' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
        ]
    ];
}

// Hitung statistik
$totalTestimoni = count($testimoniData);
$totalRating = array_sum(array_column($testimoniData, 'rating'));
$avgRating = $totalTestimoni > 0 ? number_format($totalRating / $totalTestimoni, 1) : 5.0;
$satisfactionRate = 98; // Persentase kepuasan

// Kategori untuk filter
$categories = ['Semua', 'SD', 'SMP', 'SMA', 'UTBK'];
?>

<style>
    body {
        background: #f8f9fa;
    }

    .stats-bar {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 40px;
        display: flex;
        justify-content: space-around;
        align-items: center;
        flex-wrap: wrap;
        gap: 30px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 36px;
        font-weight: 800;
        background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-label {
        font-size: 14px;
        color: #666;
        margin-top: 5px;
    }

    .filter-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-bottom: 40px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 10px 25px;
        border: 2px solid #ddd;
        background: white;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 600;
        color: #666;
        font-size: 15px;
    }

    .filter-btn:hover, .filter-btn.active {
        background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
        color: white;
        border-color: #FF6B35;
        transform: translateY(-2px);
    }

    .testimoni-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
        margin-bottom: 60px;
    }

    .testimoni-card {
        background: white;
        padding: 35px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        position: relative;
        transition: all 0.3s;
    }

    .testimoni-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .quote-icon {
        position: absolute;
        top: -15px;
        left: 30px;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        font-weight: 700;
        box-shadow: 0 5px 15px rgba(255,107,53,0.3);
    }

    .rating-stars {
        display: flex;
        gap: 5px;
        margin-bottom: 20px;
        margin-top: 30px;
        justify-content: center;
    }

    .rating-stars i {
        color: #FFD700;
        font-size: 20px;
    }

    .testimoni-text {
        font-size: 16px;
        line-height: 1.8;
        color: #555;
        text-align: center;
        font-style: italic;
        margin-bottom: 25px;
    }

    .testimoni-footer {
        border-top: 2px solid #f0f0f0;
        padding-top: 20px;
        text-align: center;
    }

    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin: 0 auto 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        font-weight: 700;
    }

    .user-name {
        font-weight: 700;
        color: #1a5f7a;
        font-size: 18px;
        margin-bottom: 5px;
    }

    .user-info {
        color: #999;
        font-size: 14px;
    }

    .user-mapel {
        color: #FF6B35;
        font-size: 13px;
        margin-top: 8px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .testimoni-grid {
            grid-template-columns: 1fr;
        }

        .stats-bar {
            flex-direction: column;
            gap: 20px;
        }
    }
</style>

<div class="container" style="margin-top: 40px;">
    <!-- Stats Bar -->
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-number"><?php echo $totalTestimoni; ?>+</div>
            <div class="stat-label">Testimoni Positif</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $avgRating; ?></div>
            <div class="stat-label">Rating Rata-rata</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $satisfactionRate; ?>%</div>
            <div class="stat-label">Tingkat Kepuasan</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">500+</div>
            <div class="stat-label">Siswa Aktif</div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="filter-buttons">
        <?php foreach ($categories as $cat): ?>
            <button class="filter-btn <?php echo $cat === 'Semua' ? 'active' : ''; ?>" 
                    onclick="filterTestimoni('<?php echo $cat; ?>', this)">
                <?php echo $cat; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Testimoni Grid -->
    <div class="testimoni-grid">
        <?php foreach ($testimoniData as $testi): ?>
            <div class="testimoni-card" data-jenjang="<?php 
                if (stripos($testi['jenjang'], 'SD') !== false) echo 'SD';
                elseif (stripos($testi['jenjang'], 'SMP') !== false) echo 'SMP';
                elseif (stripos($testi['jenjang'], 'SMA') !== false) echo 'SMA';
                elseif (stripos($testi['jenjang'], 'UTBK') !== false) echo 'UTBK';
                else echo 'SMA';
            ?>">
                <div class="quote-icon">
                    <i class="bi bi-quote"></i>
                </div>
                
                <div class="rating-stars">
                    <?php 
                    $fullStars = floor($testi['rating']);
                    $hasHalfStar = ($testi['rating'] - $fullStars) >= 0.5;
                    
                    for ($i = 0; $i < $fullStars; $i++) {
                        echo '<i class="bi bi-star-fill"></i>';
                    }
                    if ($hasHalfStar) {
                        echo '<i class="bi bi-star-half"></i>';
                    }
                    ?>
                </div>
                
                <p class="testimoni-text">"<?php echo htmlspecialchars($testi['testimoni']); ?>"</p>
                
                <div class="testimoni-footer">
                    <div class="user-avatar" style="background: <?php echo $testi['gradient']; ?>">
                        <?php echo $testi['inisial']; ?>
                    </div>
                    <div class="user-name"><?php echo htmlspecialchars($testi['nama']); ?></div>
                    <div class="user-info"><?php echo htmlspecialchars($testi['jenjang']); ?></div>
                    <div class="user-mapel">
                        <i class="bi bi-book"></i> <?php echo htmlspecialchars($testi['mapel']); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Footer -->
<?php include '../../layouts/footer.php'; ?>

<script>
function filterTestimoni(category, button) {
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    button.classList.add('active');
    
    // Filter cards
    const cards = document.querySelectorAll('.testimoni-card');
    cards.forEach(card => {
        if (category === 'Semua') {
            card.style.display = 'block';
        } else {
            card.style.display = card.dataset.jenjang === category ? 'block' : 'none';
        }
    });
}
</script>
