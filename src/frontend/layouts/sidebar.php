<?php 
if (!isset($assetPath)) {
  $assetPath = "../../assets/";
}
?>

<div class="sb-sidebar">
  
  <div class="sb-side-header">
    <img src="<?php echo $assetPath ?>img/logo.png" class="sb-side-logo">
    <span class="sb-side-title">PeerLearn</span>
  </div>

  <ul class="sb-side-menu">

    <li>
      <a href="../public/landing_page.php">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
    </li>

    <li>
      <a href="#" onclick="alert('Fitur sedang dalam pengembangan'); return false;">
        <i class="bi bi-megaphone"></i> Buat Iklan
      </a>
    </li>

    <li>
      <a href="#" onclick="alert('Fitur sedang dalam pengembangan'); return false;">
        <i class="bi bi-person"></i> Profil Saya
      </a>
    </li>

    <li>
      <a href="#" onclick="alert('Fitur sedang dalam pengembangan'); return false;">
        <i class="bi bi-chat-text"></i> Pesan
      </a>
    </li>

    <li>
      <a href="../auth/logout.php" class="sb-side-logout">
        <i class="bi bi-box-arrow-right"></i> Keluar
      </a>
    </li>

  </ul>

</div>
