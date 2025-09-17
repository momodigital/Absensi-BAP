<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'config/database.php';
require 'includes/functions.php';

$user_id = $_SESSION['user_id'];
$selectedMonth = $_GET['month'] ?? date('n');
$selectedYear = $_GET['year'] ?? date('Y');

// Hitung gaji bulan ini
$salaryInfo = calculateMonthlySalary($pdo, $user_id, $selectedYear, $selectedMonth);

// Ambil data absensi bulan ini
$stmt = $pdo->prepare("
    SELECT * FROM attendance 
    WHERE user_id = ? 
      AND YEAR(date) = ? 
      AND MONTH(date) = ?
    ORDER BY date ASC
");
$stmt->execute([$user_id, $selectedYear, $selectedMonth]);
$records = $stmt->fetchAll();

// Siapkan data grafik per minggu
$weeks = [];
$startOfMonth = "$selectedYear-$selectedMonth-01";
for ($w = 0; $w < 5; $w++) {
    $start = date('Y-m-d', strtotime("$startOfMonth + " . ($w*7) . " days"));
    $end = date('Y-m-d', strtotime("$start + 6 days"));
    
    // Cek apakah masih dalam bulan yang sama
    if ($w > 0 && date('m', strtotime($start)) != $selectedMonth) {
        break;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM attendance 
        WHERE user_id = ? 
          AND date BETWEEN ? AND ? 
          AND status IN ('present', 'late')
    ");
    $stmt->execute([$user_id, $start, $end]);
    $result = $stmt->fetch();
    $weeks[] = (int)$result['total'];
}

$labels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5'];
$labels = array_slice($labels, 0, count($weeks)); // Sesuaikan jumlah minggu
?>

<?php include 'includes/header.php'; ?>

<div class="card">
    <h2>📈 Laporan Bulanan</h2>
    
    <form method="GET" style="margin:24px 0; text-align:center;">
        <label style="margin-right:10px; font-weight:600;">Bulan:</label>
        <select name="month" onchange="this.form.submit()" style="padding:10px; border-radius:8px; border:2px solid var(--border); font-size:16px;">
            <?php
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            foreach ($months as $num => $name):
            ?>
                <option value="<?= $num ?>" <?= $num == $selectedMonth ? 'selected' : '' ?>>
                    <?= $name ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label style="margin:0 10px 0 20px; font-weight:600;">Tahun:</label>
        <select name="year" onchange="this.form.submit()" style="padding:10px; border-radius:8px; border:2px solid var(--border); font-size:16px;">
            <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>>
                    <?= $y ?>
                </option>
            <?php endfor; ?>
        </select>
    </form>

    <!-- ✅ BARU: TAMBAHKAN TOTAL GAJI DI HEADER -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>🗓️ Bulan</h3>
            <div class="value"><?= $months[$selectedMonth] ?> <?= $selectedYear ?></div>
        </div>
        <div class="stat-card">
            <h3>💼 Hari Kerja</h3>
            <div class="value"><?= $salaryInfo['working_days'] ?></div>
        </div>
        <div class="stat-card">
            <h3>💰 Total Gaji</h3>
            <div class="value">Rp <?= number_format($salaryInfo['total_salary'], 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<div class="card">
    <h3>📊 Grafik Kehadiran per Minggu</h3>
    <div class="chart-container">
        <canvas id="monthlyChart"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
             {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Jumlah Hadir',
                     <?= json_encode($weeks) ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        stepSize: 1,
                        ticks: {
                            precision: 0
                        }
                    } 
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    </script>
</div>

<div class="card">
    <h3>📋 Detail Absensi Bulan Ini</h3>
    <?php if (count($records) > 0): ?>
        <table>
            <tr>
                <th>Tanggal</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Tujuan</th>
                <th>Status</th>
                <th>Gaji</th>
            </tr>
            <?php foreach ($records as $r): ?>
            <tr>
                <td><?= date('d M Y', strtotime($r['date'])) ?></td>
                <td><?= $r['check_in'] ?: '-' ?></td>
                <td><?= $r['check_out'] ?: '-' ?></td>
                <td><?= htmlspecialchars($r['destination']) ?></td>
                <td>
                    <?php if ($r['status'] == 'present'): ?>
                        <span class="badge badge-success">Hadir</span>
                    <?php elseif ($r['status'] == 'late'): ?>
                        <span class="badge badge-warning">Terlambat</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Absen</span>
                    <?php endif; ?>
                </td>
                <td>Rp <?= number_format($r['daily_salary'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center; padding:20px; color:#64748b;">
            Belum ada data absensi untuk bulan <?= $months[$selectedMonth] ?> <?= $selectedYear ?>.
        </p>
    <?php endif; ?>
</div>

<div style="text-align:center; margin:24px 0;">
    <a href="index.php" class="btn" style="background:#64748b;">← Kembali ke Dashboard</a>
</div>

<?php include 'includes/footer.php'; ?>
