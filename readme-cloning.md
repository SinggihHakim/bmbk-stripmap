# 🚀 Panduan Cloning & Setup Project — BMBK Stripmap

Panduan lengkap untuk menjalankan project **Sistem Informasi Strip Map BMBK** dari nol di mesin lokal.

---

## ✅ Prerequisites (Software yang Harus Terinstall)

Sebelum mulai, pastikan software berikut sudah terinstall di komputer kamu:

| Software | Versi Minimal | Download |
|---|---|---|
| **PHP** | 8.0+ | https://www.php.net/downloads |
| **MySQL / MariaDB** | 5.7+ / 10.4+ | Termasuk dalam paket Laragon/XAMPP |
| **Web Server (Apache)** | — | Termasuk dalam paket Laragon/XAMPP |
| **Git** | — | https://git-scm.com/downloads |
| **Laragon** *(rekomendasi)* | Terbaru | https://laragon.org/download |

> **Rekomendasi:** Gunakan **Laragon** sebagai local development environment karena paling mudah disetup di Windows dan sudah bundel PHP + MySQL + Apache.

---

## 📋 PHP Extensions yang Harus Aktif

Cek dan aktifkan extension berikut di `php.ini`:

```ini
extension=pdo_mysql    ; Koneksi database (wajib)
extension=zip          ; Parsing file Excel .xlsx (wajib untuk import)
extension=fileinfo     ; Validasi tipe file upload (wajib untuk import)
extension=mbstring     ; Encoding string (biasanya sudah aktif)
```

**Cara cek di Laragon:**
- Klik kanan icon Laragon di taskbar → **PHP** → **php.ini**
- Cari baris extension di atas, hapus tanda `;` di depannya jika ada
- Restart Laragon setelah simpan

---

## 🪜 Langkah-langkah Setup

### Step 1 — Clone Repository

Buka terminal / Git Bash, lalu jalankan:

```bash
# Clone ke folder web server Laragon
cd C:\laragon\www

git clone https://github.com/username/bmbk-stripmap.git
```

> Ganti `https://github.com/username/bmbk-stripmap.git` dengan URL repository yang sebenarnya.

Atau jika kamu dapat file ZIP:
```bash
# Ekstrak ZIP ke folder:
C:\laragon\www\bmbk-stripmap\
```

---

### Step 2 — Buat File `.env`

File `.env` **tidak disertakan** di repository (sengaja di-gitignore). Kamu harus membuatnya secara manual.

Buat file baru bernama `.env` di root project (`C:\laragon\www\bmbk-stripmap\.env`) dengan isi:

```env
APP_NAME='Stripmap - BMBK'
APP_URL=http://localhost/bmbk-stripmap/public/
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta

DB_HOST=localhost
DB_PORT=3306
DB_NAME=stripmap_db
DB_USER=root
DB_PASS=password_mysql_kamu
```

> **Penting:** Sesuaikan `DB_PASS` dengan password MySQL lokal kamu. Jika menggunakan Laragon default, password biasanya kosong `""` atau `root`.

---

### Step 3 — Sesuaikan Konfigurasi Database

Edit file `config/database.php` sesuai MySQL lokal kamu:

```php
return [
    'host'    => 'localhost',
    'port'    => 3306,
    'dbname'  => 'stripmap_db',
    'user'    => 'root',
    'pass'    => 'password_mysql_kamu',   // <-- sesuaikan ini
    'charset' => 'utf8mb4',
];
```

---

### Step 4 — Buat Database & Import Schema

**Cara A — via phpMyAdmin (Rekomendasi untuk pemula):**

1. Buka browser → `http://localhost/phpmyadmin`
2. Klik tab **SQL**
3. Copy-paste seluruh isi file `database/schema.sql`
4. Klik **Go / Execute**

**Cara B — via MySQL CLI:**

```bash
# Masuk ke MySQL
mysql -u root -p

# Jalankan schema
source C:/laragon/www/bmbk-stripmap/database/schema.sql
```

---

### Step 5 — Import Data Contoh (Opsional)

Jika ingin langsung ada data untuk testing:

**Via phpMyAdmin:**
1. Pilih database `stripmap_db`
2. Tab **SQL** → Copy-paste isi `database/seeder.sql` → Execute

**Via MySQL CLI:**
```bash
source C:/laragon\www\bmbk-stripmap\database\seeder.sql
```

---

### Step 6 — Start Web Server

**Dengan Laragon:**
- Klik **Start All** di Laragon

**Dengan XAMPP:**
- Start **Apache** dan **MySQL** dari XAMPP Control Panel

---

### Step 7 — Buka di Browser

```
http://localhost/bmbk-stripmap/public/
```

Jika berhasil, kamu akan melihat halaman **Dashboard** dengan tampilan ringkasan kondisi jalan.

---

## ❌ Troubleshooting

### Error: "Database Connection Failed"

- Pastikan MySQL server sudah berjalan
- Cek ulang `DB_USER` dan `DB_PASS` di file `.env` dan `config/database.php`
- Pastikan database `stripmap_db` sudah dibuat (Step 4)

---

### Error: "500 Internal Server Error"

Aktifkan debug mode sementara di file `.env`:

```env
APP_DEBUG=true
```

Refresh halaman untuk melihat pesan error detail. **Jangan lupa matikan kembali** setelah selesai troubleshoot.

---

### Error saat Import Excel: "zip extension not loaded"

Aktifkan extension `zip` di `php.ini`:

```ini
extension=zip
```

Restart Apache / Laragon setelah menyimpan perubahan.

---

### Halaman tidak ditemukan (404)

Pastikan **mod_rewrite** Apache aktif dan file `.htaccess` di folder `public/` berfungsi:

**Laragon:** Secara default sudah aktif.

**XAMPP:** Edit file `C:\xampp\apache\conf\httpd.conf`, cari dan ubah:
```apache
# Dari:
AllowOverride None

# Menjadi:
AllowOverride All
```

Lalu restart Apache.

---

### URL tidak benar / redirect salah

Pastikan `APP_URL` di `.env` diakhiri dengan `/` dan mengarah ke folder `public/`:

```env
# Benar:
APP_URL=http://localhost/bmbk-stripmap/public/

# Salah (tidak ada /public):
APP_URL=http://localhost/bmbk-stripmap/
```

---

## 📁 Struktur Project

```
bmbk-stripmap/
├── app/
│   ├── controllers/       # Controller (DashboardController, RuasController, dll)
│   ├── helpers/           # Helper & utility (Database, Router, ExcelImporter, dll)
│   ├── models/            # Model (Stripmap, Ruas, dll)
│   └── services/          # Service layer (StripmapService, RuasService, dll)
├── config/
│   ├── app.php            # Konfigurasi aplikasi (baca dari .env)
│   └── database.php       # Konfigurasi koneksi database
├── database/
│   ├── schema.sql         # Struktur tabel database (jalankan ini pertama)
│   └── seeder.sql         # Data contoh (opsional)
├── public/
│   ├── index.php          # Entry point semua request
│   ├── .htaccess          # URL rewrite rules
│   └── assets/            # CSS, JS, Gambar
├── resources/
│   └── views/             # Template HTML/PHP (layouts, pages)
├── routes/
│   └── web.php            # Definisi semua URL route
├── .env                   # ⚠️ Buat manual, tidak ada di repo
├── .env.example           # Contoh konfigurasi .env (jika ada)
└── .gitignore             # File yang tidak di-track Git
```

---

## 🌐 Dependencies (Semua via CDN)

Project ini **tidak menggunakan Composer** — tidak perlu `composer install`.

Semua library frontend dimuat otomatis via CDN (perlu koneksi internet):

| Library | Fungsi |
|---|---|
| **Tailwind CSS** | Framework CSS untuk styling |
| **Alpine.js** | Interaktivitas UI ringan (sidebar, toggle) |
| **Alpine.js Collapse** | Plugin animasi collapse sidebar |
| **SweetAlert2** | Dialog konfirmasi & notifikasi toast |
| **Chart.js** | Grafik di halaman dashboard |
| **Google Fonts (Inter)** | Font utama aplikasi |

---

## ✅ Checklist Setup

Gunakan checklist ini untuk memastikan semua langkah sudah selesai:

- [ ] Git clone atau ekstrak ZIP ke `C:\laragon\www\bmbk-stripmap\`
- [ ] Buat file `.env` di root project
- [ ] Sesuaikan `DB_USER` dan `DB_PASS` di `.env` dan `config/database.php`
- [ ] PHP extension `pdo_mysql`, `zip`, `fileinfo` sudah aktif
- [ ] Jalankan `database/schema.sql` di phpMyAdmin / MySQL CLI
- [ ] *(Opsional)* Jalankan `database/seeder.sql` untuk data contoh
- [ ] Start Apache & MySQL (Laragon: klik **Start All**)
- [ ] Buka `http://localhost/bmbk-stripmap/public/` di browser
- [ ] Dashboard berhasil tampil ✅
