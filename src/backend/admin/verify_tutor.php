<?php
// Disable error display, hanya log
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// Set header JSON di awal
header('Content-Type: application/json');

// Cek koneksi database
$configPath = dirname(__FILE__) . '/../../config/database.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Database config tidak ditemukan']);
    exit;
}

require_once $configPath;

// Check if admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Silakan login sebagai admin']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tutorId = isset($_POST['tutor_id']) ? (int)$_POST['tutor_id'] : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($tutorId <= 0 || empty($action)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap (ID: ' . $tutorId . ', Action: ' . $action . ')']);
        exit;
    }

    if (!in_array($action, ['approve', 'reject'])) {
        echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
        exit;
    }

    // Begin transaction
    mysqli_begin_transaction($conn);

    try {
        // Get tutor email
        $emailQuery = "SELECT email FROM tutor WHERE id = ?";
        $stmt = mysqli_prepare($conn, $emailQuery);
        mysqli_stmt_bind_param($stmt, "i", $tutorId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $tutor = mysqli_fetch_assoc($result);

        if (!$tutor) {
            throw new Exception("Tutor tidak ditemukan");
        }

        if ($action === 'approve') {
            // Update tutor table: status = Aktif
            $updateTutorQuery = "UPDATE tutor SET status = 'Aktif' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateTutorQuery);
            mysqli_stmt_bind_param($stmt, "i", $tutorId);
            mysqli_stmt_execute($stmt);

            $message = "Tutor berhasil diaktifkan";

        } else if ($action === 'reject') {
            // Update tutor table: status = Cuti (sebagai penanda ditolak)
            $updateTutorQuery = "UPDATE tutor SET status = 'Cuti' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateTutorQuery);
            mysqli_stmt_bind_param($stmt, "i", $tutorId);
            mysqli_stmt_execute($stmt);

            $message = "Tutor berhasil ditolak";
        } else {
            throw new Exception("Invalid action");
        }

        // Commit transaction
        mysqli_commit($conn);

        echo json_encode([
            'success' => true,
            'message' => $message
        ]);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
mysqli_close($conn);
?>