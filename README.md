# My Daily Journal

Website artikel sederhana dengan sistem CRUD menggunakan PHP, MySQL, Bootstrap 5, dan jQuery AJAX.

## 🚀 Fitur

- ✅ Landing page responsif dengan Bootstrap 5
- ✅ CRUD artikel dengan jQuery AJAX
- ✅ Upload gambar
- ✅ Sistem login dengan session
- ✅ Admin dashboard
- ✅ Optimasi untuk hosting

## 📁 Struktur File

```
├── index.php           # Homepage
├── config.php          # Konfigurasi utama
├── env.php             # Konfigurasi environment (JANGAN UPLOAD)
├── env.example.php     # Template environment
├── install.php         # Script instalasi (HAPUS SETELAH INSTALL)
├── 404.php             # Halaman error 404
├── 500.php             # Halaman error 500
├── .htaccess           # Konfigurasi Apache
├── admin/
│   ├── index.php       # Dashboard admin
│   ├── login.php       # Halaman login
│   ├── logout.php      # Logout handler
│   ├── create.php      # Form tambah artikel
│   ├── edit.php        # Form edit artikel
│   ├── delete.php      # Hapus artikel
│   ├── api_create.php  # API endpoint create
│   └── api_update.php  # API endpoint update
├── assets/
│   ├── css/style.css   # Custom CSS
│   └── js/script.js    # Custom JavaScript
├── uploads/            # Folder upload gambar
└── logs/               # Folder log error
```

## 📦 Instalasi di Hosting

### 1. Upload File
Upload semua file **KECUALI**:
- `env.php` (buat baru di server)
- `init_db.php` (tidak perlu)
- `.git/` folder

### 2. Konfigurasi Environment
Buat file `env.php` di server dengan mengcopy dari `env.example.php`:

```php
<?php
return [
    'environment' => 'production',
    'db_host' => 'localhost',
    'db_name' => 'nama_database_anda',
    'db_user' => 'username_database',
    'db_pass' => 'password_database',
    'site_name' => 'My Daily Journal',
    'site_url' => 'https://yourdomain.com',
    'timezone' => 'Asia/Jakarta',
];
```

### 3. Jalankan Instalasi
Akses URL berikut (ganti tanggal sesuai hari ini format YYYYMMDD):
```
https://yourdomain.com/install.php?key=install_20260108
```

### 4. PENTING: Hapus File Instalasi
Setelah instalasi selesai, **HAPUS** file berikut:
- `install.php`
- `init_db.php`

### 5. Ganti Password Admin
Login dengan:
- Username: `admin`
- Password: `admin123`

**Segera ganti password default!**

## 🔒 Keamanan

- File sensitif (`env.php`, `config.php`) dilindungi via `.htaccess`
- Session menggunakan `httponly` dan `secure` cookies
- CSRF token tersedia (belum diimplementasikan di semua form)
- Error tidak ditampilkan di production

## 💡 Tips Hosting

1. **Pastikan PHP >= 7.4**
2. **Aktifkan ekstensi PDO MySQL**
3. **Set permission folder `uploads/` ke 755 atau 775**
4. **Uncomment HTTPS redirect di `.htaccess` jika menggunakan SSL**

## 📝 License

MIT License
