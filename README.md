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
├── assets/ → CSS (kosong — inline CSS di header)
├── config/ → Konfigurasi database (auto-generated)
├── includes/ → Komponen header, footer, fungsi
├── install.php → ✅ Installer otomatis
├── login.php → Login sopir
├── admin_login.php → Login admin
├── index.php → Dashboard sopir
├── absen.php → Proses absen
├── kelola_tujuan.php → Kelola dropdown tujuan
├── kelola_gaji.php → Atur gaji AMT 1 & AMT 2
├── tambah_sopir.php → Tambah sopir baru
├── export_gaji.php → Export Excel
└── README.md → Dokumentasi ini

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
