<?php require_once '../../layouts/header.php'; ?>

<div class="container" style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
    <div class="card" style="width: 100%; max-width: 400px;">
        <h2 style="text-align: center; color: var(--color-primary); margin-bottom: 20px;">Masuk</h2>

        <?php if (isset($_GET['error'])): ?>
            <div style="color: var(--color-danger); text-align: center; margin-bottom: 15px;">
                <?php
                    if ($_GET['error'] == 'wrong_password') echo "Password salah.";
                    else if ($_GET['error'] == 'user_not_found') echo "Email tidak ditemukan.";
                    else if ($_GET['error'] == 'empty_fields') echo "Mohon isi semua kolom.";
                    else if ($_GET['error'] == 'access_denied') echo "Silakan login terlebih dahulu.";
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div style="color: var(--color-success); text-align: center; margin-bottom: 15px;">
                Registrasi berhasil, silakan login.
            </div>
        <?php endif; ?>

        <form action="../../../backend/auth/login_process.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Email</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid var(--color-border); border-radius: var(--border-radius);">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid var(--color-border); border-radius: var(--border-radius);">
            </div>

            <button type="submit" style="width: 100%; padding: 12px; background-color: var(--color-primary); color: white; border: none; border-radius: var(--border-radius); cursor: pointer; font-size: 16px;">
                Login
            </button>
        </form>

        <p style="text-align: center; margin-top: 15px;">
            Belum punya akun? <a href="register.php">Daftar</a>
        </p>
    </div>
</div>

<?php require_once '../../layouts/footer.php'; ?>