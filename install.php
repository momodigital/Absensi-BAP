<?php
session_start();

if (file_exists('config/database.php') && isset($_SESSION['installed']) && $_SESSION['installed'] === true) {
    die('<div style="font-family:Arial; max-width:600px; margin:50px auto; padding:30px; background:#e8f5e9; border-radius:10px; text-align:center;">
        <h2 style="color:#2e7d32;">✅ Instalasi sudah selesai!</h2>
        <p>Aplikasi siap digunakan.</p>
        <a href="login.php" style="display:inline-block; margin-top:20px; padding:10px 20px; background:#1976d2; color:white; text-decoration:none; border-radius:5px;">→ Login Sopir</a>
        <br><br>
        <a href="admin_login.php" style="display:inline-block; margin-top:10px; padding:10px 20px; background:#d32f2f; color:white; text-decoration:none; border-radius:5px;">→ Login Admin</a>
    </div>');
}

$message = '';
$error = '';

if ($_POST && isset($_POST['db_host'])) {
    $db_host = trim($_POST['db_host']);
    $db_name = trim($_POST['db_name']);
    $db_user = trim($_POST['db_user']);
    $db_pass = $_POST['db_pass'];
    $admin_email = trim($_POST['admin_email']);
    $admin_password = $_POST['admin_password'];

    if (empty($db_host) || empty($db_name) || empty($db_user) || empty($admin_email) || empty($admin_password)) {
        $error = "Semua field wajib diisi!";
    } else {
        try {
            $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$db_name`");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `users` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL,
                    `email` VARCHAR(100) UNIQUE NOT NULL,
                    `password` VARCHAR(255) NOT NULL,
                    `amt_type` ENUM('AMT 1', 'AMT 2') DEFAULT 'AMT 1',
                    `role` ENUM('user','admin') DEFAULT 'user',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `attendance` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT NOT NULL,
                    `date` DATE NOT NULL,
                    `check_in` TIME DEFAULT NULL,
                    `check_out` TIME DEFAULT NULL,
                    `destination` TEXT NOT NULL,
                    `daily_salary` INT NOT NULL,
                    `status` ENUM('present','late','absent') DEFAULT 'present',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `destinations` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL UNIQUE,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $pdo->exec("INSERT IGNORE INTO destinations (name) VALUES 
                ('Surabaya - Gresik PP'),
                ('Surabaya - Sidoarjo PP'),
                ('Surabaya - Malang PP'),
                ('Surabaya - Mojokerto PP'),
                ('Dalam Kota Surabaya')");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `settings` (
                    `name` VARCHAR(100) PRIMARY KEY,
                    `value` TEXT NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $pdo->prepare("INSERT IGNORE INTO settings (name, value) VALUES (?, ?)")->execute(['gaji_AMT 1', '250000']);
            $pdo->prepare("INSERT IGNORE INTO settings (name, value) VALUES (?, ?)")->execute(['gaji_AMT 2', '200000']);

            $hashedPassword = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$admin_email]);
            if (!$stmt->fetch()) {
                $pdo->prepare("
                    INSERT INTO users (name, email, password, role) 
                    VALUES (?, ?, ?, 'admin')
                ")->execute([
                    'Admin Armada',
                    $admin_email,
                    $hashedPassword
                ]);
            }

            $configContent = "<?php\n\$host = '" . addslashes($db_host) . "';\n";
            $configContent .= "\$dbname = '" . addslashes($db_name) . "';\n";
            $configContent .= "\$username = '" . addslashes($db_user) . "';\n";
            $configContent .= "\$password = '" . addslashes($db_pass) . "';\n\n";
            $configContent .= "try {\n";
            $configContent .= "    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8mb4\", \$username, \$password);\n";
            $configContent .= "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
            $configContent .= "} catch (PDOException \$e) {\n";
            $configContent .= "    die(\"Koneksi gagal: \" . \$e->getMessage());\n";
            $configContent .= "}\n";
            $configContent .= "?>";

            if (!is_dir('config')) mkdir('config', 0755, true);
            file_put_contents('config/database.php', $configContent);

            $_SESSION['installed'] = true;
            $message = "✅ Instalasi berhasil! Database, akun admin, tujuan default, dan pengaturan gaji telah dibuat.";
        } catch (Exception $e) {
            $error = "Instalasi gagal: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi Absensi Sopir Armada</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #1e3a8a; margin-bottom: 30px; font-size: 1.8em; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        button { width: 100%; padding: 15px; background: #1e3a8a; color: white; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; margin-top: 20px; transition: background 0.3s; }
        button:hover { background: #1e40af; }
        .message { padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center; }
        .success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .note { background: #fff3e0; padding: 15px; border-radius: 5px; margin: 20px 0; font-size: 14px; color: #e65100; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Instalasi Otomatis</h1>

        <?php if ($message): ?>
            <div class="message success">
                <?= $message ?>
                <p><a href="login.php" style="color:#1976d2; text-decoration:underline; font-weight:bold;">→ Login sebagai Sopir</a></p>
                <p><a href="admin_login.php" style="color:#d32f2f; text-decoration:underline; font-weight:bold;">→ Login sebagai Admin</a></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error">
                ❌ <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (!$message): ?>
            <div class="note">
                <strong>📝 Catatan Instalasi:</strong><br>
                - Siapkan database di cPanel/phpMyAdmin (opsional — script akan buat otomatis)<br>
                - Isi data koneksi database hosting Anda<br>
                - Akun admin dan tujuan default akan dibuat otomatis
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>📍 Host Database</label>
                    <input type="text" name="db_host" value="<?= isset($_POST['db_host']) ? htmlspecialchars($_POST['db_host']) : 'localhost' ?>" required placeholder="localhost">
                </div>

                <div class="form-group">
                    <label>🗃️ Nama Database</label>
                    <input type="text" name="db_name" value="<?= isset($_POST['db_name']) ? htmlspecialchars($_POST['db_name']) : 'absensi_armada' ?>" required placeholder="absensi_armada">
                </div>

                <div class="form-group">
                    <label>🔑 Username Database</label>
                    <input type="text" name="db_user" value="<?= isset($_POST['db_user']) ? htmlspecialchars($_POST['db_user']) : 'root' ?>" required placeholder="root">
                </div>

                <div class="form-group">
                    <label>🔒 Password Database</label>
                    <input type="password" name="db_pass" placeholder="(kosongkan jika tidak ada password)">
                </div>

                <hr>

                <div class="form-group">
                    <label>📧 Email Admin</label>
                    <input type="email" name="admin_email" value="<?= isset($_POST['admin_email']) ? htmlspecialchars($_POST['admin_email']) : 'admin@armada.com' ?>" required placeholder="admin@armada.com">
                </div>

                <div class="form-group">
                    <label>🔐 Password Admin</label>
                    <input type="text" name="admin_password" value="<?= isset($_POST['admin_password']) ? htmlspecialchars($_POST['admin_password']) : 'password' ?>" required placeholder="password">
                </div>

                <button type="submit">🚀 Jalankan Instalasi</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
