<div class="mb-4">
    <h2 class="mb-1 fw-bold" style="color: #cc5500;"><i class="fas fa-cog me-2"></i>Pengaturan Akun</h2>
    <p class="text-muted mb-0">Kelola informasi profil dan keamanan akun Anda</p>
</div>

<div class="row g-4">
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm text-center p-4" style="border-top: 4px solid #ff6b35; border-radius: 12px;">
            <div class="mb-3">
                <div class="position-relative d-inline-block">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name'] ?? 'Admin') ?>&size=128&background=ff6b35&color=fff" class="rounded-circle shadow">
                    <div class="position-absolute bottom-0 end-0 bg-success rounded-circle p-1" style="width: 30px; height: 30px;">
                        <i class="fas fa-check text-white"></i>
                    </div>
                </div>
            </div>
            <h5 class="fw-bold" style="color: #cc5500;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Super Admin') ?></h5>
            <p class="mb-1"><span class="badge rounded-pill" style="background: linear-gradient(135deg, #ffb899 0%, #ffd4c1 100%); color: #cc5500;">Administrator</span></p>
            <p class="text-muted small mb-3"><?= htmlspecialchars($_SESSION['user_email'] ?? 'admin@peerlearn.com') ?></p>
            <button class="btn btn-sm rounded-pill px-4 shadow-sm" disabled style="background: linear-gradient(135deg, rgba(255, 147, 41, 0.3) 0%, rgba(255, 184, 102, 0.3) 100%); color: #cc5500; border: 1px dashed #ffb866;">
                <i class="fas fa-camera me-1"></i>Upload Foto Baru
            </button>
            <small class="text-muted d-block mt-2">(Fitur akan datang)</small>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #ff9329; border-radius: 12px;">
            <div class="card-header py-3" style="background: linear-gradient(135deg, rgba(255, 147, 41, 0.15) 0%, rgba(255, 184, 102, 0.15) 100%);">
                <h5 class="mb-0 fw-bold" style="color: #cc5500;"><i class="fas fa-user-edit me-2"></i>Edit Informasi</h5>
            </div>
            <div class="card-body p-4">
                <form onsubmit="event.preventDefault(); showToast('Profil berhasil disimpan!');">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: #cc5500;">Nama Lengkap</label>
                        <input type="text" class="form-control border-0 shadow-sm" value="<?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>" style="background: #f8f9fa;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: #cc5500;">Email Address</label>
                        <input type="email" class="form-control border-0 shadow-sm" value="<?= htmlspecialchars($_SESSION['user_email'] ?? 'admin@peerlearn.com') ?>" readonly style="background: linear-gradient(135deg, rgba(255, 147, 41, 0.1) 0%, rgba(255, 184, 102, 0.1) 100%);">
                        <small class="text-muted"><i class="fas fa-lock me-1"></i>Email tidak dapat diubah</small>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3" style="color: #cc5500;"><i class="fas fa-key me-2"></i>Ganti Password</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control border-0 shadow-sm" style="background: #f8f9fa;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control border-0 shadow-sm" style="background: #f8f9fa;">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-light me-2 shadow-sm">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm" style="background: linear-gradient(135deg, #ff6b35 0%, #ff9329 100%); border: none;">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>