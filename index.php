<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'config/database.php';
require 'includes/functions.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT amt_type FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$amt_type = $user['amt_type'];

$daily_base_salary = getAmtSalary($pdo, $amt_type);

$stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = CURDATE()");
$stmt->execute([$user_id]);
$todayRecord = $stmt->fetch();
$hasCheckedIn = $todayRecord && $todayRecord['check_in'];
$hasCheckedOut = $todayRecord && $todayRecord['check_out'];

$stmt = $pdo->prepare("SELECT id, name FROM destinations ORDER BY name ASC");
$stmt->execute();
$destinations = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="card">
    <h2>📋 Absensi Hari Ini</h2>

    <?php if (!$hasCheckedIn): ?>
        <form action="absen.php" method="POST" class="form-group">
            <input type="hidden" name="action" value="checkin">
            
            <div class="form-group">
                <label>📍 Pilih Tujuan Perjalanan Hari Ini (Wajib)</label>
                <select name="destination_id" class="form-control" required style="padding:14px; font-size:16px; border-radius:12px;">
                    <option value="">-- Pilih Tujuan --</option>
                    <?php foreach ($destinations as $dest): ?>
                        <option value="<?= $dest['id'] ?>"><?= htmlspecialchars($dest['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color:#64748b;">Pilih salah satu rute yang tersedia.</small>
            </div>
            
            <div class="form-group">
                <label>💰 Gaji Dasar AMT <?= $amt_type ?>: Rp <?= number_format($daily_base_salary, 0, ',', '.') ?></label>
                <input type="hidden" name="daily_salary" value="<?= $daily_base_salary ?>">
            </div>
            
            <button type="submit" class="btn btn-success" style="width:100%; padding:16px; font-size:18px;">
                ✅ Check-In & Simpan Tujuan
            </button>
        </form>
    <?php else: ?>
        <div style="background:#dbeafe; padding:20px; border-radius:12px; margin-bottom:20px;">
            <p style="font-weight:600; color:#1e40af;">✅ Anda sudah check-in hari ini</p>
            <p><strong>Tujuan:</strong> <?= htmlspecialchars($todayRecord['destination']) ?></p>
            <p><strong>Gaji Hari Ini:</strong> Rp <?= number_format($todayRecord['daily_salary'], 0, ',', '.') ?></p>
            
            <?php if (!$hasCheckedOut): ?>
                <form action="absen.php" method="POST" style="margin-top:20px;">
                    <input type="hidden" name="action" value="checkout">
                    <button type="submit" class="btn btn-warning" style="width:100%; padding:14px;">
                        🚪 Check-Out Sekarang
                    </button>
                </form>
            <?php else: ?>
                <p style="margin-top:20px; font-weight:600; color:#059669;">✅ Perjalanan hari ini selesai.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$salaryInfo = calculateMonthlySalary($pdo, $user_id);
?>

<div class="card">
    <h3>💰 Rekap Gaji Bulan Ini</h3>
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Hari Kerja</h3>
            <div class="value"><?= $salaryInfo['working_days'] ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Gaji</h3>
            <div class="value">Rp <?= number_format($salaryInfo['total_salary'], 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<div style="text-align:center; margin:24px 0;">
    <a href="weekly_report.php" class="btn" style="margin:8px;">📊 Laporan Mingguan</a>
    <a href="monthly_report.php" class="btn" style="margin:8px;">📈 Laporan Bulanan</a>
</div>

<?php include 'includes/footer.php'; ?>
