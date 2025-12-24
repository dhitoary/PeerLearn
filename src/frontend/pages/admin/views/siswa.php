<?php
global $conn; 

if (!$conn) {
    echo "<div class='alert alert-danger'>Koneksi database gagal!</div>";
    exit;
}

$query = "SELECT * FROM siswa ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
    <div>
        <h2 class="mb-1 fw-bold" style="color: #cc5500;"><i class="fas fa-user-graduate me-2"></i>Data Siswa (Murid)</h2>
        <p class="text-muted mb-0">Kelola data siswa yang terdaftar</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm rounded-pill shadow-sm" onclick="window.print()" 
                style="background: linear-gradient(135deg, #cc5500 0%, #0A5A70 100%); color: white; border: none;">
            <i class="fas fa-download me-1"></i>Export Data
        </button>
    </div>
</div>

<div class="row mb-4 no-print g-3">
    <div class="col-md-3">
        <select id="filterJenjang" class="form-select border-0 shadow-sm" onchange="filterSiswa()" 
                style="background: linear-gradient(135deg, rgba(255, 147, 41, 0.15) 0%, rgba(255, 184, 102, 0.15) 100%);">
            <option value="">🎓 Semua Jenjang</option>
            <option value="SD">📚 SD</option>
            <option value="SMP">📖 SMP</option>
            <option value="SMA">🎯 SMA</option>
        </select>
    </div>
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text border-0 shadow-sm" style="background: linear-gradient(135deg, #cc5500 0%, #0A5A70 100%); color: white;">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" id="searchSiswa" class="form-control border-0 shadow-sm" placeholder="Cari nama atau email siswa..." onkeyup="filterSiswa()">
        </div>
    </div>
</div>

<div class="card shadow border-0" style="border-left: 5px solid #cc5500 !important; border-radius: 12px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tableSiswa">
            <thead style="background: linear-gradient(135deg, #ff9329 0%, #ffd4c1 100%); color: #cc5500;">
                <tr>
                    <th class="ps-4">Nama Siswa</th>
                    <th>Jenjang</th>
                    <th>Sekolah</th>
                    <th>Kelas</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                
                <?php 
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $badgeColor = 'primary'; 
                        if($row['jenjang'] == 'SD') $badgeColor = 'info text-dark';
                        if($row['jenjang'] == 'SMP') $badgeColor = 'warning text-dark';

                        $statusColor = 'success';
                        if($row['status'] == 'Cuti') $statusColor = 'warning text-dark';
                        if($row['status'] == 'Non-Aktif') $statusColor = 'secondary';
                ?>
                
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['nama_lengkap']) ?>&background=random" class="rounded-circle me-3" width="35">
                            <div>
                                <div class="fw-bold nama-col"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-<?= $badgeColor ?> jenjang-col"><?= $row['jenjang'] ?></span></td>
                    <td><?= htmlspecialchars($row['sekolah']) ?></td>
                    <td><?= htmlspecialchars($row['kelas']) ?></td>
                    <td><span class="badge bg-<?= $statusColor ?>"><?= $row['status'] ?></span></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-light text-info" 
                                onclick="showDetailSiswa(
                                    '<?= addslashes($row['nama_lengkap']) ?>', 
                                    '<?= $row['jenjang'] ?>', 
                                    '<?= addslashes($row['sekolah']) ?>', 
                                    '<?= addslashes($row['kelas']) ?>', 
                                    '<?= addslashes($row['minat']) ?>'
                                )">
                            <i class="fas fa-eye"></i>
                        </button>
                        
                        <button class="btn btn-sm btn-light text-danger" onclick="confirmAction('Hapus siswa ini?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>

                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='6' class='text-center py-4'>Belum ada data siswa di database.</td></tr>";
                }
                ?>

            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalDetailSiswa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Info Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="d_img" src="" class="rounded-circle shadow-sm" width="80">
                    <h4 id="d_nama" class="mt-2 fw-bold"></h4>
                    <span id="d_jenjang" class="badge bg-primary"></span>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><span>Sekolah</span><strong id="d_sekolah"></strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Kelas</span><strong id="d_kelas"></strong></li>
                    <li class="list-group-item">
                        <small class="text-muted d-block">Minat Belajar:</small>
                        <p id="d_minat" class="mb-0 fw-bold text-dark"></p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    function showDetailSiswa(nama, jenjang, sekolah, kelas, minat) {
        document.getElementById('d_nama').innerText = nama;
        document.getElementById('d_jenjang').innerText = jenjang;
        document.getElementById('d_sekolah').innerText = sekolah;
        document.getElementById('d_kelas').innerText = kelas;
        document.getElementById('d_minat').innerText = minat;
        document.getElementById('d_img').src = "https://ui-avatars.com/api/?name=" + encodeURIComponent(nama) + "&background=random";
        new bootstrap.Modal(document.getElementById('modalDetailSiswa')).show();
    }

    function filterSiswa() {
        let keyword = document.getElementById('searchSiswa').value.toLowerCase();
        let jenjang = document.getElementById('filterJenjang').value;
        let table = document.getElementById('tableSiswa');
        let rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            let row = rows[i];
            let namaEl = row.querySelector('.nama-col');
            let jgEl = row.querySelector('.jenjang-col');

            if (namaEl && jgEl) {
                let nama = namaEl.textContent.toLowerCase();
                let jg = jgEl.textContent.trim();
                let email = row.querySelector('.text-muted') ? row.querySelector('.text-muted').textContent.toLowerCase() : '';
                
                let matchSearch = nama.includes(keyword) || email.includes(keyword);
                let matchJenjang = jenjang === "" || jg === jenjang;
                
                row.style.display = (matchSearch && matchJenjang) ? "" : "none";
            }
        }
    }
</script>