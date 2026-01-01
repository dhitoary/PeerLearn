<?php 
$assetPath = "../../assets/";
include '../../layouts/header.php'; 

// Redirect tutor yang sudah login langsung ke dashboard
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] && isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'tutor') {
        header("Location: ../tutor/dashboard_tutor.php");
        exit();
    } elseif ($_SESSION['user_role'] === 'learner') {
        // Learner boleh akses landing page
    }
}

// Koneksi database
require_once '../../../config/database.php';

// Query untuk mengambil data tutor yang sudah diverifikasi
$query = "SELECT t.id, t.nama_lengkap as nama, t.email, t.keahlian, t.harga_per_sesi, t.rating 
          FROM tutor t 
          WHERE t.status = 'Aktif' 
          ORDER BY t.rating DESC 
          LIMIT 8";
$result = mysqli_query($conn, $query);

$tutorsData = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Ambil mata pelajaran pertama dari tutor_mapel
        $mapelQuery = "SELECT nama_mapel FROM tutor_mapel WHERE tutor_id = {$row['id']} LIMIT 1";
        $mapelResult = mysqli_query($conn, $mapelQuery);
        $mapelRow = mysqli_fetch_assoc($mapelResult);
        
        $tutorsData[] = [
            'id' => $row['id'],
            'nama' => $row['nama'],
            'mapel' => $mapelRow['nama_mapel'] ?? $row['keahlian'],
            'harga' => $row['harga_per_sesi'] ?? 100000,
            'rating' => $row['rating'] ?? 4.5
        ];
    }
}

// Query untuk mengambil jumlah tutor per mata pelajaran
$categoryQuery = "SELECT tm.nama_mapel, COUNT(DISTINCT tm.tutor_id) as tutor_count 
                  FROM tutor_mapel tm
                  INNER JOIN tutor t ON tm.tutor_id = t.id 
                  WHERE t.status = 'Aktif' 
                  GROUP BY tm.nama_mapel 
                  ORDER BY tutor_count DESC";
$categoryResult = mysqli_query($conn, $categoryQuery);

$categoriesData = [];
if ($categoryResult && mysqli_num_rows($categoryResult) > 0) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categoriesData[] = [
            'name' => $row['nama_mapel'],
            'count' => $row['tutor_count']
        ];
    }
}

// Jika tidak ada data dari database, gunakan dummy data
if (empty($tutorsData)) {
    $tutorsData = [
        ['id' => 1, 'nama' => 'Rizky Ramadhan', 'mapel' => 'Matematika', 'harga' => 350000, 'rating' => 4.9],
        ['id' => 2, 'nama' => 'Aulia Putri', 'mapel' => 'Bahasa Inggris', 'harga' => 420000, 'rating' => 5.0],
        ['id' => 3, 'nama' => 'Dimas Wahyu', 'mapel' => 'Fisika', 'harga' => 300000, 'rating' => 4.7],
        ['id' => 4, 'nama' => 'Nadia Fitri', 'mapel' => 'Kimia', 'harga' => 400000, 'rating' => 4.8],
        ['id' => 5, 'nama' => 'Farhan Akbar', 'mapel' => 'Biologi', 'harga' => 320000, 'rating' => 4.6],
        ['id' => 6, 'nama' => 'Sinta Maharani', 'mapel' => 'Bahasa Indonesia', 'harga' => 280000, 'rating' => 4.5],
        ['id' => 7, 'nama' => 'Adi Pratama', 'mapel' => 'Ekonomi', 'harga' => 330000, 'rating' => 4.7],
        ['id' => 8, 'nama' => 'Maya Sari', 'mapel' => 'Sejarah', 'harga' => 260000, 'rating' => 4.4]
    ];
}
?>

<main class="site-main">
  <!-- HERO (Full Width) -->
  <section class="hero-section" role="region" aria-label="Hero">
    <div class="hero-overlay">
      <div class="hero-inner">
        <div class="hero-badge">🏆 Platform #1 di Lampung</div>
        <h1 class="hero-title mt-2">
          Raih Prestasi Akademik<br>
          dengan <span class="accent">Tutor Terbaik</span>
        </h1>
        <p class="hero-sub">
          PeerLearn menghubungkan siswa SD/SMP/SMA dengan tutor mahasiswa berprestasi. 
          Bimbingan fleksibel, terjangkau, dan berkualitas.
        </p>
      </div>
    </div>
  </section>

  <!-- CONTAINER untuk konten lainnya -->
  <div class="container">
    <!-- SEARCH -->
    <div class="search-box" role="search" aria-label="Pencarian tutor">
      <input id="searchInput" type="text" placeholder="Cari mata pelajaran atau nama tutor..." aria-label="Cari tutor">
      <button id="btnSearch" class="btn-search" type="button">Cari Tutor</button>
    </div>

  <!-- KATEGORI POPULER -->
  <section class="section-category" aria-label="Kategori populer" style="padding: 80px 0;">
    <div class="section-title" style="font-size: 42px; font-weight: 700; color: #1a5f7a; margin-bottom: 15px;">Jelajahi Mata Pelajaran</div>
    <div class="section-desc" style="color: #666; margin-bottom: 50px; font-size: 18px;">Pilih dari 50+ mata pelajaran yang tersedia untuk semua jenjang pendidikan</div>
    
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; margin-bottom: 50px;">
      <?php 
      // Definisi kategori dengan icon dan warna
      $categoryIcons = [
        'Matematika' => ['icon' => 'bi-calculator', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'Ekonomi' => ['icon' => 'bi-journal-text', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'Fisika' => ['icon' => 'bi-lightning', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'Kimia' => ['icon' => 'bi-droplet', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'Biologi' => ['icon' => 'bi-heart-pulse', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'Bahasa Inggris' => ['icon' => 'bi-translate', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'Pemrograman' => ['icon' => 'bi-code-slash', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'Informatika' => ['icon' => 'bi-journal-text', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'Bahasa Indonesia' => ['icon' => 'bi-book', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'Geografi' => ['icon' => 'bi-globe', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'Akuntansi' => ['icon' => 'bi-journal-text', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'IPA' => ['icon' => 'bi-journal-text', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
        'IPS' => ['icon' => 'bi-journal-text', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)']
      ];
      
      $displayedCount = 0;
      foreach ($categoriesData as $cat) {
        if ($displayedCount >= 8) break;
        
        $mapel = $cat['name'];
        $count = $cat['count'];
        
        // Cari yang cocok dengan key
        $matchedKey = null;
        foreach ($categoryIcons as $key => $value) {
          if (stripos($mapel, $key) !== false || stripos($key, $mapel) !== false) {
            $matchedKey = $key;
            break;
          }
        }
        
        $icon = $matchedKey ? $categoryIcons[$matchedKey]['icon'] : 'bi-journal-text';
        $gradient = $matchedKey ? $categoryIcons[$matchedKey]['gradient'] : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        
        $cardId = 'card-' . $displayedCount;
      ?>
        <div id="<?php echo $cardId; ?>" class="category-card-item" 
             style="background: #f8f9fa; padding: 45px 30px; border-radius: 18px; text-align: center; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 2px 12px rgba(0,0,0,0.08); position: relative; overflow: hidden;"
             data-gradient="<?php echo $gradient; ?>">
          <div class="icon-circle" style="width: 90px; height: 90px; margin: 0 auto 25px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.4s;">
            <i class="bi <?php echo $icon; ?>" style="font-size: 40px; color: #FF6B35; transition: all 0.4s;"></i>
          </div>
          <div class="card-title" style="font-size: 22px; font-weight: 700; color: #1a5f7a; transition: all 0.4s;">
            <?php echo htmlspecialchars($mapel); ?>
          </div>
        </div>
      <?php 
        $displayedCount++;
      } 
      ?>
    </div>
    
    <div style="text-align: center;">
      <a href="categories.php" style="display: inline-flex; align-items: center; gap: 12px; padding: 18px 45px; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); color: white; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 18px; transition: all 0.3s; box-shadow: 0 5px 20px rgba(255, 107, 53, 0.35);" 
         onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 30px rgba(255, 107, 53, 0.45)'" 
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 20px rgba(255, 107, 53, 0.35)'">
        Lihat Semua Kategori
        <i class="bi bi-arrow-right" style="font-size: 20px;"></i>
      </a>
    </div>
  </section>
  
  <script>
  // Hover effect untuk category cards
  document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.category-card-item');
    
    cards.forEach(card => {
      const gradient = card.dataset.gradient;
      const iconCircle = card.querySelector('.icon-circle');
      const icon = card.querySelector('.bi');
      const title = card.querySelector('.card-title');
      
      card.addEventListener('mouseenter', function() {
        this.style.background = gradient;
        this.style.transform = 'translateY(-10px) scale(1.02)';
        this.style.boxShadow = '0 20px 40px rgba(102, 126, 234, 0.3)';
        
        iconCircle.style.background = 'rgba(255, 255, 255, 0.25)';
        icon.style.color = 'white';
        title.style.color = 'white';
      });
      
      card.addEventListener('mouseleave', function() {
        this.style.background = '#f8f9fa';
        this.style.transform = 'translateY(0) scale(1)';
        this.style.boxShadow = '0 2px 12px rgba(0,0,0,0.08)';
        
        iconCircle.style.background = 'white';
        icon.style.color = '#FF6B35';
        title.style.color = '#1a5f7a';
      });
    });
  });
  </script>

  <!-- CARA KERJA -->
  <section class="section-steps" aria-label="Cara kerja">
    <div class="section-title">Cara Kerja</div>
    <div class="section-desc">3 langkah sederhana untuk mulai belajar bersama tutor terbaik di Lampung.</div>

    <div class="steps">
      <div class="step-card">
        <div class="step-number">1</div>
        <div class="step-title">Cari & Pilih Tutor</div>
        <div class="step-desc">Gunakan pencarian berdasarkan mata pelajaran atau lokasi.</div>
      </div>

      <div class="step-card">
        <div class="step-number">2</div>
        <div class="step-title">Booking Jadwal</div>
        <div class="step-desc">Ajukan permintaan pertemuan, lalu tutor konfirmasi.</div>
      </div>

      <div class="step-card">
        <div class="step-number">3</div>
        <div class="step-title">Belajar & Beri Ulasan</div>
        <div class="step-desc">Sesi selesai, beri penilaian untuk kualitas tutor.</div>
      </div>
    </div>
  </section>

  <!-- REKOMENDASI TUTOR -->
  <section class="section-rekomendasi" aria-label="Rekomendasi tutor">
    <div class="section-title">Rekomendasi Tutor</div>
    <div class="section-desc">Tutor terbaik dan terpercaya untuk berbagai mata pelajaran.</div>
    <div class="grid" id="tutorContainer">
      <!-- rendered by JS -->
    </div>
  </section>

  <!-- TESTIMONIAL -->
  <section class="section-testimoni" aria-label="Testimoni" style="padding: 80px 0; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
    <div class="section-title" style="font-size: 42px; font-weight: 700; color: #1a5f7a; margin-bottom: 15px; text-align: center;">Apa Kata Siswa Kami</div>
    <div class="section-desc" style="color: #666; margin-bottom: 60px; font-size: 18px; text-align: center;">Testimoni nyata dari siswa yang telah merasakan bimbingan berkualitas</div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto;">
      <!-- Testimoni 1 -->
      <div style="background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative; transition: all 0.3s;"
           onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 15px 40px rgba(0,0,0,0.15)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.1)'">
        <div style="position: absolute; top: -15px; left: 30px; width: 50px; height: 50px; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700; box-shadow: 0 5px 15px rgba(255,107,53,0.3);">
          <i class="bi bi-quote" style="font-size: 28px;"></i>
        </div>
        <div style="margin-top: 30px; margin-bottom: 25px;">
          <div style="display: flex; gap: 5px; margin-bottom: 20px; justify-content: center;">
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
          </div>
          <p style="font-size: 16px; line-height: 1.8; color: #555; text-align: center; font-style: italic; margin-bottom: 25px;">
            "Tutor Matematika yang mengajar saya sangat sabar dan detail dalam menjelaskan. Nilai saya meningkat drastis dari 70 menjadi 95! Terima kasih PeerLearn."
          </p>
        </div>
        <div style="border-top: 2px solid #f0f0f0; padding-top: 20px; text-align: center;">
          <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
            AN
          </div>
          <div style="font-weight: 700; color: #1a5f7a; font-size: 18px; margin-bottom: 5px;">Alya Natasya</div>
          <div style="color: #999; font-size: 14px;">Siswa SMA Kelas 12</div>
          <div style="color: #FF6B35; font-size: 13px; margin-top: 8px; font-weight: 600;">
            <i class="bi bi-book"></i> Matematika
          </div>
        </div>
      </div>

      <!-- Testimoni 2 -->
      <div style="background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative; transition: all 0.3s;"
           onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 15px 40px rgba(0,0,0,0.15)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.1)'">
        <div style="position: absolute; top: -15px; left: 30px; width: 50px; height: 50px; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700; box-shadow: 0 5px 15px rgba(255,107,53,0.3);">
          <i class="bi bi-quote" style="font-size: 28px;"></i>
        </div>
        <div style="margin-top: 30px; margin-bottom: 25px;">
          <div style="display: flex; gap: 5px; margin-bottom: 20px; justify-content: center;">
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
          </div>
          <p style="font-size: 16px; line-height: 1.8; color: #555; text-align: center; font-style: italic; margin-bottom: 25px;">
            "Persiapan UTBK jadi lebih terarah dengan tutor dari PeerLearn. Metode belajarnya efektif dan jadwal fleksibel. Akhirnya lolos PTN impian!"
          </p>
        </div>
        <div style="border-top: 2px solid #f0f0f0; padding-top: 20px; text-align: center;">
          <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
            RP
          </div>
          <div style="font-weight: 700; color: #1a5f7a; font-size: 18px; margin-bottom: 5px;">Rizki Pratama</div>
          <div style="color: #999; font-size: 14px;">Alumni SMA - Mahasiswa ITB</div>
          <div style="color: #FF6B35; font-size: 13px; margin-top: 8px; font-weight: 600;">
            <i class="bi bi-book"></i> Persiapan UTBK
          </div>
        </div>
      </div>

      <!-- Testimoni 3 -->
      <div style="background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative; transition: all 0.3s;"
           onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 15px 40px rgba(0,0,0,0.15)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.1)'">
        <div style="position: absolute; top: -15px; left: 30px; width: 50px; height: 50px; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700; box-shadow: 0 5px 15px rgba(255,107,53,0.3);">
          <i class="bi bi-quote" style="font-size: 28px;"></i>
        </div>
        <div style="margin-top: 30px; margin-bottom: 25px;">
          <div style="display: flex; gap: 5px; margin-bottom: 20px; justify-content: center;">
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-half" style="color: #FFD700; font-size: 20px;"></i>
          </div>
          <p style="font-size: 16px; line-height: 1.8; color: #555; text-align: center; font-style: italic; margin-bottom: 25px;">
            "Belajar Bahasa Inggris jadi lebih fun dan nggak membosankan. Tutor bisa bikin suasana nyaman dan materi mudah dipahami. Rekomendasi banget!"
          </p>
        </div>
        <div style="border-top: 2px solid #f0f0f0; padding-top: 20px; text-align: center;">
          <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
            DM
          </div>
          <div style="font-weight: 700; color: #1a5f7a; font-size: 18px; margin-bottom: 5px;">Dinda Maharani</div>
          <div style="color: #999; font-size: 14px;">Siswa SMP Kelas 9</div>
          <div style="color: #FF6B35; font-size: 13px; margin-top: 8px; font-weight: 600;">
            <i class="bi bi-book"></i> Bahasa Inggris SMP
          </div>
        </div>
      </div>

      <!-- Testimoni 4 -->
      <div style="background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative; transition: all 0.3s;"
           onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 15px 40px rgba(0,0,0,0.15)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.1)'">
        <div style="position: absolute; top: -15px; left: 30px; width: 50px; height: 50px; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700; box-shadow: 0 5px 15px rgba(255,107,53,0.3);">
          <i class="bi bi-quote" style="font-size: 28px;"></i>
        </div>
        <div style="margin-top: 30px; margin-bottom: 25px;">
          <div style="display: flex; gap: 5px; margin-bottom: 20px; justify-content: center;">
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
          </div>
          <p style="font-size: 16px; line-height: 1.8; color: #555; text-align: center; font-style: italic; margin-bottom: 25px;">
            "Fisika yang tadinya susah banget sekarang jadi lebih mudah dipahami. Tutor ngejelasin dengan contoh kehidupan sehari-hari jadi lebih relate."
          </p>
        </div>
        <div style="border-top: 2px solid #f0f0f0; padding-top: 20px; text-align: center;">
          <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
            FA
          </div>
          <div style="font-weight: 700; color: #1a5f7a; font-size: 18px; margin-bottom: 5px;">Farhan Aditya</div>
          <div style="color: #999; font-size: 14px;">Siswa SMA Kelas 11</div>
          <div style="color: #FF6B35; font-size: 13px; margin-top: 8px; font-weight: 600;">
            <i class="bi bi-book"></i> Fisika
          </div>
        </div>
      </div>

      <!-- Testimoni 5 -->
      <div style="background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative; transition: all 0.3s;"
           onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 15px 40px rgba(0,0,0,0.15)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.1)'">
        <div style="position: absolute; top: -15px; left: 30px; width: 50px; height: 50px; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700; box-shadow: 0 5px 15px rgba(255,107,53,0.3);">
          <i class="bi bi-quote" style="font-size: 28px;"></i>
        </div>
        <div style="margin-top: 30px; margin-bottom: 25px;">
          <div style="display: flex; gap: 5px; margin-bottom: 20px; justify-content: center;">
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
          </div>
          <p style="font-size: 16px; line-height: 1.8; color: #555; text-align: center; font-style: italic; margin-bottom: 25px;">
            "Les privat terbaik yang pernah saya ikuti! Harganya terjangkau untuk pelajar, tutornya ramah, dan sistem bookingnya mudah banget."
          </p>
        </div>
        <div style="border-top: 2px solid #f0f0f0; padding-top: 20px; text-align: center;">
          <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
            SK
          </div>
          <div style="font-weight: 700; color: #1a5f7a; font-size: 18px; margin-bottom: 5px;">Siti Khadijah</div>
          <div style="color: #999; font-size: 14px;">Siswa SMP Kelas 8</div>
          <div style="color: #FF6B35; font-size: 13px; margin-top: 8px; font-weight: 600;">
            <i class="bi bi-book"></i> Matematika SMP
          </div>
        </div>
      </div>

      <!-- Testimoni 6 -->
      <div style="background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative; transition: all 0.3s;"
           onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 15px 40px rgba(0,0,0,0.15)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.1)'">
        <div style="position: absolute; top: -15px; left: 30px; width: 50px; height: 50px; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700; box-shadow: 0 5px 15px rgba(255,107,53,0.3);">
          <i class="bi bi-quote" style="font-size: 28px;"></i>
        </div>
        <div style="margin-top: 30px; margin-bottom: 25px;">
          <div style="display: flex; gap: 5px; margin-bottom: 20px; justify-content: center;">
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-fill" style="color: #FFD700; font-size: 20px;"></i>
            <i class="bi bi-star-half" style="color: #FFD700; font-size: 20px;"></i>
          </div>
          <p style="font-size: 16px; line-height: 1.8; color: #555; text-align: center; font-style: italic; margin-bottom: 25px;">
            "Tutor Kimia yang mengajar sangat kompeten dan berpengalaman. Konsep yang rumit dijelaskan dengan sederhana. Sangat membantu untuk persiapan ujian."
          </p>
        </div>
        <div style="border-top: 2px solid #f0f0f0; padding-top: 20px; text-align: center;">
          <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
            BA
          </div>
          <div style="font-weight: 700; color: #1a5f7a; font-size: 18px; margin-bottom: 5px;">Budi Anwar</div>
          <div style="color: #999; font-size: 14px;">Siswa SMA Kelas 12</div>
          <div style="color: #FF6B35; font-size: 13px; margin-top: 8px; font-weight: 600;">
            <i class="bi bi-book"></i> Kimia
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="section-faq" aria-label="FAQ">
    <div class="section-title">Pertanyaan Umum</div>
    <div class="section-desc">Pertanyaan yang sering diajukan — jawaban singkat dan jelas.</div>

    <div class="faq-list" style="max-width:900px;margin:24px auto 60px;">
      <div class="faq-item" data-idx="0">
        <div class="faq-question">Bagaimana cara verifikasi tutor?</div>
        <div class="faq-answer">Tutor wajib unggah KTM/transkrip. Admin akan memverifikasi sebelum akun aktif.</div>
      </div>

      <div class="faq-item" data-idx="1">
        <div class="faq-question">Apakah ada jaminan kualitas?</div>
        <div class="faq-answer">Sistem rating & review membantu memastikan kualitas — Anda bisa melihat ulasan siswa sebelumnya.</div>
      </div>

      <div class="faq-item" data-idx="2">
        <div class="faq-question">Bagaimana cara pembayaran?</div>
        <div class="faq-answer">Saat ini diarahkan ke WhatsApp tutor; integrasi payment gateway akan dikembangkan pada backend selanjutnya.</div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="section-cta" aria-label="Call to action" style="background: linear-gradient(135deg, #1a5f7a 0%, #2d7a8f 100%); padding: 100px 60px 120px 60px; text-align: center; margin: 60px auto 0 auto; border-radius: 0; width: 100%;">
    <div style="max-width: 900px; margin: 0 auto;">
      <h2 style="font-size: 48px; font-weight: 700; color: white; margin: 0 auto 50px auto; line-height: 1.3; letter-spacing: -0.5px; text-align: center; max-width: 800px;">
        Siap Meningkatkan Prestasi Akademik
      </h2>
      <div style="display: flex; gap: 20px; justify-content: center; align-items: center; flex-wrap: wrap;">
        <a href="../auth/register.php" style="display: inline-flex; align-items: center; gap: 10px; padding: 15px 35px; background: white; color: #1a5f7a; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
          <i class="bi bi-person-plus" style="font-size: 18px;"></i>
          Daftar Sekarang
        </a>
        <a href="search_result.php" style="display: inline-flex; align-items: center; gap: 10px; padding: 15px 35px; background: white; color: #1a5f7a; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
          <i class="bi bi-search" style="font-size: 18px;"></i>
          Cari Tutor
        </a>
      </div>
    </div>
  </section>
  </div><!-- end container -->
</main>

<?php include '../../layouts/footer.php'; ?>

<!-- ======= Inline JS: render tutors + FAQ interaksi (simple) ======= -->
<script>
  // Data tutors dari PHP
  const tutorsData = <?php echo json_encode($tutorsData); ?>;

  // format harga
  function rupiah(n){ return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); }

  // render tutor cards
  function renderTutors(list){
    const container = document.getElementById('tutorContainer');
    if(!container) return;
    container.innerHTML = '';
    list.forEach(t => {
      const el = document.createElement('div');
      el.className = 'tutor-card';
      el.innerHTML = `
        <div class="tutor-photo" aria-hidden="true">${t.nama.split(' ')[0].charAt(0)}</div>
        <div class="tutor-name">${t.nama}</div>
        <div class="tutor-sub">${t.mapel}</div>
        <div class="tutor-meta">
          <div class="rating">★ ${t.rating}</div>
          <div style="margin-left:auto" class="tutor-price">Rp ${rupiah(t.harga)}</div>
        </div>
        <a href="detail_tutor.php?id=${t.id}&nama=${encodeURIComponent(t.nama)}&mapel=${encodeURIComponent(t.mapel)}&harga=${t.harga}&rating=${t.rating}" class="btn-detail" role="button" aria-label="Detail ${t.nama}">Detail Tutor</a>
      `;
      container.appendChild(el);
    });
  }

  // initial render
  renderTutors(tutorsData);

  // search realtime (on this page only)
  const searchInput = document.getElementById('searchInput');
  const btnSearch = document.getElementById('btnSearch');
  if(searchInput){
    searchInput.addEventListener('input', function(e){
      const q = e.target.value.toLowerCase().trim();
      const filtered = tutorsData.filter(t => t.nama.toLowerCase().includes(q) || t.mapel.toLowerCase().includes(q));
      renderTutors(filtered);
    });
    // support button search for keyboard-less test
    btnSearch && btnSearch.addEventListener('click', function(){
      const q = (searchInput.value||'').toLowerCase().trim();
      const filtered = tutorsData.filter(t => t.nama.toLowerCase().includes(q) || t.mapel.toLowerCase().includes(q));
      renderTutors(filtered);
    });
  }

  // FAQ accordion simple
  document.querySelectorAll('.faq-item').forEach(item => {
    item.addEventListener('click', function(){
      // toggle active class
      const isActive = this.classList.contains('active');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));
      if(!isActive) this.classList.add('active');
    });
  });

  // simple scroll reveal for elements with .fade-up
  function revealOnScroll(){
    document.querySelectorAll('.fade-up').forEach(el => {
      const rect = el.getBoundingClientRect();
      if(rect.top < window.innerHeight - 80){ el.classList.add('show'); }
    });
  }
  window.addEventListener('scroll', revealOnScroll);
  window.addEventListener('load', revealOnScroll);

</script>
