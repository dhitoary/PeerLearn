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
        <button type="button" class="btn btn-sm rounded-pill shadow-sm me-2" onclick="openAddSiswaModal()" 
                style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none;">
            <i class="fas fa-plus me-1"></i>Tambah Siswa
        </button>
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
                        
                        <button class="btn btn-sm btn-light text-primary" 
                                onclick="editSiswa(<?= $row['id'] ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <button class="btn btn-sm btn-light text-danger" 
                                onclick="deleteSiswa(<?= $row['id'] ?>)">
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

<!-- Modal Tambah/Edit Siswa -->
<div class="modal fade" id="modalFormSiswa" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #cc5500 0%, #0A5A70 100%); color: white;">
                <h5 class="modal-title" id="modalFormTitle"><i class="fas fa-user-plus me-2"></i>Tambah Siswa Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSiswa">
                <div class="modal-body">
                    <input type="hidden" id="siswa_id" name="id">
                    <input type="hidden" id="form_action" name="action" value="create">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="siswa_nama" name="nama_lengkap" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="siswa_email" name="email" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jenjang <span class="text-danger">*</span></label>
                            <select class="form-select" id="siswa_jenjang" name="jenjang" required>
                                <option value="">Pilih Jenjang</option>
                                <option value="SD">SD</option>
                                <option value="SMP">SMP</option>
                                <option value="SMA">SMA</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="siswa_kelas" name="kelas" placeholder="Contoh: 10 IPA" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" id="siswa_status" name="status">
                                <option value="Aktif">Aktif</option>
                                <option value="Cuti">Cuti</option>
                                <option value="Non-Aktif">Non-Aktif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Sekolah <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="siswa_sekolah" name="sekolah" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Minat Belajar</label>
                            <textarea class="form-control" id="siswa_minat" name="minat" rows="3" placeholder="Mata pelajaran yang diminati..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                        <i class="fas fa-save me-1"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
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
    // Fungsi membuka modal tambah siswa
    function openAddSiswaModal() {
        document.getElementById('modalFormTitle').innerHTML = '<i class="fas fa-user-plus me-2"></i>Tambah Siswa Baru';
        document.getElementById('formSiswa').reset();
        document.getElementById('siswa_id').value = '';
        document.getElementById('form_action').value = 'create';
        new bootstrap.Modal(document.getElementById('modalFormSiswa')).show();
    }

    // Fungsi edit siswa
    function editSiswa(id) {
        fetch(`../../../backend/admin/crud_siswa.php?action=read&id=${id}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    document.getElementById('modalFormTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Data Siswa';
                    document.getElementById('siswa_id').value = data.id;
                    document.getElementById('siswa_nama').value = data.nama_lengkap;
                    document.getElementById('siswa_email').value = data.email;
                    document.getElementById('siswa_jenjang').value = data.jenjang;
                    document.getElementById('siswa_kelas').value = data.kelas;
                    document.getElementById('siswa_sekolah').value = data.sekolah;
                    document.getElementById('siswa_minat').value = data.minat;
                    document.getElementById('siswa_status').value = data.status;
                    document.getElementById('form_action').value = 'update';
                    new bootstrap.Modal(document.getElementById('modalFormSiswa')).show();
                } else {
                    showToast(result.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat mengambil data', 'error');
            });
    }

    // Fungsi hapus siswa
    function deleteSiswa(id) {
        Swal.fire({
            title: 'Hapus Siswa?',
            text: "Data siswa akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('../../../backend/admin/crud_siswa.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showToast(result.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(result.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat menghapus data', 'error');
                });
            }
        });
    }

    // Submit form siswa
    document.getElementById('formSiswa').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('../../../backend/admin/crud_siswa.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('modalFormSiswa')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat menyimpan data', 'error');
        });
    });

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