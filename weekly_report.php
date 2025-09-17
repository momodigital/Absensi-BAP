<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'config/database.php';

$user_id = $_SESSION['user_id'];

// Ambil data absensi 7 hari terakhir
$stmt = $pdo->prepare("
    SELECT * FROM attendance 
    WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ORDER BY date ASC
");
$stmt->execute([$user_id]);
$records = $stmt->fetchAll();

// Siapkan data untuk grafik
$labels = [];
$data_present = [];
$data_late = [];
$data_absent = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = date('D', strtotime($date));
    $labels[] = "$dayName";

    $found = false;
    foreach ($records as $r) {
        if ($r['date'] == $date) {
            $data_present[] = ($r['status'] == 'present') ? 1 : 0;
            $data_late[] = ($r['status'] == 'late') ? 1 : 0;
            $data_absent[] = 0;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $data_present[] = 0;
        $data_late[] = 0;
        $data_absent[] = 1;
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="card">
    <h2>📊 Laporan Absensi Mingguan</h2>
    <p>Menampilkan data 7 hari terakhir</p>

    <div class="chart-container">
        <canvas id="weeklyChart"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('weeklyChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
             {
                labels: <?= json_encode($labels) ?>,
                datasets: [
                    {
                        label: 'Hadir Tepat Waktu',
                         <?= json_encode($data_present) ?>,
                        backgroundColor: '#3b82f6'
                    },
                    {
                        label: 'Terlambat',
                         <?= json_encode($data_late) ?>,
                        backgroundColor: '#f59e0b'
                    },
                    {
                        label: 'Absen',
                         <?= json_encode($data_absent) ?>,
                        backgroundColor: '#ef4444'
                    }
                ]
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
    <h3>📋 Detail Absensi</h3>
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
        <p style="text-align:center; padding:20px; color:#64748b;">Belum ada data absensi dalam 7 hari terakhir.</p>
    <?php endif; ?>
</div>

<div style="text-align:center; margin:24px 0;">
    <a href="index.php" class="btn" style="background:#64748b;">← Kembali ke Dashboard</a>
</div>

<?php include 'includes/footer.php'; ?>
