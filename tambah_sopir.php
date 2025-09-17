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

if ($_POST) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? substr(md5(rand()), 0, 8);
    $amt_type = $_POST['amt_type'] ?? 'AMT 1';

    if (empty($name) || empty($email)) {
        $message = "<div class='message error'>❌ Nama dan email wajib diisi!</div>";
    } else {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, amt_type, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->execute([$name, $email, $hashedPassword, $amt_type]);
            
            $message = "
                <div class='message success'>
                    ✅ Sopir <strong>" . htmlspecialchars($name) . "</strong> berhasil ditambahkan!<br>
                    <small>Email: <strong>" . htmlspecialchars($email) . "</strong> | Password: <strong>" . htmlspecialchars($password) . "</strong> | Kategori: <strong>" . htmlspecialchars($amt_type) . "</strong></small>
                </div>
            ";
        } catch (Exception $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $message = "<div class='message error'>❌ Email sudah terdaftar! Gunakan email lain.</div>";
            } else {
                $message = "<div class='message error'>❌ Terjadi kesalahan: " . $e->getMessage() . "</div>";
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="card">
    <h2>➕ Tambah Sopir Baru</h2>
    <p>Isi form berikut untuk mendaftarkan sopir baru ke sistem.</p>
</div>

<?= $message ?>

<div class="card">
    <form method="POST" style="max-width: 600px; margin: 0 auto;">
        <div class="form-group">
            <label>👤 Nama Lengkap Sopir</label>
            <input 
                type="text" 
                name="name" 
                required 
                placeholder="Contoh: Budi Santoso" 
                value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                style="font-size: 16px; padding: 14px;"
            >
        </div>
        
        <div class="form-group">
            <label>📧 Email (Username Login)</label>
            <input 
                type="email" 
                name="email" 
                required 
                placeholder="sopir@armada.com" 
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                style="font-size: 16px; padding: 14px;"
            >
            <small style="color: #64748b;">Email akan digunakan sebagai username untuk login</small>
        </div>
        
        <div class="form-group">
            <label>🔒 Password (Opsional)</label>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input 
                    type="text" 
                    name="password" 
                    placeholder="Kosongkan untuk generate otomatis" 
                    value="<?= isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '' ?>"
                    style="flex: 1; font-size: 16px; padding: 14px;"
                >
                <button type="button" class="btn" onclick="generatePassword()" style="padding: 10px 15px; white-space: nowrap;">
                    🔄 Generate
                </button>
            </div>
            <small style="color: #64748b;">Jika kosong, sistem akan generate password acak otomatis</small>
        </div>
        
        <div class="form-group">
            <label>🚛 Kategori AMT</label>
            <select 
                name="amt_type" 
                class="form-control" 
                required 
                style="padding: 14px; font-size: 16px; border-radius: 12px;"
            >
                <option value="AMT 1" <?= (isset($_POST['amt_type']) && $_POST['amt_type'] == 'AMT 1') ? 'selected' : '' ?>>AMT 1</option>
                <option value="AMT 2" <?= (isset($_POST['amt_type']) && $_POST['amt_type'] == 'AMT 2') ? 'selected' : '' ?>>AMT 2</option>
            </select>
            <small style="color: #64748b;">Pilih kategori AMT untuk menentukan gaji dasar sopir</small>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn" style="padding: 16px 40px; font-size: 18px; font-weight: 600; background: #1e3a8a;">
                ✅ Tambah Sopir
            </button>
        </div>
    </form>
</div>

<div class="card">
    <h3>📌 Catatan Penting</h3>
    <ul style="padding-left: 20px; line-height: 1.8; color: #334155;">
        <li>Password default akan digenerate otomatis jika Anda tidak mengisinya</li>
        <li>Sopir bisa mengganti password sendiri setelah login pertama kali</li>
        <li>Email harus unik dan belum terdaftar di sistem</li>
        <li>Setelah ditambahkan, sopir bisa langsung login dan mulai absen</li>
    </ul>
</div>

<div style="text-align: center; margin: 24px 0;">
    <a href="admin_dashboard.php" class="btn" style="background: #64748b; padding: 12px 30px;">
        ← Kembali ke Dashboard Admin
    </a>
</div>

<script>
function generatePassword() {
    const length = 8;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    let password = "";
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    document.querySelector('input[name="password"]').value = password;
}
</script>

<?php include 'includes/footer.php'; ?>
