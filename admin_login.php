<?php
session_start();
if (isset($_SESSION['user_id'])) {
    require 'config/database.php';
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $role = $stmt->fetch()['role'] ?? 'user';
    if ($role === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

require 'config/database.php';
$error = '';

if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Email atau password salah, atau bukan akun admin!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Absensi Armada</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #7c2d12, #dc2626);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }
        .login-container h2 {
            color: #7c2d12;
            margin-bottom: 30px;
            font-size: 2rem;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 24px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #334155;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            font-family: inherit;
            transition: border 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #dc2626;
        }
        button {
            width: 100%;
            padding: 16px;
            background: #7c2d12;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin: 10px 0;
            transition: all 0.3s;
        }
        button:hover {
            background: #991b1b;
            transform: translateY(-2px);
        }
        .links {
            margin-top: 25px;
            font-size: 14px;
        }
        .links a {
            color: #dc2626;
            text-decoration: none;
            font-weight: 500;
            margin: 0 8px;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .error {
            background: #fee;
            color: #c00;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
            font-size: 14px;
        }
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                margin: 10px;
            }
            .login-container h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🔐 Login Admin</h2>

        <?php if ($error): ?>
            <div class="error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Admin</label>
                <input type="email" name="email" required autofocus placeholder="admin@armada.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit">Login sebagai Admin</button>
        </form>

        <div class="links">
            <a href="login.php">← Login sebagai Sopir</a>
        </div>
    </div>
</body>
</html>
