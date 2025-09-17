<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit;
}

require 'config/database.php';
require 'autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetch()['role'] ?? 'user';

if ($userRole !== 'admin') {
    die("<h2 style='color:red; text-align:center;'>❌ Akses Ditolak</h2>");
}

$bulan = $_GET['bulan'] ?? date('n');
$tahun = $_GET['tahun'] ?? date('Y');

// Ambil data gaji bulanan semua sopir
$stmt = $pdo->prepare("
    SELECT 
        u.name AS nama_sopir,
        u.amt_type AS kategori_amt,
        u.email,
        COUNT(a.id) AS hari_kerja,
        SUM(a.daily_salary) AS total_gaji
    FROM users u
    LEFT JOIN attendance a ON u.id = a.user_id 
        AND YEAR(a.date) = ? 
        AND MONTH(a.date) = ?
        AND a.status IN ('present', 'late')
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY total_gaji DESC, u.name ASC
");
$stmt->execute([$tahun, $bulan]);
$data = $stmt->fetchAll();

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Judul laporan
$sheet->setCellValue('A1', 'LAPORAN REKAP GAJI SOPIR ARMADA');
$sheet->setCellValue('A2', 'Bulan: ' . date('F Y', strtotime("$tahun-$bulan-01")));
$sheet->setCellValue('A3', 'Tanggal Export: ' . date('d M Y H:i:s'));
$sheet->setCellValue('A4', 'Export oleh: ' . $_SESSION['user_name']);

// Header tabel
$sheet->setCellValue('A6', 'No');
$sheet->setCellValue('B6', 'Nama Sopir');
$sheet->setCellValue('C6', 'Kategori AMT');
$sheet->setCellValue('D6', 'Email');
$sheet->setCellValue('E6', 'Hari Kerja');
$sheet->setCellValue('F6', 'Total Gaji (Rp)');

// Style header
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1e3a8a']
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
];

$sheet->getStyle('A6:F6')->applyFromArray($headerStyle);
$sheet->getRowDimension(6)->setRowHeight(30);

// Isi data
$row = 7;
$no = 1;
foreach ($data as $d) {
    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $d['nama_sopir']);
    $sheet->setCellValue('C' . $row, $d['kategori_amt']);
    $sheet->setCellValue('D' . $row, $d['email']);
    $sheet->setCellValue('E' . $row, $d['hari_kerja']);
    $sheet->setCellValue('F' . $row, $d['total_gaji']);
    $row++;
}

// Style data
$dataStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => 'D9D9D9']
        ]
    ],
    'alignment' => [
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
    ]
];

if ($row > 7) {
    $sheet->getStyle('A7:F' . ($row - 1))->applyFromArray($dataStyle);
}

// Format kolom angka
$sheet->getStyle('E7:F' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

// Auto width kolom
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Style judul
$titleStyle = [
    'font' => [
        'bold' => true,
        'size' => 16,
        'color' => ['rgb' => '1e3a8a']
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    ]
];
$sheet->getStyle('A1:F1')->applyFromArray($titleStyle);
$sheet->mergeCells('A1:F1');

$subTitleStyle = [
    'font' => [
        'bold' => true,
        'size' => 12,
        'color' => ['rgb' => '374151']
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    ]
];
$sheet->getStyle('A2:F2')->applyFromArray($subTitleStyle);
$sheet->mergeCells('A2:F2');

$sheet->getStyle('A3:F3')->applyFromArray($subTitleStyle);
$sheet->mergeCells('A3:F3');

$sheet->getStyle('A4:F4')->applyFromArray($subTitleStyle);
$sheet->mergeCells('A4:F4');

// Freeze header
$sheet->freezePane('A7');

// Set nama sheet
$sheet->setTitle("Rekap Gaji " . date('F Y', strtotime("$tahun-$bulan-01")));

// Header untuk download file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="rekap_gaji_armada_' . $tahun . '_' . $bulan . '.xlsx"');
header('Cache-Control: max-age=0');

// Export ke browser
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
