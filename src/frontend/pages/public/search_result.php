<?php 
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../../config/database.php';

$assetPath = "../../assets/";
$logoPath = "/src/assets/img/logo.png";

$isLoggedIn = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'];
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : (isset($_SESSION['email']) ? $_SESSION['email'] : '');

$siswa_data = null;
if ($isLoggedIn && $userRole == 'learner') {
    $siswa_query = "SELECT * FROM siswa WHERE email = '$userEmail' LIMIT 1";
    $siswa_result = mysqli_query($conn, $siswa_query);
    $siswa_data = mysqli_fetch_assoc($siswa_result);
}

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// Query untuk mengambil data tutor
$query = "SELECT t.id, t.nama_lengkap as nama, t.email, t.keahlian, t.harga_per_sesi, t.rating 
          FROM tutor t 
          WHERE t.status = 'Aktif'";

// Jika ada pencarian, tambahkan filter
if (!empty($searchQuery)) {
    $searchQuery = mysqli_real_escape_string($conn, $searchQuery);
    $query .= " AND (t.nama_lengkap LIKE '%{$searchQuery}%' 
                 OR t.keahlian LIKE '%{$searchQuery}%')";
}

$query .= " ORDER BY t.rating DESC";

$result = mysqli_query($conn, $query);

$tutorsData = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Ambil semua jenjang dari tutor_mapel
        $mapelQuery = "SELECT DISTINCT jenjang FROM tutor_mapel WHERE tutor_id = {$row['id']}";
        $mapelResult = mysqli_query($conn, $mapelQuery);
        
        $jenjangList = [];
        if ($mapelResult && mysqli_num_rows($mapelResult) > 0) {
            while ($mapelRow = mysqli_fetch_assoc($mapelResult)) {
                $jenjangList[] = $mapelRow['jenjang'];
            }
        }
        
        // Jika tidak ada data di tutor_mapel, set default
        if (empty($jenjangList)) {
            $jenjangList = ['SD', 'SMP', 'SMA', 'Umum'];
        }
        
        // Simpan tutor sekali saja dengan array jenjang
        $tutorsData[] = [
            'id' => (int)$row['id'],
            'nama' => $row['nama'],
            'mapel' => $row['keahlian'],
            'jenjang_list' => $jenjangList, // Array of all jenjang
            'jenjang' => implode(', ', $jenjangList), // String for display
            'harga' => (int)($row['harga_per_sesi'] ?? 100000),
            'rating' => (float)($row['rating'] ?? 4.5)
        ];
    }
}

// Jika database kosong, gunakan data dummy
if (empty($tutorsData)) {
    $tutorsData = [
        ['id' => 1, 'nama' => 'Rizky Ramadhan', 'mapel' => 'Matematika', 'jenjang' => 'SMA', 'harga' => 350000, 'rating' => 4.9],
        ['id' => 2, 'nama' => 'Aulia Putri', 'mapel' => 'Bahasa Inggris', 'jenjang' => 'SMP', 'harga' => 420000, 'rating' => 5.0],
        ['id' => 3, 'nama' => 'Dimas Wahyu', 'mapel' => 'Fisika', 'jenjang' => 'SMA', 'harga' => 300000, 'rating' => 4.7],
        ['id' => 4, 'nama' => 'Nadia Fitri', 'mapel' => 'Kimia', 'jenjang' => 'SMA', 'harga' => 400000, 'rating' => 4.8],
        ['id' => 5, 'nama' => 'Farhan Akbar', 'mapel' => 'Biologi', 'jenjang' => 'SMP', 'harga' => 320000, 'rating' => 4.6],
        ['id' => 6, 'nama' => 'Sinta Maharani', 'mapel' => 'Bahasa Indonesia', 'jenjang' => 'SD', 'harga' => 280000, 'rating' => 4.5],
        ['id' => 7, 'nama' => 'Adi Pratama', 'mapel' => 'Ekonomi', 'jenjang' => 'SMA', 'harga' => 330000, 'rating' => 4.7],
        ['id' => 8, 'nama' => 'Maya Sari', 'mapel' => 'Sejarah', 'jenjang' => 'Kuliah', 'harga' => 260000, 'rating' => 4.4]
    ];
}
?>

<?php if ($isLoggedIn && $userRole == 'learner' && $siswa_data): ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Tutor - PeerLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $assetPath; ?>css/style.css">
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
            <li><a href="../learner/dashboard_siswa.php">Beranda</a></li>
            <li><a href="search_result.php" class="active">Cari Tutor</a></li>
            <li><a href="../learner/sesi_saya.php">Sesi Saya</a></li>
            <li><a href="../learner/riwayat.php">Riwayat Booking</a></li>
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
                    <a href="../learner/profil.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
                        <i class="bi bi-person"></i> Profil Saya
                    </a>
                    <a href="../learner/sesi_saya.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
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

<?php else: ?>
<?php include '../../layouts/header.php'; ?>
<?php endif; ?>

<main style="background: #f8f9fa; min-height: 100vh; padding-top: <?php echo ($isLoggedIn && $userRole == 'learner') ? '20px' : '100px'; ?>;">
  <div class="container" style="max-width: 1400px; margin: 0 auto; padding: 40px 120px;">

    <!-- TITLE & SEARCH -->
    <div style="background: white; padding: 40px 50px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 30px;">
      <h2 style="font-size: 32px; font-weight: 700; color: #1e293b; margin: 0 0 30px 0;">Temukan Tutor Terbaik</h2>
      
      <form method="GET" action="search_result.php" style="display: flex; gap: 12px; margin-bottom: 35px;">
        <input type="text" id="searchInput" name="q" placeholder="Cari mata pelajaran, nama tutor, atau universitas..." 
               value="<?php echo htmlspecialchars($searchQuery); ?>"
               style="flex: 1; padding: 16px 24px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; outline: none; transition: all 0.2s;"
               onfocus="this.style.borderColor='#FF6B35'"
               onblur="this.style.borderColor='#e2e8f0'">
        <button type="submit" style="background: #FF6B35; color: white; border: none; padding: 16px 40px; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 15px; white-space: nowrap; transition: all 0.2s;"
                onmouseover="this.style.background='#E55A2B'"
                onmouseout="this.style.background='#FF6B35'">
          <i class="bi bi-search"></i> Cari
        </button>
      </form>

      <!-- FILTERS - Clean Horizontal Design -->
      <div style="display: flex; gap: 10px; align-items: center; background: #f8fafc; padding: 14px 18px; border-radius: 12px; border: 1px solid #e2e8f0; overflow-x: auto; flex-wrap: wrap;">
        
        <!-- Filter Icon & Label -->
        <div style="display: flex; align-items: center; gap: 8px; padding-right: 10px;">
          <i class="bi bi-sliders" style="color: #FF6B35; font-size: 18px;"></i>
          <span style="color: #334155; font-size: 15px; font-weight: 600; white-space: nowrap;">Filter:</span>
        </div>
        
        <!-- Jenjang Pills -->
        <button class="filter-pill jenjang-filter active" data-jenjang="Semua">Lihat Semua</button>
        <button class="filter-pill jenjang-filter" data-jenjang="SD">SD</button>
        <button class="filter-pill jenjang-filter" data-jenjang="SMP">SMP</button>
        <button class="filter-pill jenjang-filter" data-jenjang="SMA">SMA</button>
        <button class="filter-pill jenjang-filter" data-jenjang="Kuliah">Kuliah</button>
        
        <div style="width: 1px; height: 24px; background: #cbd5e1; margin: 0 4px;"></div>
        
        <!-- Mapel Dropdown -->
        <select id="mapelFilter" style="padding: 8px 14px; border: 1px solid #cbd5e1; border-radius: 8px; background: white; cursor: pointer; font-size: 14px; color: #334155; font-weight: 500; outline: none; min-width: 140px;">
          <option value="">Lihat Semua</option>
          <option value="Matematika">Matematika</option>
          <option value="Fisika">Fisika</option>
          <option value="Kimia">Kimia</option>
          <option value="Biologi">Biologi</option>
          <option value="Bahasa Inggris">Bahasa Inggris</option>
          <option value="Bahasa Indonesia">Bahasa Indonesia</option>
          <option value="Ekonomi">Ekonomi</option>
        </select>

        <div style="width: 1px; height: 24px; background: #cbd5e1; margin: 0 4px;"></div>

        <!-- Price Slider -->
        <div style="display: flex; align-items: center; gap: 10px;">
          <span style="color: #334155; font-weight: 500; font-size: 14px; white-space: nowrap;">Harga Maks:</span>
          <input type="range" id="priceRange" min="0" max="500" value="100" step="50" style="width: 120px; cursor: pointer;">
          <span id="priceLabel" style="background: #FF6B35; color: white; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; min-width: 60px; text-align: center;">100k</span>
        </div>

        <!-- Reset Button -->
        <button onclick="resetFilters()" style="background: white; border: 1px solid #cbd5e1; color: #64748b; padding: 8px 16px; border-radius: 20px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s; white-space: nowrap; margin-left: auto; display: flex; align-items: center; gap: 6px;"
                onmouseover="this.style.borderColor='#FF6B35'; this.style.color='#FF6B35'"
                onmouseout="this.style.borderColor='#cbd5e1'; this.style.color='#64748b'">
          <i class="bi bi-arrow-clockwise"></i> Reset
        </button>
      </div>
    </div>

    <!-- RESULT INFO -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 0 4px;">
      <h3 style="color: #1e293b; font-size: 18px; font-weight: 600; margin: 0;">
        Menampilkan <span id="resultCount" style="color: #FF6B35;">51</span> Tutor
      </h3>
      <select id="sortBy" style="padding: 10px 18px; border: 2px solid #e2e8f0; border-radius: 8px; background: white; cursor: pointer; font-size: 14px; color: #1e293b; font-weight: 500; outline: none;">
        <option value="recommended">Rekomendasi</option>
        <option value="rating">Rating Tertinggi</option>
        <option value="price-low">Harga Terendah</option>
        <option value="price-high">Harga Tertinggi</option>
      </select>
    </div>

    <!-- SEARCH RESULT GRID -->
    <div id="resultContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; margin-bottom: 40px;">
      <!-- JS render -->
    </div>

    <!-- PAGINATION -->
    <div id="paginationContainer" style="display: flex; justify-content: center; align-items: center; gap: 8px; padding: 40px 0 60px 0;">
      <!-- JS render pagination -->
    </div>

  </div>
</main>


<!-- ================= JS: DATA TUTOR + SEARCH ================= -->
<script>
const tutorsData = <?php echo json_encode($tutorsData); ?>;

// format ribuan
function rp(n){
  return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Generate random colors for avatar
function getAvatarColor(name) {
  const colors = ['#FF6B9D', '#4A90E2', '#50C878', '#FF6B35', '#9B59B6', '#E67E22', '#16A085', '#D35400'];
  const index = name.charCodeAt(0) % colors.length;
  return colors[index];
}

// PAGINATION
let currentPage = 1;
const itemsPerPage = 20;
let filteredData = [...tutorsData];

// RENDER RESULT with Pagination
function renderResults(list){
  filteredData = list;
  currentPage = 1; // Reset to page 1 when filter changes
  renderPage();
  renderPagination();
}

function renderPage() {
  const container = document.getElementById('resultContainer');
  container.innerHTML = "";
  
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const pageData = filteredData.slice(startIndex, endIndex);
  
  document.getElementById('resultCount').textContent = filteredData.length;

  if(filteredData.length === 0){
    container.innerHTML = `
      <p style="grid-column:1/-1;text-align:center;color:#64748b;padding:60px 20px;">
        Tidak ada tutor ditemukan. Coba ubah filter pencarian Anda.
      </p>
    `;
    return;
  }

  pageData.forEach(t => {
    const card = document.createElement("div");
    card.className = "tutor-card-modern";
    
    const initials = t.nama.split(' ').map(n => n[0]).join('').substring(0, 2);
    const avatarColor = getAvatarColor(t.nama);
    const ratingStars = '⭐'.repeat(Math.floor(t.rating));

    card.innerHTML = `
      <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; cursor: pointer; height: 100%;"
           onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'; this.style.transform='translateY(-4px)'"
           onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'; this.style.transform='translateY(0)'">
        
        <!-- Avatar & Verified Badge -->
        <div style="display: flex; align-items: start; gap: 16px; margin-bottom: 16px;">
          <div style="width: 56px; height: 56px; border-radius: 50%; background: ${avatarColor}; display: flex; align-items: center; justify-content: center; color: white; font-size: 22px; font-weight: 700; flex-shrink: 0;">
            ${initials}
          </div>
          <div style="flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
              <h3 style="font-size: 17px; font-weight: 700; color: #1e293b; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                ${t.nama}
              </h3>
              <span style="color: #10b981; font-size: 14px;">✓</span>
            </div>
            <p style="color: #64748b; font-size: 13px; margin: 0;">${t.mapel || 'Umum'}</p>
          </div>
          <div style="background: #FEF3C7; color: #D97706; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 4px;">
            ⭐ ${t.rating}
          </div>
        </div>

        <!-- Tags -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px;">
          <span style="background: #DBEAFE; color: #1D4ED8; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
            Fisika
          </span>
          <span style="background: #DBEAFE; color: #1D4ED8; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
            ${t.mapel}
          </span>
        </div>

        <!-- Stats -->
        <div style="display: flex; gap: 20px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
          <div style="display: flex; align-items: center; gap: 6px; color: #64748b; font-size: 13px;">
            <i class="bi bi-clock"></i>
            <span>${Math.floor(Math.random() * 4) + 2} tahun</span>
          </div>
          <div style="display: flex; align-items: center; gap: 6px; color: #64748b; font-size: 13px;">
            <i class="bi bi-people"></i>
            <span>${Math.floor(Math.random() * 300) + 50} review</span>
          </div>
        </div>

        <!-- Price & Actions -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <div>
            <div style="color: #FF6B35; font-size: 20px; font-weight: 700;">
              Rp ${Math.floor(t.harga / 1000)}k
            </div>
            <div style="color: #94a3b8; font-size: 12px;">/jam</div>
          </div>
        </div>

        <!-- Buttons -->
        <div style="display: grid; gap: 8px;">
          <button onclick="window.location.href='../learner/booking.php?tutor_id=${t.id}'" 
                  style="background: #FF6B35; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%; transition: all 0.2s;"
                  onmouseover="this.style.background='#E55A2B'"
                  onmouseout="this.style.background='#FF6B35'">
            <i class="bi bi-calendar-check"></i> Booking
          </button>
          <button onclick="window.location.href='detail_tutor.php?id=${t.id}'"
                  style="background: white; color: #FF6B35; border: 2px solid #FF6B35; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%; transition: all 0.2s;"
                  onmouseover="this.style.background='#FFF5F0'"
                  onmouseout="this.style.background='white'">
            <i class="bi bi-person"></i> Lihat Profil
          </button>
        </div>

      </div>
    `;

    container.appendChild(card);
  });
}

function renderPagination() {
  const paginationContainer = document.getElementById('paginationContainer');
  paginationContainer.innerHTML = '';
  
  const totalPages = Math.ceil(filteredData.length / itemsPerPage);
  
  if (totalPages <= 1) {
    paginationContainer.style.display = 'none';
    return;
  }
  
  paginationContainer.style.display = 'flex';
  
  // Previous button
  const prevBtn = document.createElement('button');
  prevBtn.innerHTML = '<i class="bi bi-chevron-left"></i>';
  prevBtn.className = 'pagination-btn';
  prevBtn.disabled = currentPage === 1;
  prevBtn.onclick = () => {
    if (currentPage > 1) {
      currentPage--;
      renderPage();
      renderPagination();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };
  paginationContainer.appendChild(prevBtn);
  
  // Page numbers
  const maxVisiblePages = 5;
  let startPage = Math.max(1, currentPage - 2);
  let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
  
  if (endPage - startPage < maxVisiblePages - 1) {
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }
  
  for (let i = startPage; i <= endPage; i++) {
    const pageBtn = document.createElement('button');
    pageBtn.textContent = i;
    pageBtn.className = 'pagination-btn' + (i === currentPage ? ' active' : '');
    pageBtn.onclick = () => {
      currentPage = i;
      renderPage();
      renderPagination();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };
    paginationContainer.appendChild(pageBtn);
  }
  
  // Next button
  const nextBtn = document.createElement('button');
  nextBtn.innerHTML = '<i class="bi bi-chevron-right"></i>';
  nextBtn.className = 'pagination-btn';
  nextBtn.disabled = currentPage === totalPages;
  nextBtn.onclick = () => {
    if (currentPage < totalPages) {
      currentPage++;
      renderPage();
      renderPagination();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };
  paginationContainer.appendChild(nextBtn);
}

// INITIAL RENDER
renderResults(tutorsData);

// Filter buttons
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});

// Rating filter
document.querySelectorAll('.rating-filter').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.rating-filter').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});

// Active filters state
let activeFilters = {
  jenjang: 'Semua',
  mapel: '',
  rating: 0,
  maxPrice: 500,
  search: '',
  sort: 'recommended'
};

// Apply all filters
function applyFilters() {
  let filtered = [...tutorsData];
  
  // Filter by jenjang
  if (activeFilters.jenjang !== 'Semua') {
    filtered = filtered.filter(t => {
      // Check if tutor teaches this jenjang (support both string and array)
      if (Array.isArray(t.jenjang_list)) {
        return t.jenjang_list.includes(activeFilters.jenjang);
      }
      return t.jenjang === activeFilters.jenjang || (t.jenjang && t.jenjang.includes(activeFilters.jenjang));
    });
  }
  
  // Filter by mapel
  if (activeFilters.mapel) {
    filtered = filtered.filter(t => {
      const mapel = t.mapel || '';
      return mapel.toLowerCase().includes(activeFilters.mapel.toLowerCase());
    });
  }
  
  // Filter by rating
  if (activeFilters.rating > 0) {
    filtered = filtered.filter(t => t.rating >= activeFilters.rating);
  }
  
  // Filter by price
  if (activeFilters.maxPrice < 500) {
    filtered = filtered.filter(t => (t.harga / 1000) <= activeFilters.maxPrice);
  }
  
  // Filter by search
  if (activeFilters.search) {
    const q = activeFilters.search.toLowerCase();
    filtered = filtered.filter(t =>
      t.nama.toLowerCase().includes(q) || 
      (t.mapel && t.mapel.toLowerCase().includes(q))
    );
  }
  
  // Sort
  switch(activeFilters.sort) {
    case 'rating':
      filtered.sort((a, b) => b.rating - a.rating);
      break;
    case 'price-low':
      filtered.sort((a, b) => a.harga - b.harga);
      break;
    case 'price-high':
      filtered.sort((a, b) => b.harga - a.harga);
      break;
  }
  
  renderResults(filtered);
}

// Jenjang filter buttons
document.querySelectorAll('.jenjang-filter').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.jenjang-filter').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    activeFilters.jenjang = this.dataset.jenjang;
    applyFilters();
  });
});

// Rating filter buttons
document.querySelectorAll('.rating-filter').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.rating-filter').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    activeFilters.rating = parseFloat(this.dataset.rating);
    applyFilters();
  });
});

// Mapel filter dropdown
document.getElementById('mapelFilter').addEventListener('change', function() {
  activeFilters.mapel = this.value;
  applyFilters();
});

// Price range
const priceRangeInput = document.getElementById('priceRange');
const priceLabel = document.getElementById('priceLabel');
priceRangeInput.addEventListener('input', function() {
  const value = parseInt(this.value);
  priceLabel.textContent = value + 'k';
  activeFilters.maxPrice = value;
  applyFilters();
});

// Sort
document.getElementById('sortBy').addEventListener('change', function() {
  activeFilters.sort = this.value;
  applyFilters();
});

// Live search
document.getElementById('searchInput').addEventListener('input', function() {
  activeFilters.search = this.value.trim();
  applyFilters();
});

function resetFilters() {
  // Reset state
  activeFilters = {
    jenjang: 'Semua',
    mapel: '',
    rating: 0,
    maxPrice: 500,
    search: '',
    sort: 'recommended'
  };
  
  // Reset UI
  document.getElementById('searchInput').value = '';
  document.getElementById('priceRange').value = 500;
  document.getElementById('priceLabel').textContent = '500k';
  document.getElementById('sortBy').value = 'recommended';
  document.getElementById('mapelFilter').value = '';
  
  document.querySelectorAll('.jenjang-filter').forEach(b => b.classList.remove('active'));
  document.querySelector('.jenjang-filter[data-jenjang="Semua"]').classList.add('active');
  
  document.querySelectorAll('.rating-filter').forEach(b => b.classList.remove('active'));
  document.querySelector('.rating-filter[data-rating="0"]').classList.add('active');
  
  renderResults(tutorsData);
}
</script>

<style>
/* Pagination Styles */
.pagination-btn {
  min-width: 44px;
  height: 44px;
  border: none;
  background: white;
  border-radius: 50%;
  font-weight: 600;
  font-size: 15px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.pagination-btn:hover:not(:disabled) {
  background: #FFF5F0;
  color: #FF6B35;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255,107,53,0.2);
}

.pagination-btn.active {
  background: linear-gradient(135deg, #FF6B35, #F7931E);
  color: white;
  box-shadow: 0 4px 12px rgba(255,107,53,0.3);
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Filter Pill Styles */
.filter-pill {
  padding: 8px 20px;
  border: 1px solid #cbd5e1;
  background: white;
  border-radius: 20px;
  font-weight: 600;
  font-size: 14px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.filter-pill:hover {
  border-color: #FF6B35;
  background: #FFF5F0;
  color: #FF6B35;
  transform: translateY(-1px);
}

.filter-pill.active {
  background: #FF6B35;
  color: white;
  border-color: #FF6B35;
}

.rating-pill {
  padding: 8px 16px;
  border: 1px solid #FDE68A;
  background: #FEF3C7;
  border-radius: 20px;
  font-weight: 600;
  font-size: 14px;
  color: #92400E;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.rating-pill:hover {
  background: #FDE047;
  border-color: #FDE047;
  color: #713F12;
  transform: translateY(-1px);
}

.rating-pill.active {
  background: #FDE047;
  color: #713F12;
  border-color: #FDE047;
}

.filter-btn {
  padding: 6px 16px;
  border: 2px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  font-weight: 600;
  font-size: 13px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.filter-btn:hover {
  border-color: #FF6B35;
  color: #FF6B35;
}

.filter-btn.active {
  background: #FF6B35;
  color: white;
  border-color: #FF6B35;
}

.rating-filter {
  padding: 6px 14px;
  border: 2px solid #e2e8f0;
  background: white;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.rating-filter:hover {
  border-color: #FEF3C7;
  background: #FEF3C7;
  color: #D97706;
  transform: translateY(-1px);
}

.rating-filter.active {
  background: #FEF3C7;
  color: #D97706;
  border-color: #FEF3C7;
}

/* Range slider styling */
#priceRange {
  -webkit-appearance: none;
  appearance: none;
  height: 6px;
  background: #e2e8f0;
  border-radius: 3px;
  outline: none;
}

#priceRange::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 18px;
  height: 18px;
  background: #FF6B35;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s;
}

#priceRange::-webkit-slider-thumb:hover {
  transform: scale(1.2);
}

#priceRange::-moz-range-thumb {
  width: 18px;
  height: 18px;
  background: #FF6B35;
  border-radius: 50%;
  cursor: pointer;
  border: none;
}
</style>

<?php if ($isLoggedIn && $userRole == 'learner' && $siswa_data): ?>
<script>
// Dropdown toggle function for learner navbar
function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
window.onclick = function(event) {
    if (!event.target.matches('.profile-trigger, .profile-trigger *')) {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
}
</script>
</body>
</html>
<?php else: ?>
<?php endif; ?>
