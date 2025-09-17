<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Sopir Armada</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚛 Absensi Sopir Armada</h1>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                require_once 'config/database.php';
                $stmt = $pdo->prepare("SELECT role, name, amt_type FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                $userRole = $user['role'] ?? 'user';
                $userName = $user['name'] ?? 'User';
                $amtType = $user['amt_type'] ?? 'AMT 1';
                ?>
                <div class="nav-links">
                    <span>Halo, <?= htmlspecialchars($userName) ?> <span class="badge badge-<?= strtolower(str_replace(' ', '', $amtType)) ?>"><?= $amtType ?></span></span>
                    <?php if ($userRole === 'admin'): ?>
                        <a href="admin_dashboard.php">Admin</a>
                        <a href="kelola_gaji.php">Gaji</a>
                        <a href="kelola_tujuan.php">Tujuan</a>
                        <a href="tambah_sopir.php">+ Sopir</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                </div>
            <?php endif; ?>
        </div>
