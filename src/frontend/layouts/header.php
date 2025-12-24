<?php 
if (!isset($assetPath)) {
  $assetPath = "../../../assets/";
}

if (!isset($logoPath)) {
  $logoPath = "/src/assets/img/logo.png";
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$isLoggedIn = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'];
$userName = $_SESSION['user_name'] ?? '';
$userRole = $_SESSION['user_role'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>PeerLearn</title>

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    rel="stylesheet">

  <link rel="stylesheet" href="<?php echo $assetPath ?>css/style.css">
</head>

<body>

<nav class="sb-navbar">
  <div class="sb-nav-container">

    <div class="sb-brand">
      <img src="<?php echo $logoPath; ?>" alt="PeerLearn Logo" class="logo">
      <span>PeerLearn</span>
    </div>

    <ul class="sb-menu">
      <?php if ($isLoggedIn && $userRole === 'learner'): ?>
        <li><a href="../learner/dashboard_siswa.php">Beranda</a></li>
        <li><a href="../public/search_result.php">Cari Tutor</a></li>
        <li><a href="../learner/sesi_saya.php">Sesi Saya</a></li>
        <li><a href="../learner/riwayat.php">Riwayat Booking</a></li>
      <?php elseif ($isLoggedIn && $userRole === 'tutor'): ?>
        <li><a href="../tutor/dashboard_tutor.php">Beranda</a></li>
        <li><a href="../public/search_result.php">Cari Tutor</a></li>
        <li><a href="#kelas-saya">Kelas Saya</a></li>
      <?php else: ?>
        <li><a href="../public/landing_page.php">Beranda</a></li>
        <li><a href="../public/search_result.php">Cari Tutor</a></li>
        <li><a href="../public/categories.php">Kategori</a></li>
        <li><a href="../public/testimoni.php">Testimoni</a></li>
      <?php endif; ?>
    </ul>

    <div style="display: flex; gap: 10px; align-items: center;">
      <?php if ($isLoggedIn): ?>
        <?php if ($userRole === 'tutor'): ?>
          <a href="../tutor/dashboard_tutor.php" class="sb-login" style="background: linear-gradient(135deg, #ff6b35, #ff9329); color: white; padding: 8px 20px; border-radius: 20px; text-decoration: none;">
            <i class="bi bi-person-workspace"></i> Dashboard Tutor
          </a>
        <?php endif; ?>
        <div style="position: relative;">
          <button onclick="toggleDropdown()" class="sb-daftar" style="display: flex; align-items: center; gap: 8px; cursor: pointer; border: none; background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%); color: white;">
            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($userName); ?>
          </button>
          <div id="userDropdown" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 200px; z-index: 1000;">
            <?php if ($userRole === 'learner'): ?>
              <div style="padding: 12px 16px; border-bottom: 1px solid #eee;">
                <p style="margin: 0; font-weight: 600; color: #333;"><?php echo htmlspecialchars($userName); ?></p>
              </div>
              <a href="../learner/profil.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
                <i class="bi bi-person"></i> Profil Saya
              </a>
              <a href="../learner/sesi_saya.php" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
                <i class="bi bi-calendar-check"></i> Sesi Belajar
              </a>
            <?php else: ?>
              <a href="#profil" style="display: block; padding: 12px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee;">
                <i class="bi bi-person"></i> Profil Saya
              </a>
            <?php endif; ?>
            <a href="../../../backend/auth/logout.php" style="display: block; padding: 12px 16px; color: #dc3545; text-decoration: none;">
              <i class="bi bi-box-arrow-right"></i> Logout
            </a>
          </div>
        </div>
      <?php else: ?>
        <a href="../auth/login.php" class="sb-login">Masuk</a>
        <a href="../auth/register.php" class="sb-daftar">Daftar</a>
      <?php endif; ?>
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
