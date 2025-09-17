# Absensi-BAP
# 🚛 Aplikasi Absensi Sopir Armada (AMT 1 & AMT 2)

Aplikasi berbasis web untuk absensi harian sopir armada pengangkutan, dengan fitur:
- Absen check-in/check-out via HP/tablet
- Pilih tujuan perjalanan dari dropdown (diatur admin)
- Gaji berbeda per kategori AMT (AMT 1 / AMT 2)
- Rekapan mingguan & bulanan + grafik
- Export gaji ke Excel
- Responsive modern UI — nyaman dipakai di HP

---

## 🎯 FITUR UTAMA

### 👤 Untuk Sopir:
- Login & absen harian
- Pilih tujuan perjalanan dari dropdown
- Lihat rekap gaji bulanan
- Tampilan mobile-friendly

### 👨‍💼 Untuk Admin:
- Kelola daftar tujuan perjalanan (tambah/hapus)
- Atur gaji dasar per AMT
- Tambah/hapus sopir
- Lihat dashboard & statistik
- Export rekap gaji ke Excel

---

## 📁 STRUKTUR FOLDER
absensi-sopir-armada/
+-- PhpSpreadsheet/              ? Library Excel (manual, hasil download GitHub)
+-- assets/
¦   +-- css/
¦       +-- style.css            ? CSS utama (responsive & modern)
+-- config/
¦   +-- database.php             ? Koneksi database (dibuat otomatis oleh installer)
+-- includes/
¦   +-- header.php               ? Header template (panggil CSS & JS)
¦   +-- footer.php               ? Footer template
¦   +-- functions.php            ? Fungsi bantu (hitung gaji, dll)
+-- install.php                  ? Installer otomatis (jalankan sekali)
+-- login.php                    ? Login untuk sopir
+-- admin_login.php              ? Login khusus admin
+-- logout.php                   ? Logout (hancurkan session)
+-- index.php                    ? Dashboard sopir (absen + pilih tujuan)
+-- absen.php                    ? Proses absen (check-in/check-out)
+-- weekly_report.php            ? Laporan mingguan (grafik + tabel)
+-- monthly_report.php           ? Laporan bulanan (grafik + tabel)
+-- admin_dashboard.php          ? Dashboard admin (statistik & shortcut)
+-- kelola_gaji.php              ? Kelola gaji dasar AMT 1 & AMT 2
+-- kelola_tujuan.php            ? Kelola daftar tujuan (dropdown)
+-- tambah_sopir.php             ? Tambah sopir baru
+-- hapus_sopir.php              ? Hapus sopir (termasuk data absensi)
+-- export_gaji.php              ? Export rekap gaji ke Excel (.xlsx)
+-- autoload.php                 ? Autoloader manual untuk PhpSpreadsheet
+-- README.md                    ? Dokumentasi lengkap (instalasi, fitur, struktur)

---

## 🚀 CARA INSTALASI

### 1. Upload ke Hosting
Upload semua file ke folder `public_html` atau subfolder di hosting Anda.

### 2. Jalankan Installer
Buka di browser:
https://domainanda.com/install.php

### 3. Isi Form Instalasi
- Host Database: `localhost` (biasanya)
- Nama Database: `absensi_armada` (atau nama lain)
- Username & Password: sesuai akun MySQL hosting
- Email & Password Admin: untuk login admin nanti

### 4. Selesai!
Setelah sukses, **HAPUS FILE `install.php`** demi keamanan.

Login:
- Sopir: `login.php`
- Admin: `admin_login.php`

---

## 🔐 AKUN ADMIN DEFAULT (Jika Tidak Diubah Saat Instalasi)

- Email: `admin@armada.com`
- Password: `password`

---

## 📥 DEPENDENCY

Aplikasi ini menggunakan **PhpSpreadsheet** untuk export Excel.

### Cara Install (Manual):
1. Download dari: https://github.com/PHPOffice/PhpSpreadsheet/releases
2. Ekstrak, lalu copy folder `src/PhpSpreadsheet` ke root project Anda
3. Rename folder menjadi `PhpSpreadsheet` (tanpa `src`)
4. File `autoload.php` sudah disediakan — tidak perlu composer

Struktur akhir:
absensi-sopir-armada/
├── PhpSpreadsheet/ ← folder hasil ekstrak
├── autoload.php ← sudah ada
└── ... (file lain)

---

## 🛠️ CUSTOMISASI

- Ubah warna/theme: edit CSS di `includes/header.php`
- Ubah jam telat: edit di `absen.php` — `date('H') > 8`
- Tambah fitur: hubungi developer

---

## 📱 RESPONSIVE

Aplikasi ini 100% responsive — bisa dipakai di:
- HP Android/iOS
- Tablet
- Laptop/Desktop

---

## ✅ VERSI

v1.0.0 — Absensi Sopir Armada AMT 1 & AMT 2
