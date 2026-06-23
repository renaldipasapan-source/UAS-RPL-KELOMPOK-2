# Aplikasi Peminjaman Lab Jaringan

## Cara Instalasi di Laragon

### 1. Letakkan Folder
Ekstrak folder `lab_peminjaman` ke dalam:
```
C:\laragon\www\lab_peminjaman
```

### 2. Import Database
- Buka **phpMyAdmin** di Laragon (klik kanan tray → phpMyAdmin)
- Buat database baru bernama `lab_peminjaman` (atau biarkan SQL yang buat otomatis)
- Pilih tab **Import** → pilih file `lab_peminjaman.sql` dari folder ini
- Klik **Go**

### 3. Sesuaikan Konfigurasi (jika perlu)
Edit file `includes/db.php`:
```php
define('DB_HOST', 'localhost');   // host MySQL
define('DB_USER', 'root');        // username MySQL
define('DB_PASS', '');            // password MySQL (default Laragon: kosong)
define('DB_NAME', 'lab_peminjaman');
define('BASE_URL', '/lab_peminjaman'); // sesuaikan nama folder
```

### 4. Akses Aplikasi
Buka browser: **http://localhost/lab_peminjaman**

---

## Akun Login Demo

| Role     | Nomor Identitas      | Password   |
|----------|----------------------|------------|
| Kaprodi  | 198501012010011001   | password   |
| Admin    | 199203152015012002   | password   |
| Peminjam | 20210001             | *(kosong)* |
| Peminjam | 20210002             | *(kosong)* |
| Peminjam | 20210003             | *(kosong)* |

> **Catatan:** Peminjam (mahasiswa/dosen) login hanya dengan Nomor Identitas, tanpa password.

---

## Fitur Per Role

### 👤 Peminjam
- Form peminjaman barang (pilih barang, tanggal, qty)
- Form peminjaman ruangan (pilih ruangan, waktu)
- Riwayat peminjaman pribadi
- Form pengaduan masalah

### 🛠️ Admin
- Approval/reject peminjaman barang & ruangan
- Serah terima pengembalian (catat kondisi & foto)
- Penanganan pengaduan masalah
- CRUD data barang & ruangan
- Manajemen pengguna

### 📊 Kaprodi
- Laporan seluruh peminjaman barang & ruangan (read-only)
- Laporan pengaduan masalah
- Pantau inventaris barang & status ruangan

---

## Struktur Folder
```
lab_peminjaman/
├── index.php               ← redirect otomatis
├── login.php               ← halaman login
├── logout.php
├── dashboard.php           ← dashboard (semua role)
├── lab_peminjaman.sql      ← file database
├── includes/
│   ├── db.php              ← konfigurasi database
│   ├── auth.php            ← fungsi auth & helper
│   ├── header.php          ← navbar template
│   └── footer.php
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── pages/
│   ├── peminjam/           ← halaman role peminjam
│   ├── admin/              ← halaman role admin
│   └── kaprodi/            ← halaman role kaprodi
└── uploads/                ← file upload (auto-created)
    ├── barang/
    ├── ruangan/
    ├── pengaduan/
    └── serahterima/
```
