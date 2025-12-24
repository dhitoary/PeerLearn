<?php
global $conn;
if (!$conn) { echo "<div class='alert alert-danger'>Koneksi database gagal!</div>"; exit; }

$query = "SELECT * FROM tutor ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
    <div>
        <h2 class="mb-1 fw-bold" style="color: #cc5500;"><i class="fas fa-chalkboard-teacher me-2"></i>Data Tutor (Pengajar)</h2>
        <p class="text-muted mb-0">Kelola data tutor yang terdaftar</p>
    </div>
    <button class="btn btn-sm rounded-pill shadow-sm" onclick="window.print()" 
            style="background: linear-gradient(135deg, #ff9329 0%, #ffd4c1 100%); color: #cc5500; border: none; font-weight: 600;">
        <i class="fas fa-download me-1"></i>Export Data
    </button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row g-2">
            <div class="col-md-3">
                <select id="filterKategori" class="form-select border-0 bg-light" onchange="filterTable()">
                    <option value="">Semua Keahlian</option>
                    <?php
                    // Ambil keahlian unik dari database
                    $keahlianQuery = mysqli_query($conn, "SELECT DISTINCT keahlian FROM tutor WHERE keahlian IS NOT NULL ORDER BY keahlian");
                    while($k = mysqli_fetch_assoc($keahlianQuery)) {
                        echo '<option value="'.htmlspecialchars($k['keahlian']).'">'.htmlspecialchars($k['keahlian']).'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterStatus" class="form-select border-0 bg-light" onchange="filterTable()">
                    <option value="">Semua Status</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Non-Aktif">Non-Aktif</option>
                    <option value="Cuti">Cuti</option>
                </select>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control border-0 bg-light" placeholder="Cari nama atau kampus..." onkeyup="filterTable()">
                    <button class="btn btn-light text-secondary"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow" style="border-left: 5px solid #ff9329 !important; border-radius: 12px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tutorTable">
                <thead style="background: linear-gradient(135deg, #cc5500 0%, #0A5A70 100%); color: white;">
                    <tr>
                        <th class="ps-4">Tutor</th>
                        <th>Keahlian</th>
                        <th>Kampus / Pendidikan</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <?php 
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            // Warna Badge Keahlian (Variasi)
                            $catColor = 'primary';
                            if($row['keahlian'] == 'Bahasa Inggris') $catColor = 'danger';
                            if($row['keahlian'] == 'Koding') $catColor = 'dark';
                            if($row['keahlian'] == 'Biologi') $catColor = 'success';

                            // Warna Status
                            $statColor = 'success';
                            if($row['status'] == 'Cuti') $statColor = 'warning text-dark';
                            if($row['status'] == 'Non-Aktif') $statColor = 'secondary';
                    ?>

                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['nama_lengkap']) ?>&background=random" class="rounded-circle me-3" width="40">
                                <div>
                                    <div class="fw-bold name-col"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-<?= $catColor ?> bg-opacity-10 text-<?= $catColor == 'dark' ? 'dark' : $catColor ?> category-col"><?= $row['keahlian'] ?></span></td>
                        <td><?= htmlspecialchars($row['pendidikan']) ?></td>
                        <td><span class="badge bg-<?= $statColor ?> status-col"><?= $row['status'] ?></span></td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-light text-info" 
                                onclick="showDetailTutor(
                                    '<?= addslashes($row['nama_lengkap']) ?>', 
                                    '<?= $row['email'] ?>', 
                                    '<?= $row['keahlian'] ?>', 
                                    '<?= $row['status'] ?>', 
                                    '<?= addslashes($row['pendidikan']) ?>'
                                )">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-light text-primary" onclick="editTutor('<?= addslashes($row['nama_lengkap']) ?>', '<?= $row['keahlian'] ?>', '<?= $row['status'] ?>')"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-light text-danger" onclick="confirmAction('Hapus tutor ini?')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>

                    <?php 
                        } 
                    } else {
                        echo "<tr><td colspan='5' class='text-center py-4'>Belum ada data tutor.</td></tr>";
                    }
                    ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTutor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Tutor Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTutor">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="inputNama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keahlian</label>
                        <select class="form-select" id="inputKategori">
                            <option value="Matematika">Matematika</option>
                            <option value="Bahasa Inggris">Bahasa Inggris</option>
                            <option value="Koding">Koding</option>
                            <option value="Fisika">Fisika</option>
                            <option value="Biologi">Biologi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="inputStatus">
                            <option value="Aktif">Aktif</option>
                            <option value="Non-Aktif">Non-Aktif</option>
                            <option value="Cuti">Cuti</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanTutor()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailTutor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="fas fa-id-card me-2"></i>Detail Tutor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <img id="detailImg" src="" class="rounded-circle shadow-sm mb-3" width="100">
                    <h4 id="detailNama" class="fw-bold mb-0"></h4>
                    <p id="detailEmail" class="text-muted"></p>
                    <span id="detailStatus" class="badge bg-success rounded-pill px-3"></span>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Keahlian</span>
                        <strong id="detailKategori"></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Pendidikan</span>
                        <strong id="detailEdu" class="text-end"></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">File CV</span>
                        <a href="#" class="text-decoration-none" onclick="event.preventDefault(); Swal.fire('Info', 'Ini simulasi download CV', 'info')">Download PDF</a>
                    </li>
                </ul>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>

    function showDetailTutor(nama, email, kategori, status, edukasi) {
        document.getElementById('detailNama').innerText = nama;
        document.getElementById('detailEmail').innerText = email;
        document.getElementById('detailKategori').innerText = kategori;
        document.getElementById('detailEdu').innerText = edukasi;
        document.getElementById('detailStatus').innerText = status;
        
        document.getElementById('detailImg').src = "https://ui-avatars.com/api/?name=" + encodeURIComponent(nama) + "&background=random&size=128";

        var myModal = new bootstrap.Modal(document.getElementById('modalDetailTutor'));
        myModal.show();
    }

    function filterTable() {
        let inputSearch = document.getElementById("searchInput").value.toLowerCase();
        let inputKategori = document.getElementById("filterKategori").value;
        let inputStatus = document.getElementById("filterStatus").value;
        
        let table = document.getElementById("tutorTable");
        let tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            let nameEl = tr[i].querySelector(".name-col");
            let catEl = tr[i].querySelector(".category-col");
            let statEl = tr[i].querySelector(".status-col");

            if(nameEl && catEl && statEl) {
                let tdName = nameEl.textContent.toLowerCase();
                let tdCat = catEl.textContent.trim();
                let tdStat = statEl.textContent.trim();
                let tdEmail = tr[i].querySelector('.small.text-muted') ? tr[i].querySelector('.small.text-muted').textContent.toLowerCase() : '';
                let tdKampus = tr[i].cells[2].textContent.toLowerCase(); 

                let matchSearch = tdName.includes(inputSearch) || tdKampus.includes(inputSearch) || tdEmail.includes(inputSearch);
                let matchKategori = inputKategori === "" || tdCat === inputKategori;
                let matchStatus = inputStatus === "" || tdStat === inputStatus;

                if (matchSearch && matchKategori && matchStatus) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    function editTutor(nama, kategori, status) {
        document.getElementById('modalTitle').innerText = "Edit Data Tutor";
        document.getElementById('inputNama').value = nama;
        document.getElementById('inputKategori').value = kategori;
        document.getElementById('inputStatus').value = status;
        new bootstrap.Modal(document.getElementById('modalTutor')).show();
    }

    function simpanTutor() {
        var modals = document.querySelectorAll('.modal');
        modals.forEach(modal => bootstrap.Modal.getInstance(modal)?.hide());
        showToast("Data berhasil disimpan!", "success");
    }
</script>