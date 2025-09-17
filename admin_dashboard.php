<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit;
}

require 'config/database.php';
require 'includes/functions.php';

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetch()['role'] ?? 'user';

if ($userRole !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

// Statistik: Total Sopir
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stmt->execute();
$totalSopir = $stmt->fetch()['total'];

// Statistik: Hadir Hari Ini
$stmt = $pdo->prepare("SELECT COUNT(*) as hadir FROM attendance WHERE date = CURDATE()");
$stmt->execute();
$hadirHariIni = $stmt->fetch()['hadir'];

// Statistik: Per AMT
$stmt = $pdo->prepare("SELECT amt_type, COUNT(*) as jumlah FROM users WHERE role = 'user' GROUP BY amt_type");
$stmt->execute();
$amtStats = $stmt->fetchAll();

// Statistik: Gaji AMT
$gajiAMT1 = getSetting($pdo, 'gaji_AMT 1', 250000);
$gajiAMT2 = getSetting($pdo, 'gaji_AMT 2', 200000);

// Absensi Hari Ini (Detail)
$stmt = $pdo->prepare("
    SELECT u.name, u.amt_type, a.destination, a.daily_salary, a.date, a.check_in, a.check_out
    FROM attendance a
    JOIN users u ON a.user_id = u.id
    WHERE a.date = CURDATE()
    ORDER BY u.name
");
$stmt->execute();
$absenHariIni = $stmt->fetchAll();

// Rekap Gaji Bulan Ini (Top 5)
$stmt = $pdo->prepare("
    SELECT u.name, u.amt_type, SUM(a.daily_salary) as total_gaji, COUNT(a.id) as hari_kerja
    FROM attendance a
    JOIN users u ON a.user_id = u.id
    WHERE YEAR(a.date) = YEAR(CURDATE()) 
      AND MONTH(a.date) = MONTH(CURDATE())
    GROUP BY u.id
    ORDER BY total_gaji DESC
    LIMIT 5
");
$stmt->execute();
$topGaji = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="card">
    <h2>🛠️ Dashboard Admin Armada</h2>
    <p>Selamat datang, Admin. Berikut ringkasan operasional hari ini.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>👥 Total Sopir</h3>
        <div class="value"><?= $totalSopir ?></div>
    </div>
    <div class="stat-card">
        <h3>✅ Hadir Hari Ini</h3>
        <div class="value"><?= $hadirHariIni ?></div>
    </div>
    <div class="stat-card">
        <h3>💰 Gaji AMT 1</h3>
        <div class="value">Rp <?= number_format($gajiAMT1, 0, ',', '.') ?></div>
    </div>
    <div class="stat-card">
        <h3>💰 Gaji AMT 2</h3>
        <div class="value">Rp <?= number_format($gajiAMT2, 0, ',', '.') ?></div>
    </div>
</div>

<div class="card">
    <h3>📊 Distribusi Sopir per AMT</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin: 20px 0;">
        <?php foreach ($amtStats as $stat): ?>
            <div style="text-align: center; padding: 20px; background: <?= $stat['amt_type'] === 'AMT 1' ? '#dbeafe' : '#fef3c7' ?>; border-radius: 12px; min-width: 150px;">
                <div style="font-size: 1.2rem; font-weight: 700; color: <?= $stat['amt_type'] === 'AMT 1' ? '#1d4ed8' : '#92400e' ?>;">
                    <?= $stat['amt_type'] ?>
                </div>
                <div style="font-size: 2rem; font-weight: 700; margin: 10px 0;"><?= $stat['jumlah'] ?></div>
                <div style="font-size: 0.9rem; color: #64748b;">Sopir</div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;">
        <a href="tambah_sopir.php" class="btn" style="flex: 1; min-width: 200px;">
            ➕ Tambah Sopir
        </a>
        <a href="kelola_gaji.php" class="btn btn-success" style="flex: 1; min-width: 200px;">
            💰 Kelola Gaji AMT
        </a>
        <a href="kelola_tujuan.php" class="btn btn-info" style="flex: 1; min-width: 200px;">
            📍 Kelola Tujuan
        </a>
        <a href="export_gaji.php?bulan=<?= date('n') ?>&tahun=<?= date('Y') ?>" class="btn btn-danger" style="flex: 1; min-width: 200px;">
            📥 Export Excel
        </a>
    </div>

    <h3>📋 Absensi Hari Ini (<?= date('d M Y') ?>)</h3>
    <?php if (count($absenHariIni) > 0): ?>
        <table>
            <tr>
                <th>Nama Sopir</th>
                <th>AMT</th>
                <th>Check-In</th>
                <th>Tujuan</th>
                <th>Gaji</th>
            </tr>
            <?php foreach ($absenHariIni as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ', '', $row['amt_type'])) ?>"><?= $row['amt_type'] ?></span></td>
                <td><?= $row['check_in'] ?></td>
                <td><?= htmlspecialchars($row['destination']) ?></td>
                <td>Rp <?= number_format($row['daily_salary'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center; padding:20px; color:#64748b; background:#fefefe; border-radius:12px;">
            Belum ada sopir yang melakukan absensi hari ini.
        </p>
    <?php endif; ?>
</div>

<div class="card">
    <h3>🏆 Top 5 Sopir dengan Gaji Tertinggi Bulan Ini</h3>
    <?php if (count($topGaji) > 0): ?>
        <table>
            <tr>
                <th>Peringkat</th>
                <th>Nama</th>
                <th>AMT</th>
                <th>Hari Kerja</th>
                <th>Total Gaji</th>
            </tr>
            <?php foreach ($topGaji as $index => $row): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ', '', $row['amt_type'])) ?>"><?= $row['amt_type'] ?></span></td>
                <td><?= $row['hari_kerja'] ?> hari</td>
                <td><strong>Rp <?= number_format($row['total_gaji'], 0, ',', '.') ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center; padding:20px; color:#64748b; background:#fefefe; border-radius:12px;">
            Belum ada data gaji bulan ini.
        </p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
