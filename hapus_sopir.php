<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit;
}

require 'config/database.php';

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetch()['role'] ?? 'user';

if ($userRole !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

if ($_POST && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];

    try {
        // Mulai transaksi
        $pdo->beginTransaction();

        // Ambil nama user sebelum dihapus (untuk notifikasi)
        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ? AND role = 'user'");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("Sopir tidak ditemukan atau sudah dihapus.");
        }

        // Hapus user (data absensi otomatis terhapus karena ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $affected = $stmt->execute([$user_id]);

        if ($affected) {
            $pdo->commit();
            $_SESSION['message'] = "✅ Sopir <strong>" . htmlspecialchars($user['name']) . "</strong> dan semua data absensinya berhasil dihapus!";
        } else {
            throw new Exception("Gagal menghapus sopir.");
        }
    } catch (Exception $e) {
        $pdo->rollback();
        $_SESSION['message'] = "❌ Gagal menghapus sopir: " . $e->getMessage();
    }
}

// Redirect kembali ke halaman sebelumnya (biasanya kelola_gaji.php atau admin_dashboard.php)
$referrer = $_SERVER['HTTP_REFERER'] ?? 'admin_dashboard.php';
header("Location: " . $referrer);
exit;
?>
