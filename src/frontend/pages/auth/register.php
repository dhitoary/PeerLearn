<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - PeerLearn</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 3L1 9L5 11.18V17.18L12 21L19 17.18V11.18L21 10.09V17H23V9L12 3M18.82 9L12 12.72L5.18 9L12 5.28L18.82 9M17 16L12 18.72L7 16V12.27L12 15L17 12.27V16Z"/>
                </svg>
            </div>

            <h1 class="auth-title">PeerLearn</h1>
            <p class="auth-subtitle">Platform Bimbingan Belajar Terpercaya</p>

            <!-- Tabs -->
            <div class="auth-tabs">
                <a href="login.php" class="auth-tab">Masuk</a>
                <button class="auth-tab active">Daftar</button>
            </div>

            <!-- Error Message -->
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php
                        if ($_GET['error'] == 'email_taken') echo "Email sudah terdaftar. Silakan gunakan email lain.";
                        else if ($_GET['error'] == 'invalid_email') echo "Format email tidak valid.";
                        else if ($_GET['error'] == 'db_error') {
                            echo "Terjadi kesalahan sistem. Silakan coba lagi.";
                            if (isset($_GET['msg'])) {
                                echo "<br><small style='font-size: 12px;'>Detail: " . htmlspecialchars($_GET['msg']) . "</small>";
                            }
                        }
                        else if ($_GET['error'] == 'empty_fields') echo "Harap isi semua data dengan lengkap.";
                        else echo "Terjadi kesalahan. Silakan coba lagi.";
                    ?>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; border-color: #c3e6cb;">
                    <?php
                        if ($_GET['success'] == 'registered') echo "Pendaftaran berhasil! Silakan login.";
                        else if ($_GET['success'] == 'registered_pending') echo "Pendaftaran berhasil! Akun Anda akan diverifikasi oleh admin.";
                    ?>
                </div>
            <?php endif; ?>

            <!-- Register Form -->
            <form action="../../../backend/auth/register_process.php" method="POST" id="registerForm">
                
                <!-- Role Selection First -->
                <div class="form-group">
                    <label class="form-label">Daftar Sebagai</label>
                    <div class="role-options">
                        <div class="role-option">
                            <input type="radio" name="role" id="role-learner" value="learner" required onchange="toggleFormFields()">
                            <label for="role-learner" class="role-label">
                                <div class="role-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"/>
                                    </svg>
                                </div>
                                <span class="role-name">Siswa</span>
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" name="role" id="role-tutor" value="tutor" required onchange="toggleFormFields()">
                            <label for="role-tutor" class="role-label">
                                <div class="role-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path d="M12,3L1,9L12,15L21,10.09V17H23V9M5,13.18V17.18L12,21L19,17.18V13.18L12,17L5,13.18Z"/>
                                    </svg>
                                </div>
                                <span class="role-name">Tutor</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-input" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="nama@email.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-toggle">
                        <input type="password" name="password" id="password" class="form-input" placeholder="Minimal 8 karakter" required minlength="8">
                        <span class="toggle-icon" onclick="togglePassword()">👁️</span>
                    </div>
                </div>

                <!-- Fields for Siswa Only -->
                <div id="siswaFields" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Jenjang Pendidikan</label>
                        <select name="jenjang" class="form-input">
                            <option value="">Pilih Jenjang</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Sekolah</label>
                        <input type="text" name="sekolah" class="form-input" placeholder="Contoh: SMAN 1 Bandar Lampung">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kelas</label>
                        <input type="text" name="kelas" class="form-input" placeholder="Contoh: 12 IPA 1">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Minat Belajar (Opsional)</label>
                        <textarea name="minat" class="form-input" rows="3" placeholder="Contoh: Matematika, Fisika, Kimia"></textarea>
                    </div>
                </div>

                <!-- Fields for Tutor Only -->
                <div id="tutorFields" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Nomor Telepon/WhatsApp</label>
                        <input type="tel" name="telepon" class="form-input" placeholder="Contoh: 081234567890">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Keahlian/Mata Pelajaran</label>
                        <input type="text" name="keahlian" class="form-input" placeholder="Contoh: Matematika, Fisika, Bahasa Inggris">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan" class="form-input" placeholder="Contoh: S1 Pendidikan Matematika Unila">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pengalaman Mengajar (Tahun)</label>
                        <input type="number" name="pengalaman_mengajar" class="form-input" placeholder="Contoh: 3" min="0" max="50">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Harga Per Sesi (Rp)</label>
                        <input type="number" name="harga_per_sesi" class="form-input" placeholder="Contoh: 150000" min="50000" step="10000">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi Singkat</label>
                        <textarea name="deskripsi" class="form-input" rows="4" placeholder="Ceritakan sedikit tentang pengalaman dan metode mengajar Anda..."></textarea>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" required>
                    <label for="terms" class="checkbox-label">
                        Saya setuju dengan <a href="#">Syarat & Ketentuan</a>
                    </label>
                </div>

                <button type="submit" class="btn-primary">Daftar Sekarang</button>
            </form>

            <div class="auth-footer">
                Sudah punya akun? <a href="login.php">Masuk</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        }

        function toggleFormFields() {
            const siswaFields = document.getElementById('siswaFields');
            const tutorFields = document.getElementById('tutorFields');
            const roleLearner = document.getElementById('role-learner');
            const roleTutor = document.getElementById('role-tutor');

            if (roleLearner.checked) {
                // Show siswa fields, hide tutor fields
                siswaFields.style.display = 'block';
                tutorFields.style.display = 'none';
                
                // Make siswa fields required
                siswaFields.querySelectorAll('select[name="jenjang"]').forEach(el => el.required = true);
                
                // Make tutor fields not required
                tutorFields.querySelectorAll('input, textarea').forEach(el => el.required = false);
                
            } else if (roleTutor.checked) {
                // Show tutor fields, hide siswa fields
                siswaFields.style.display = 'none';
                tutorFields.style.display = 'block';
                
                // Make tutor fields required
                tutorFields.querySelectorAll('input[name="telepon"], input[name="keahlian"], input[name="pendidikan"]').forEach(el => el.required = true);
                
                // Make siswa fields not required
                siswaFields.querySelectorAll('select, input, textarea').forEach(el => el.required = false);
            }
        }

        // Validasi form sebelum submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const roleLearner = document.getElementById('role-learner');
            const roleTutor = document.getElementById('role-tutor');
            
            // Pastikan role dipilih
            if (!roleLearner.checked && !roleTutor.checked) {
                e.preventDefault();
                alert('Silakan pilih role: Siswa atau Tutor');
                return false;
            }

            // Validasi field tutor jika role tutor
            if (roleTutor.checked) {
                const telepon = document.querySelector('input[name="telepon"]').value.trim();
                const keahlian = document.querySelector('input[name="keahlian"]').value.trim();
                const pendidikan = document.querySelector('input[name="pendidikan"]').value.trim();

                if (!telepon || !keahlian || !pendidikan) {
                    e.preventDefault();
                    alert('Untuk registrasi tutor, harap lengkapi: Telepon, Keahlian, dan Pendidikan');
                    return false;
                }
            }

            // Validasi field siswa jika role siswa
            if (roleLearner.checked) {
                const jenjang = document.querySelector('select[name="jenjang"]').value;
                
                if (!jenjang) {
                    e.preventDefault();
                    alert('Silakan pilih jenjang pendidikan');
                    return false;
                }
            }

            return true;
        });
    </script>
</body>
</html>