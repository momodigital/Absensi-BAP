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

// Hitung gaji bulan ini
$salaryInfo = calculateMonthlySalary($pdo, $user_id);

// Ambil riwayat gaji bulan ini (untuk tabel detail)
$stmt = $pdo->prepare("
    SELECT date, destination, daily_salary, status, check_in, check_out
    FROM attendance 
    WHERE user_id = ? 
      AND YEAR(date) = YEAR(CURDATE()) 
      AND MONTH(date) = MONTH(CURDATE())
    ORDER BY date DESC
");
$stmt->execute([$user_id]);
$gajiHistory = $stmt->fetchAll();
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

<!-- ✅ BARU: TAMPILKAN TOTAL GAJI BULAN INI -->
<div class="card">
    <h3>💰 Total Gaji Bulan Ini</h3>
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Hari Kerja</h3>
            <div class="value"><?= $salaryInfo['working_days'] ?> hari</div>
        </div>
        <div class="stat-card">
            <h3>Total Gaji</h3>
            <div class="value">Rp <?= number_format($salaryInfo['total_salary'], 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<!-- ✅ BARU: TABEL RIWAYAT GAJI BULAN INI -->
<div class="card">
    <h3>📋 Riwayat Gaji Bulan Ini</h3>
    <?php if (count($gajiHistory) > 0): ?>
        <table>
            <tr>
                <th>Tanggal</th>
                <th>Tujuan</th>
                <th>Status</th>
                <th>Gaji</th>
            </tr>
            <?php foreach ($gajiHistory as $row): ?>
            <tr>
                <td><?= date('d M', strtotime($row['date'])) ?></td>
                <td><?= htmlspecialchars($row['destination']) ?></td>
                <td>
                    <?php if ($row['status'] == 'present'): ?>
                        <span class="badge badge-success">Hadir</span>
                    <?php elseif ($row['status'] == 'late'): ?>
                        <span class="badge badge-warning">Terlambat</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Absen</span>
                    <?php endif; ?>
                </td>
                <td>Rp <?= number_format($row['daily_salary'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center; padding:20px; color:#64748b;">Belum ada riwayat gaji bulan ini.</p>
    <?php endif; ?>
</div>

<div style="text-align:center; margin:24px 0;">
    <a href="weekly_report.php" class="btn" style="margin:8px;">📊 Laporan Mingguan</a>
    <a href="monthly_report.php" class="btn" style="margin:8px;">📈 Laporan Bulanan</a>
</div>

<?php include 'includes/footer.php'; ?>
