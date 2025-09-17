<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'config/database.php';
require 'includes/functions.php';

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$destination_id = (int)($_POST['destination_id'] ?? 0);
$daily_salary = (int)($_POST['daily_salary'] ?? 0);

date_default_timezone_set('Asia/Jakarta');

if ($action === 'checkin') {
    if ($destination_id <= 0) {
        $_SESSION['error'] = "Tujuan perjalanan wajib dipilih!";
        header("Location: index.php");
        exit;
    }

    if ($daily_salary <= 0) {
        $stmt = $pdo->prepare("SELECT amt_type FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $amt_type = $stmt->fetch()['amt_type'];
        $daily_salary = getAmtSalary($pdo, $amt_type);
    }

    $stmt = $pdo->prepare("SELECT name FROM destinations WHERE id = ?");
    $stmt->execute([$destination_id]);
    $dest = $stmt->fetch();
    if (!$dest) {
        $_SESSION['error'] = "Tujuan tidak valid!";
        header("Location: index.php");
        exit;
    }
    $destination_name = $dest['name'];

    $checkInTime = date('H:i:s');
    $status = (date('H') > 8) ? 'late' : 'present';

    $stmt = $pdo->prepare("INSERT INTO attendance (user_id, date, check_in, destination, daily_salary, status) VALUES (?, CURDATE(), ?, ?, ?, ?)");
    $stmt->execute([$user_id, $checkInTime, $destination_name, $daily_salary, $status]);

    $_SESSION['message'] = "Check-in dan tujuan perjalanan berhasil disimpan!";
} elseif ($action === 'checkout') {
    $checkOutTime = date('H:i:s');
    $stmt = $pdo->prepare("UPDATE attendance SET check_out = ? WHERE user_id = ? AND date = CURDATE()");
    $stmt->execute([$checkOutTime, $user_id]);

    $_SESSION['message'] = "Check-out berhasil!";
}

header("Location: index.php");
exit;
?>
