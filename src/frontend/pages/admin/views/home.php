<?php
global $conn;

$qSiswa = mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa");
$totalSiswa = mysqli_fetch_assoc($qSiswa)['total'];

$qTutor = mysqli_query($conn, "SELECT COUNT(*) as total FROM tutor");
$totalTutor = mysqli_fetch_assoc($qTutor)['total'];

$qPending = mysqli_query($conn, "SELECT COUNT(*) as total FROM tutor WHERE status = 'Pending'");
$totalPending = mysqli_fetch_assoc($qPending)['total'];

$qKelasAktif = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE status IN ('confirmed', 'pending')");
$totalKelas = mysqli_fetch_assoc($qKelasAktif)['total'];

$chartQuery = mysqli_query($conn, "SELECT keahlian, COUNT(*) as jumlah FROM tutor WHERE status = 'Aktif' GROUP BY keahlian ORDER BY jumlah DESC LIMIT 8");

$labels = [];
$dataChart = [];

while($row = mysqli_fetch_assoc($chartQuery)) {
    $labels[] = $row['keahlian'];
    $dataChart[] = $row['jumlah'];
}

$jsonLabels = json_encode($labels);
$jsonData = json_encode($dataChart);

$monthlyDataSiswa = [];
$monthlyDataTutor = [];
$monthLabels = [];

for ($i = 11; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $monthName = date('M', strtotime("-$i months"));
    $monthLabels[] = $monthName;
    
    $qSiswaMonth = mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa WHERE DATE_FORMAT(created_at, '%Y-%m') = '$date'");
    $monthlyDataSiswa[] = (int)mysqli_fetch_assoc($qSiswaMonth)['total'];
    
    $qTutorMonth = mysqli_query($conn, "SELECT COUNT(*) as total FROM tutor WHERE DATE_FORMAT(created_at, '%Y-%m') = '$date'");
    $monthlyDataTutor[] = (int)mysqli_fetch_assoc($qTutorMonth)['total'];
}

$jsonMonthLabels = json_encode($monthLabels);
$jsonMonthlySiswa = json_encode($monthlyDataSiswa);
$jsonMonthlyTutor = json_encode($monthlyDataTutor);

$qBookingStatus = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM bookings GROUP BY status");
$bookingStatus = [];
while($row = mysqli_fetch_assoc($qBookingStatus)) {
    $bookingStatus[$row['status']] = (int)$row['total'];
}
$jsonBookingStatus = json_encode($bookingStatus);

$logQuery = "SELECT id, nama_lengkap, email, role, created_at 
             FROM users 
             WHERE role IN ('learner', 'tutor')
             ORDER BY created_at DESC 
             LIMIT 10";
$logResult = mysqli_query($conn, $logQuery);
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #cc5500 0%, #0A5A70 100%); color: white;">
            <div class="card-body d-flex align-items-center">
                <div class="bg-white bg-opacity-20 p-3 rounded-circle me-3">
                    <i class="fas fa-user-graduate fa-2x text-white"></i>
                </div>
                <div>
                    <h6 class="mb-1 small text-uppercase fw-bold opacity-75">Total Siswa</h6>
                    <h2 class="mb-0 fw-bold"><?= $totalSiswa ?></h2>
                    <small class="opacity-75"><i class="fas fa-check-circle"></i> Data Realtime</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ff9329 0%, #ffd4c1 100%);">
            <div class="card-body d-flex align-items-center">
                <div class="bg-white bg-opacity-50 p-3 rounded-circle me-3" style="color: #cc5500;">
                    <i class="fas fa-chalkboard-teacher fa-2x"></i>
                </div>
                <div style="color: #cc5500;">
                    <h6 class="mb-1 small text-uppercase fw-bold opacity-75">Total Tutor</h6>
                    <h2 class="mb-0 fw-bold"><?= $totalTutor ?></h2>
                    <small class="opacity-75"><i class="fas fa-check-circle"></i> Data Realtime</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ffb866 0%, #F9E79F 100%);">
            <div class="card-body d-flex align-items-center">
                <div class="bg-white bg-opacity-50 p-3 rounded-circle me-3" style="color: #856404;">
                    <i class="fas fa-book-open fa-2x"></i>
                </div>
                <div style="color: #856404;">
                    <h6 class="mb-1 small text-uppercase fw-bold opacity-75">Kelas Aktif</h6>
                    <h2 class="mb-0 fw-bold"><?= $totalKelas ?></h2>
                    <small class="opacity-75">Sedang berjalan</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #FF6B35 0%, #FF8C61 100%); color: white;">
            <div class="card-body d-flex align-items-center">
                <div class="bg-white bg-opacity-20 p-3 rounded-circle me-3">
                    <i class="fas fa-bell fa-2x text-white"></i>
                </div>
                <div>
                    <h6 class="mb-1 small text-uppercase fw-bold opacity-75">Perlu Verifikasi</h6>
                    <h2 class="mb-0 fw-bold"><?= $totalPending ?></h2>
                    <small class="opacity-75 fw-bold">Butuh Tindakan</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #cc5500 !important;">
            <div class="card-header py-3" style="background: linear-gradient(135deg, #cc5500 0%, #0A5A70 100%); color: white;">
                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-chart-line me-2"></i>Tren Pendaftaran (12 Bulan Terakhir)</h5>
            </div>
            <div class="card-body">
                <canvas id="registrationChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ff9329 !important;">
            <div class="card-header py-3" style="background: linear-gradient(135deg, #ff9329 0%, #ffd4c1 100%); color: #cc5500;">
                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-graduation-cap me-2"></i>Sebaran Keahlian Tutor</h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="width: 100%; max-height: 280px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-left: 4px solid #ffb866 !important;">
    <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, rgba(255, 147, 41, 0.15) 0%, rgba(255, 184, 102, 0.15) 100%);">
    <h5 class="mb-0 fw-bold" style="color: #cc5500;"><i class="fas fa-history me-2"></i>Pendaftaran Terbaru</h5>
    
    <div class="dropdown">
        <button class="btn btn-sm rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" 
                style="background: #cc5500; color: white; border: none;">
            <i class="fas fa-eye me-1"></i>Lihat Semua
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
            <li><h6 class="dropdown-header text-uppercase small" style="color: #cc5500;">Pilih Data</h6></li>
            <li>
                <a class="dropdown-item" href="?page=siswa">
                    <i class="fas fa-user-graduate me-2" style="color: #cc5500;"></i> Data Siswa
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="?page=tutor">
                    <i class="fas fa-chalkboard-teacher me-2" style="color: #ff9329;"></i> Data Tutor
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="?page=verifikasi">
                    <i class="fas fa-check-circle me-2" style="color: #FF6B35;"></i> Cek Verifikasi
                </a>
            </li>
        </ul>
    </div>
</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Aktivitas</th>
                    <th>Waktu Daftar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($logResult) > 0): ?>
                    <?php while($log = mysqli_fetch_assoc($logResult)): 
                        // Hitung waktu lalu
                        $createdTime = strtotime($log['created_at']);
                        $now = time();
                        $diff = $now - $createdTime;
                        $minutes = floor($diff / 60);
                        $hours = floor($diff / 3600);
                        $days = floor($diff / 86400);
                        
                        if ($minutes < 1) {
                            $timeAgo = 'Baru saja';
                        } elseif ($minutes < 60) {
                            $timeAgo = $minutes . ' menit lalu';
                        } elseif ($hours < 24) {
                            $timeAgo = $hours . ' jam lalu';
                        } else {
                            $timeAgo = $days . ' hari lalu';
                        }
                        
                        $statusClass = 'success';
                        $statusText = 'Aktif';
                        if ($log['role'] == 'tutor') {
                            $statusClass = 'info';
                            $statusText = 'Tutor';
                        } elseif ($log['role'] == 'learner') {
                            $statusClass = 'primary';
                            $statusText = 'Siswa';
                        }
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($log['nama_lengkap']) ?>&background=random" class="rounded-circle me-2" width="32" height="32">
                                <div>
                                    <strong><?= htmlspecialchars($log['nama_lengkap']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($log['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($log['role'] == 'learner'): ?>
                                <span class="badge bg-primary-subtle text-primary">
                                    <i class="fas fa-user-graduate me-1"></i> Siswa Baru
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-chalkboard-teacher me-1"></i> Tutor Baru
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small">
                            <i class="far fa-clock me-1"></i><?= $timeAgo ?>
                        </td>
                        <td><span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        Belum ada aktivitas registrasi
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const monthLabels = <?= $jsonMonthLabels ?>;
    const siswaData = <?= $jsonMonthlySiswa ?>;
    const tutorData = <?= $jsonMonthlyTutor ?>;
    
    const ctxReg = document.getElementById('registrationChart').getContext('2d');
    new Chart(ctxReg, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Siswa Baru',
                data: siswaData,
                borderColor: '#cc5500',
                backgroundColor: 'rgba(12, 74, 96, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#cc5500',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7
            },
            {
                label: 'Tutor Baru',
                data: tutorData,
                borderColor: '#FF6B35',
                backgroundColor: 'rgba(255, 107, 53, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#FF6B35',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 13,
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' orang';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });

    const dbLabels = <?= $jsonLabels ?>; 
    const dbData = <?= $jsonData ?>;

    const colors = [
        '#cc5500',
        '#ff9329',
        '#FF6B35',
        '#ffb866',
        '#4A90E2',
        '#50C878',
        '#9B59B6',
        '#E67E22'
    ];

    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: dbLabels,
            datasets: [{
                data: dbData,
                backgroundColor: colors.slice(0, dbLabels.length),
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12,
                            weight: '600'
                        },
                        usePointStyle: true,
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    return {
                                        text: label + ' (' + value + ')',
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' tutor (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
</script>