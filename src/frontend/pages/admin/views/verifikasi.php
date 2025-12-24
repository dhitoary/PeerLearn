<?php
global $conn;

$pendingQuery = "SELECT t.*, u.status as user_status, u.created_at as register_date, u.name as user_name
                 FROM users u
                 INNER JOIN tutor t ON u.email = t.email
                 WHERE u.role = 'tutor' 
                 AND u.status = 'pending'
                 ORDER BY u.created_at DESC";
$pendingResult = mysqli_query($conn, $pendingQuery);
$totalPending = mysqli_num_rows($pendingResult);

$historyQuery = "SELECT t.*, u.status as user_status, u.created_at as register_date, u.name as user_name,
                 CASE 
                     WHEN u.status = 'active' AND t.status = 'Aktif' THEN 'approved'
                     WHEN u.status = 'banned' THEN 'rejected'
                     ELSE 'unknown'
                 END as decision_status
                 FROM users u
                 INNER JOIN tutor t ON u.email = t.email
                 WHERE u.role = 'tutor'
                 AND (u.status = 'active' OR u.status = 'banned')
                 ORDER BY u.created_at DESC
                 LIMIT 20";
$historyResult = mysqli_query($conn, $historyQuery);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1 fw-bold" style="color: #cc5500;"><i class="fas fa-shield-alt me-2"></i>Verifikasi Akun Tutor</h2>
        <p class="text-muted mb-0">Kelola persetujuan akun tutor yang mendaftar</p>
    </div>
</div>

<ul class="nav nav-tabs border-0 mb-4" id="verifTabs" role="tablist" style="gap: 10px;">
  <li class="nav-item">
    <button class="nav-link active fw-bold border-0 rounded-pill shadow-sm" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-content" type="button" 
            style="background: linear-gradient(135deg, #FF6B35 0%, #FF8C61 100%); color: white;">
        <i class="fas fa-clock me-2"></i>Menunggu Review <span class="badge bg-white text-danger ms-2" id="count-pending"><?= $totalPending ?></span>
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link border-0 rounded-pill" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-content" type="button" 
            style="background: #f8f9fa; color: #cc5500;">
        <i class="fas fa-history me-2"></i>Riwayat Keputusan
    </button>
  </li>
</ul>

<style>
    .nav-tabs .nav-link:not(.active):hover {
        background: linear-gradient(135deg, #ff9329 0%, #ffd4c1 100%) !important;
        color: #cc5500 !important;
    }
</style>

<div class="tab-content">
    
    <div class="tab-pane fade show active" id="pending-content">
        <div class="row" id="pending-list">
            
            <?php if ($totalPending == 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Tidak ada tutor yang menunggu verifikasi.
                    </div>
                </div>
            <?php else: ?>
                <?php 
                $counter = 1;
                while($tutor = mysqli_fetch_assoc($pendingResult)): 
                    $timeAgo = time() - strtotime($tutor['register_date']);
                    if ($timeAgo < 3600) {
                        $timeText = floor($timeAgo / 60) . ' menit lalu';
                    } else if ($timeAgo < 86400) {
                        $timeText = floor($timeAgo / 3600) . ' jam lalu';
                    } else {
                        $timeText = floor($timeAgo / 86400) . ' hari lalu';
                    }
                    
                    $regId = 'REG-' . date('Y') . '-' . str_pad($tutor['id'], 3, '0', STR_PAD_LEFT);
                    $initials = strtoupper(substr($tutor['nama_lengkap'], 0, 1));
                ?>
                <div class="col-md-6 mb-4" id="card-tutor-<?= $tutor['id'] ?>">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-warning text-dark me-2">Pending</span>
                                <span class="badge bg-light text-dark border">ID: <?= $regId ?></span>
                            </div>
                            <small class="text-muted"><i class="far fa-clock me-1"></i> <?= $timeText ?></small>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($tutor['nama_lengkap']) ?>&background=random" class="rounded-circle me-3" width="60">
                                <div>
                                    <h5 class="mb-1 fw-bold"><?= htmlspecialchars($tutor['nama_lengkap']) ?></h5>
                                    <p class="text-muted mb-0 small">Mendaftar sebagai: <strong>Tutor <?= htmlspecialchars($tutor['keahlian']) ?></strong></p>
                                    <?php if ($tutor['pendidikan']): ?>
                                        <small class="text-secondary"><i class="fas fa-university me-1"></i> <?= htmlspecialchars($tutor['pendidikan']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <h6 class="fw-bold text-uppercase small text-muted mb-3">Informasi Tutor</h6>
                            <div class="small mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Email:</span>
                                    <span class="fw-bold"><?= htmlspecialchars($tutor['email']) ?></span>
                                </div>
                                <?php if ($tutor['telepon']): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Telepon:</span>
                                    <span class="fw-bold"><?= htmlspecialchars($tutor['telepon']) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Pengalaman:</span>
                                    <span class="fw-bold"><?= $tutor['pengalaman_mengajar'] ?? 0 ?> Tahun</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Harga:</span>
                                    <span class="fw-bold">Rp <?= number_format($tutor['harga_per_sesi'] ?? 0, 0, ',', '.') ?>/sesi</span>
                                </div>
                            </div>

                            <?php if ($tutor['deskripsi']): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold text-uppercase small text-muted mb-2">Deskripsi</h6>
                                <p class="small text-secondary"><?= nl2br(htmlspecialchars($tutor['deskripsi'])) ?></p>
                            </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2 d-md-flex">
                                <button class="btn btn-success flex-grow-1 py-2" onclick="verifyTutor(<?= $tutor['id'] ?>, '<?= addslashes($tutor['nama_lengkap']) ?>', 'approve')">
                                    <i class="fas fa-check-circle me-2"></i> Terima
                                </button>
                                <button class="btn btn-outline-danger flex-grow-1 py-2" onclick="verifyTutor(<?= $tutor['id'] ?>, '<?= addslashes($tutor['nama_lengkap']) ?>', 'reject')">
                                    <i class="fas fa-times-circle me-2"></i> Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                $counter++;
                endwhile; 
                ?>
            <?php endif; ?>

        </div>
    </div>
    
    <!-- Riwayat Tab -->
    <div class="tab-pane fade" id="history-content">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #ff9329 !important; border-radius: 12px;">
            <div class="card-header py-3" style="background: linear-gradient(135deg, rgba(255, 147, 41, 0.15) 0%, rgba(255, 184, 102, 0.15) 100%);">
                <h5 class="mb-0 fw-bold" style="color: #cc5500;">
                    <i class="fas fa-history me-2"></i>Riwayat Verifikasi
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (mysqli_num_rows($historyResult) == 0): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Belum ada riwayat verifikasi.</p>
                        </div>
                    <?php else: ?>
                        <?php while($history = mysqli_fetch_assoc($historyResult)): 
                            $isApproved = ($history['decision_status'] == 'approved');
                            $statusBadge = $isApproved ? 
                                '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Disetujui</span>' : 
                                '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Ditolak</span>';
                            $dateFormatted = date('d M Y, H:i', strtotime($history['register_date']));
                            
                            // Time ago
                            $registerTimestamp = strtotime($history['register_date']);
                            $currentTimestamp = time();
                            $timeAgo = abs($currentTimestamp - $registerTimestamp); 
                            
                            if ($timeAgo < 60) {
                                $timeText = 'Baru saja';
                            } else if ($timeAgo < 3600) {
                                $timeText = floor($timeAgo / 60) . ' menit lalu';
                            } else if ($timeAgo < 86400) {
                                $timeText = floor($timeAgo / 3600) . ' jam lalu';
                            } else if ($timeAgo < 604800) {
                                $timeText = floor($timeAgo / 86400) . ' hari lalu';
                            } else if ($timeAgo < 2592000) {
                                $timeText = floor($timeAgo / 604800) . ' minggu lalu';
                            } else {
                                $timeText = floor($timeAgo / 2592000) . ' bulan lalu';
                            }
                        ?>
                        <div class="list-group-item border-0 border-bottom py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($history['nama_lengkap']) ?>&background=random" class="rounded-circle me-3 shadow-sm" width="50" height="50">
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($history['nama_lengkap']) ?></h6>
                                        <div class="small text-muted">
                                            <i class="fas fa-graduation-cap me-1"></i>Tutor <?= htmlspecialchars($history['keahlian']) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="far fa-clock me-1"></i><?= $timeText ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <?= $statusBadge ?>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function verifyTutor(tutorId, nama, action) {
        let confirmColor = action === 'approve' ? '#198754' : '#dc3545';
        let confirmText = action === 'approve' ? 'Ya, Aktifkan Akun' : 'Ya, Tolak Pengajuan';
        let title = action === 'approve' ? 'Terima Tutor' : 'Tolak Tutor';
        let message = action === 'approve' ? 
            `Anda akan mengaktifkan akun <b>${nama}</b> sebagai tutor.` : 
            `Anda akan menolak pengajuan <b>${nama}</b>.`;

        Swal.fire({
            title: title,
            html: message + '<br>Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            confirmButtonText: confirmText,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    html: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send AJAX request to backend
                const formData = new FormData();
                formData.append('tutor_id', tutorId);
                formData.append('action', action);

                fetch('../../../backend/admin/verify_tutor.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    
                    if (data.success) {
                        const cardElement = document.getElementById('card-tutor-' + tutorId);
                        cardElement.style.transition = "all 0.5s ease";
                        cardElement.style.transform = "translateX(100px)";
                        cardElement.style.opacity = "0";
                        
                        setTimeout(() => {
                            cardElement.remove();
                            updateBadgeCount();
                            
                            // Check if no more pending
                            if (document.querySelectorAll('[id^="card-tutor-"]').length === 0) {
                                document.getElementById('pending-list').innerHTML = `
                                    <div class="col-12">
                                        <div class="alert alert-success text-center p-5">
                                            <div class="mb-3"><i class="fas fa-check-double fa-3x text-success opacity-50"></i></div>
                                            <h5 class="fw-bold">Semua Beres!</h5>
                                            <p class="text-muted">Tidak ada permintaan verifikasi baru saat ini.</p>
                                        </div>
                                    </div>
                                `;
                            }
                        }, 500);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Terjadi kesalahan'
                        });
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan sistem: ' + error.message
                    });
                });
            }
        });
    }

    function updateBadgeCount() {
        let badge = document.getElementById('count-pending');
        let count = parseInt(badge.innerText) - 1;
        badge.innerText = count > 0 ? count : 0;
        
        if(count <= 0) {
            badge.style.display = 'none';
        }
    }
</script>