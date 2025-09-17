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

$message = '';

if ($_POST && isset($_POST['add_destination'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO destinations (name) VALUES (?)");
            $stmt->execute([$name]);
            $message = "<div class='message success'>✅ Tujuan baru berhasil ditambahkan!</div>";
        } catch (Exception $e) {
            $message = "<div class='message error'>❌ Tujuan sudah ada atau terjadi kesalahan.</div>";
        }
    } else {
        $message = "<div class='message error'>❌ Nama tujuan tidak boleh kosong.</div>";
    }
}

if ($_GET && isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM destinations WHERE id = ?");
    $stmt->execute([$id]);
    $message = "<div class='message success'>✅ Tujuan berhasil dihapus!</div>";
}

$stmt = $pdo->prepare("SELECT id, name FROM destinations ORDER BY name ASC");
$stmt->execute();
$destinations = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="card">
    <h2>📍 Kelola Tujuan Perjalanan</h2>

    <?= $message ?>

    <div class="form-group">
        <label>➕ Tambah Tujuan Baru</label>
        <form method="POST">
            <input type="text" name="name" placeholder="Contoh: Surabaya - Pasuruan PP" required style="margin-right:10px;">
            <button type="submit" name="add_destination" class="btn" style="padding:10px 20px; background:#059669;">
                Tambah
            </button>
        </form>
    </div>

    <h3>📋 Daftar Tujuan Tersedia</h3>
    <?php if (count($destinations) > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Nama Tujuan</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($destinations as $dest): ?>
            <tr>
                <td><?= $dest['id'] ?></td>
                <td><?= htmlspecialchars($dest['name']) ?></td>
                <td>
                    <a href="?delete_id=<?= $dest['id'] ?>" 
                       onclick="return confirm('Yakin hapus tujuan: <?= addslashes($dest['name']) ?>?')" 
                       class="btn btn-danger" 
                       style="padding:6px 12px; font-size:14px;">
                        Hapus
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center; padding:20px; color:#64748b;">Belum ada tujuan yang ditambahkan.</p>
    <?php endif; ?>
</div>

<div style="text-align:center; margin:24px 0;">
    <a href="admin_dashboard.php" class="btn" style="background:#64748b;">← Kembali ke Dashboard Admin</a>
</div>

<?php include 'includes/footer.php'; ?>
