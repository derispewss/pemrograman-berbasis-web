# My Daily Journal

Website artikel dan galeri berbasis pengguna dengan sistem manajemen konten (CMS) sederhana menggunakan PHP Native (PDO), MySQL, Bootstrap 5, dan jQuery AJAX.

## 🚀 Fitur Utama

### 📱 Public / Frontend
- **Responsive Landing Page**: Desain modern menggunakan Bootstrap 5.
- **Dynamic Content**: Menampilkan artikel dan galeri foto dari database.
- **Section Schedule**: Menampilkan jadwal kegiatan harian.
- **About Me**: Informasi profil pemilik.
- **Author Attribution**: Menampilkan nama penulis pada setiap artikel dan item galeri.

### 🛠 Admin Panel / Backend
- **Dashboard**: Statistik ringkas jumlah artikel dan galeri.
- **Manajemen Artikel**: 
  - CRUD (Create, Read, Update, Delete) Artikel.
  - Upload gambar artikel.
  - Pencatatan otomatis penulis artikel.
- **Manajemen Galeri**:
  - CRUD Foto Galeri.
  - Pengurutan (sorting) dan status aktif/non-aktif.
- **Manajemen User**:
  - CRUD User (Admin).
  - Tambah admin/penulis baru.
  - Upload foto profil pengguna.
  - Proteksi hapus akun sendiri.
- **Pengaturan Profil**:
  - Update nama, username, foto profil, dan password.
- **Keamanan**:
  - Login session management.
  - Password hashing (Bcrypt).
  - Proteksi akses langsung ke file sensitif.

## 📁 Struktur File

```
├── index.php             # Landing Page Utama
├── config.php            # Konfigurasi Database & Helper
├── env.php               # Konfigurasi Environment (Sensitive)
├── install.php           # Script Instalasi & Reset Database
├── .htaccess             # Konfigurasi Keamanan Apache
├── admin/
│   ├── index.php         # Dashboard Admin
│   ├── login.php         # Halaman Login
│   ├── logout.php        # Logout Handler
│   ├── api_profile.php   # API Profile Update
│   ├── includes/         # Komponen Sidebar & Navigasi
│   ├── articles/         # Modul Manajemen Artikel
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── api_*.php
│   ├── gallery/          # Modul Manajemen Galeri
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── api_*.php
│   └── users/            # Modul Manajemen Users
│       ├── index.php
│       ├── create.php
│       ├── edit.php
│       └── api_*.php
├── assets/               # CSS & JS Global
└── uploads/              # Folder Penyimpanan File
```

## 📦 Panduan Instalasi (Lokal / Hosting)

### 1. Persiapan
Pastikan server Anda memenuhi syarat:
- PHP >= 7.4
- MySQL / MariaDB
- Web Server (Apache/Nginx)
- Ekstensi PHP: PDO, GD

### 2. Konfigurasi
Copy file `env.example.php` (jika ada) atau buat file `env.php` baru:

```php
<?php
return [
    'environment' => 'development', // atau 'production'
    'db_host' => 'localhost',
    'db_name' => 'my_article',
    'db_user' => 'root',
    'db_pass' => '',
    'site_name' => 'My Daily Journal',
    // Sesuaikan URL dengan alamat lokal/domain Anda
    'site_url' => 'http://localhost/kuliah/pemrograman-berbasis-web', 
    'timezone' => 'Asia/Jakarta',
];
```

### 3. Instalasi Database
Jalankan script instalasi otomatis untuk membuat database, tabel, dan data dummy.
Akses URL:
```
http://localhost/kuliah/pemrograman-berbasis-web/install.php
```
> **PERINGATAN**: Script ini akan menghapus semua data lama di database yang dikonfigurasi!

### 4. Login Admin
Setelah instalasi berhasil, login dengan akun default:
- **Username**: `admin`
- **Password**: `admin123`

### 5. Pasca Instalasi (Hosting)
Untuk keamanan di server production:
1. Hapus file `install.php`.
2. Ubah permission folder `uploads/` menjadi 755.
3. Pastikan `env.php` tidak bisa diakses publik (sudah diproteksi `.htaccess`).
4. Segera ganti password default admin.

## 📝 Catatan Pengembang
- **Multi-User**: Setiap artikel dan foto galeri sekarang terhubung dengan user yang membuatnya (`user_id`).
- **Sidebar Dinamis**: Foto profil di sidebar admin otomatis menyesuaikan dengan user yang sedang login.
- **Optimasi Gambar**: Upload gambar mendukung format standar (JPG, PNG, WebP) dengan batasan ukuran via PHP.

## 📄 License
MIT License
