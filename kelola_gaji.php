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

$message = '';

if ($_POST) {
    $amt1_salary = (int)$_POST['amt1_salary'];
    $amt2_salary = (int)$_POST['amt2_salary'];

    if ($amt1_salary < 50000 || $amt2_salary < 50000) {
        $message = "<div class='message error'>❌ Gaji minimal Rp 50.000 per hari!</div>";
    } else {
        saveSetting($pdo, 'gaji_AMT 1', $amt1_salary);
        saveSetting($pdo, 'gaji_AMT 2', $amt2_salary);
        $message = "<div class='message success'>✅ Pengaturan gaji AMT berhasil diperbarui!</div>";
    }
}

$amt1_salary = getSetting($pdo, 'gaji_AMT 1', 250000);
$amt2_salary = getSetting($pdo, 'gaji_AMT 2', 200000);
?>

<?php include 'includes/header.php'; ?>

<div class="card">
    <h2>💰 Kelola Gaji Dasar per Kategori AMT</h2>
    <p>Atur gaji harian dasar untuk setiap kategori sopir. Gaji ini akan digunakan sebagai dasar perhitungan saat sopir absen.</p>
</div>

<?= $message ?>

<div class="card">
    <form method="POST">
        <div class="form-group">
            <label>🚛 Gaji Dasar AMT 1 (Rp/hari)</label>
            <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                <span style="font-size: 1.2rem; font-weight: 700;">Rp</span>
                <input 
                    type="number" 
                    name="amt1_salary" 
                    value="<?= $amt1_salary ?>" 
                    min="50000" 
                    required 
                    style="flex: 1; padding: 14px; font-size: 18px; font-weight: bold; border: 2px solid #dbeafe; border-radius: 12px;"
                >
            </div>
            <small style="color: #64748b;">Contoh: 250000 → untuk sopir kategori AMT 1</small>
        </div>

        <div class="form-group">
            <label>🚛 Gaji Dasar AMT 2 (Rp/hari)</label>
            <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                <span style="font-size: 1.2rem; font-weight: 700;">Rp</span>
                <input 
                    type="number" 
                    name="amt2_salary" 
                    value="<?= $amt2_salary ?>" 
                    min="50000" 
                    required 
                    style="flex: 1; padding: 14px; font-size: 18px; font-weight: bold; border: 2px solid #fef3c7; border-radius: 12px;"
                >
            </div>
            <small style="color: #64748b;">Contoh: 200000 → untuk sopir kategori AMT 2</small>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn btn-success" style="padding: 16px 40px; font-size: 18px; font-weight: 600;">
                💾 Simpan Pengaturan Gaji
            </button>
        </div>
    </form>
</div>

<div class="card">
    <h3>📊 Preview Gaji per Kategori</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        <div style="background: #dbeafe; padding: 20px; border-radius: 16px; border: 2px solid #93c5fd; text-align: center;">
            <div style="font-size: 1.1rem; font-weight: 600; color: #1d4ed8; margin-bottom: 10px;">AMT 1</div>
            <div style="font-size: 2rem; font-weight: 700; color: #1e3a8a; margin: 15px 0;">Rp <?= number_format($amt1_salary, 0, ',', '.') ?></div>
            <div style="font-size: 0.9rem; color: #374151;">per hari kerja</div>
        </div>
        <div style="background: #fef3c7; padding: 20px; border-radius: 16px; border: 2px solid #fde68a; text-align: center;">
            <div style="font-size: 1.1rem; font-weight: 600; color: #92400e; margin-bottom: 10px;">AMT 2</div>
            <div style="font-size: 2rem; font-weight: 700; color: #92400e; margin: 15px 0;">Rp <?= number_format($amt2_salary, 0, ',', '.') ?></div>
            <div style="font-size: 0.9rem; color: #374151;">per hari kerja</div>
        </div>
    </div>
</div>

<div style="text-align: center; margin: 24px 0;">
    <a href="admin_dashboard.php" class="btn" style="background: #64748b; padding: 12px 30px;">
        ← Kembali ke Dashboard Admin
    </a>
</div>

<?php include 'includes/footer.php'; ?>
