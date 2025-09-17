<?php
function calculateMonthlySalary($pdo, $user_id, $year = null, $month = null) {
    if (!$year) $year = date('Y');
    if (!$month) $month = date('m');

    $stmt = $pdo->prepare("
        SELECT SUM(daily_salary) as total_salary, COUNT(*) as working_days
        FROM attendance 
        WHERE user_id = ? 
          AND YEAR(date) = ? 
          AND MONTH(date) = ? 
          AND status IN ('present', 'late')
    ");
    $stmt->execute([$user_id, $year, $month]);
    $result = $stmt->fetch();

    return [
        'working_days' => $result['working_days'] ?? 0,
        'total_salary' => $result['total_salary'] ?? 0
    ];
}

function getAmtSalary($pdo, $amt_type) {
    $default = $amt_type === 'AMT 1' ? 250000 : 200000;
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = ?");
    $stmt->execute(["gaji_{$amt_type}"]);
    $row = $stmt->fetch();
    return $row ? (int)$row['value'] : $default;
}

function saveSetting($pdo, $name, $value) {
    $stmt = $pdo->prepare("INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
    $stmt->execute([$name, $value, $value]);
}

function getSetting($pdo, $name, $default = '') {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = ?");
    $stmt->execute([$name]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : $default;
}
?>
