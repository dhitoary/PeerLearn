<?php
session_set_cookie_params(0, '/'); 
session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: ../auth/login.php");
    exit;
}

$configPath = dirname(__DIR__, 3) . '/config/database.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

$page = $_GET['page'] ?? 'home'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - PeerLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-dark: #cc5500;
            --primary-light: #ff9329;
            --accent-yellow: #ffb866;
            --accent-orange: #FF6B35;
            --bg-light: #F8FAFB;
        }
        
        body { 
            overflow-x: hidden; 
            background: linear-gradient(135deg, #F8FAFB 0%, #E8F4F8 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        #wrapper { display: flex; width: 100%; height: 100vh; overflow: hidden; }
        
        #sidebar-wrapper {
            min-width: 260px; max-width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-dark) 0%, #094356 100%);
            color: #fff;
            overflow-y: auto;
            position: relative;
            box-shadow: 4px 0 20px rgba(12, 74, 96, 0.15);
        }
        
        #sidebar-wrapper .sidebar-heading {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--primary-light) !important;
            letter-spacing: 1px;
        }
        
        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            color: rgba(255, 255, 255, 0.85);
            border: none;
            padding: 0.9rem 1.5rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        #sidebar-wrapper .list-group-item:hover {
            background: rgba(154, 212, 214, 0.15);
            color: #fff;
            border-left-color: var(--accent-yellow);
            transform: translateX(5px);
        }
        
        #sidebar-wrapper .list-group-item.active {
            background: linear-gradient(90deg, rgba(154, 212, 214, 0.3) 0%, transparent 100%);
            color: #fff;
            font-weight: 600;
            border-left-color: var(--accent-orange);
            box-shadow: 0 2px 10px rgba(255, 107, 53, 0.2);
        }
        
        #sidebar-wrapper .list-group-item.text-danger {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto !important;
        }
        
        #sidebar-wrapper .list-group-item.text-danger:hover {
            background: rgba(255, 107, 53, 0.2);
            border-left-color: var(--accent-orange);
        }

        #page-content-wrapper {
            width: 100%;
            height: 100vh;
            overflow-y: auto;
            position: relative;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(154, 212, 214, 0.3) !important;
        }
        
        .card {
            border: none !important;
            border-radius: 12px !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(12, 74, 96, 0.15) !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #0A5A70 100%) !important;
            border: none !important;
            box-shadow: 0 4px 10px rgba(12, 74, 96, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(12, 74, 96, 0.4) !important;
        }
        
        .badge {
            padding: 0.4rem 0.8rem;
            font-weight: 500;
            border-radius: 20px;
        }
        
        .table thead {
            background: linear-gradient(135deg, var(--primary-light) 0%, #ffd4c1 100%) !important;
            color: var(--primary-dark) !important;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light) !important;
            box-shadow: 0 0 0 0.2rem rgba(154, 212, 214, 0.25) !important;
        }
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">
    <div id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4 border-bottom border-secondary fs-4 fw-bold">
            PeerLearn
        </div>
            <div class="list-group list-group-flush">
                <a href="?page=home" class="list-group-item list-group-item-action <?= $page == 'home' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="?page=tutor" class="list-group-item list-group-item-action <?= $page == 'tutor' ? 'active' : '' ?>">
                <i class="fas fa-chalkboard-teacher me-2"></i> Data Tutor
                </a>
                <a href="?page=siswa" class="list-group-item list-group-item-action <?= $page == 'siswa' ? 'active' : '' ?>">
                <i class="fas fa-user-graduate me-2"></i> Data Siswa
                </a>
                <a href="?page=verifikasi" class="list-group-item list-group-item-action <?= $page == 'verifikasi' ? 'active' : '' ?>">
                <i class="fas fa-check-circle me-2"></i> Verifikasi
                </a>
                <a href="../../../backend/auth/logout.php" class="list-group-item list-group-item-action text-danger mt-5">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-4 sticky-top">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <i class="fas fa-folder-open me-2" style="color: var(--primary-dark);"></i>
                    <span class="navbar-text">Halaman: <strong style="color: var(--primary-dark);"><?= ucfirst($page) ?></strong></span>
                </div>
                <div class="d-flex align-items-center ms-auto">
                    <div class="bg-light rounded-pill px-3 py-2 d-flex align-items-center" style="border: 2px solid var(--primary-light);">
                        <div class="rounded-circle bg-gradient d-flex align-items-center justify-content-center me-2" 
                             style="width: 35px; height: 35px; background: linear-gradient(135deg, var(--primary-dark), var(--primary-light));">
                            <i class="fas fa-user-shield text-white"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.7rem; line-height: 1;">Administrator</small>
                            <strong style="color: var(--primary-dark); font-size: 0.9rem;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid px-4 mt-4 mb-5">
            <?php 
            $viewDir = 'views/';
            $file = $viewDir . $page . '.php';
            
            if (file_exists($file)) {
                include $file;
            } else {
                echo '<div class="alert alert-danger">File View tidak ditemukan: '.$file.'</div>';
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmAction(message, icon = 'warning') {
        return Swal.fire({
            title: 'Yakin?',
            text: message,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        });
    }

    function showToast(message, icon = 'success') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        Toast.fire({ icon: icon, title: message });
    }
</script>

</body>
</html>