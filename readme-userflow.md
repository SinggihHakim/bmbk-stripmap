# 📖 User Flow — Sistem Informasi Strip Map BMBK

Dokumen ini menjelaskan alur penggunaan aplikasi **Sistem Informasi Preservasi Jalan BMBK** (Bina Marga & Bina Konstruksi) Provinsi Lampung secara lengkap dari awal hingga akhir.

---

## 🗺️ Peta Halaman (Sitemap)

```
/                          → Dashboard (Ringkasan Global)
├── /dashboard/detail      → Detail kondisi jalan per kategori
├── /ruas                  → Daftar Ruas Jalan
│   ├── /ruas/create       → Form Tambah Ruas Jalan
│   ├── /ruas/import       → Import Data via Excel / CSV
│   ├── /ruas/show/{id}    → Detail Ruas Jalan
│   └── /ruas/edit/{id}    → Edit Ruas Jalan
├── /stripmap/{id}         → Manajemen Strip Map & Perkerasan per Ruas
│   ├── /stripmap/create/{id}  → Tambah Segmen Strip Map / Perkerasan
│   ├── /stripmap/edit/{id}    → Edit Segmen Strip Map
│   ├── /perkerasan/edit/{id}  → Edit Segmen Perkerasan
│   └── /stripmap/preview/{id} → Preview Visual Strip Map
├── /rekap/kemantapan      → Rekapitulasi Kemantapan Jalan
├── /rekap/perkerasan      → Rekapitulasi Jenis Perkerasan
└── /export                → Pusat Export & Cetak
```

---

## 1. 🏠 Dashboard

**URL:** `/` atau `/public/`

Halaman pertama yang muncul saat membuka aplikasi. Berisi ringkasan kondisi seluruh jaringan jalan secara agregat.

### Yang Ditampilkan:
| Informasi | Keterangan |
|---|---|
| **Total Ruas Jalan** | Jumlah ruas yang terdaftar di sistem |
| **Total Panjang Jalan (km)** | Akumulasi panjang seluruh ruas |
| **Panjang Kondisi Baik** | Dalam km & persentase |
| **Panjang Kondisi Sedang** | Dalam km & persentase |
| **Panjang Rusak Ringan** | Dalam km & persentase |
| **Panjang Rusak Berat** | Dalam km & persentase |
| **Persentase Kemantapan** | Baik + Sedang |
| **Persentase Tidak Mantap** | Rusak Ringan + Rusak Berat |

### Grafik yang Tersedia:
- **Pie Chart Kondisi Jalan** — persentase kemantapan global
- **Pie Chart Jenis Perkerasan** — komposisi Rigid / Aspal / Agregat / Belum Tembus
- **Bar Chart per Kabupaten/Kota** — kondisi jalan per wilayah
- **Bar Chart per Koridor** — kondisi jalan per koridor
- **Bar Chart per UPTD** — kondisi jalan per unit pengelola

### Aksi dari Dashboard:
- Klik angka **Rusak Ringan / Rusak Berat / Baik / Sedang / Mantap / Tidak Mantap** → masuk ke halaman **Detail Kondisi**
- Navigasi melalui **sidebar** ke modul lain

---

## 2. 📊 Halaman Detail Kondisi

**URL:** `/dashboard/detail?kondisi={kondisi}`

Parameter `kondisi` yang tersedia: `baik`, `sedang`, `rusak_ringan`, `rusak_berat`, `mantap`, `tidak_mantap`.

### Yang Ditampilkan:
- Tabel seluruh ruas jalan beserta panjang segmen untuk kondisi yang dipilih
- Filter kondisi dapat diganti via tab di bagian atas halaman
- Persentase tiap ruas terhadap total panjangnya

---

## 3. 🛣️ Manajemen Ruas Jalan

**URL:** `/ruas`

Pusat pengelolaan data ruas jalan. Semua operasi CRUD dilakukan dari sini.

### 3.1 Melihat Daftar Ruas

Menampilkan tabel semua ruas jalan dengan kolom:
- Kode Ruas, Nama Ruas, Koridor, Kabupaten/Kota
- STA Awal & STA Akhir, Panjang Total
- Aksi: **Detail**, **Edit**, **Strip Map**, **Hapus**

### 3.2 Menambah Ruas Jalan Baru

**URL:** `/ruas/create`

**Langkah-langkah:**

1. Klik tombol **"+ Tambah Ruas"** di halaman daftar ruas
2. Isi form **Informasi Ruas Jalan:**
   - Kode Ruas *(wajib, unik)*
   - Nama Ruas *(wajib)*
   - Koridor
   - Kabupaten / Kota
3. *(Opsional)* Isi tabel **Data Strip Map (Kondisi Jalan):**
   - Klik **"+ Tambah Baris"** untuk menambah segmen
   - Isi: STA Awal, STA Akhir, Panjang (otomatis), Baik, Sedang, Rusak Ringan, Rusak Berat
4. *(Opsional)* Isi tabel **Data Jenis Perkerasan:**
   - Klik **"+ Tambah Baris"** untuk menambah segmen perkerasan
   - Isi: STA Awal, STA Akhir, Panjang (otomatis), Rigid, Aspal, Agregat/Tanah, Belum Tembus
5. Klik **"Simpan Ruas Jalan"**

> **Catatan:** Format STA menggunakan notasi `KM+M`, contoh: `0+000`, `5+200`, `10+500`

### 3.3 Import Data via Excel / CSV

**URL:** `/ruas/import`

Cara alternatif memasukkan data dalam jumlah besar sekaligus.

**Langkah-langkah:**

1. Klik tombol **"Import Excel"** di halaman daftar ruas
2. Download **template Excel** yang disediakan *(jika tersedia)*
3. Isi data sesuai format kolom template
4. Upload file `.xlsx` atau `.csv`
5. Klik **"Proses Import"**
6. Sistem akan memvalidasi dan menyimpan data otomatis

> **Peringatan:** File yang didukung: `.xlsx` (Excel) dan `.csv`. Pastikan PHP extension `zip` dan `fileinfo` aktif.

### 3.4 Melihat Detail Ruas

**URL:** `/ruas/show/{id}`

Menampilkan:
- Informasi lengkap ruas jalan
- Tabel semua segmen strip map (kondisi jalan)
- Ringkasan kondisi (total & persentase)
- Tabel semua segmen perkerasan
- Ringkasan perkerasan (total & persentase)

### 3.5 Edit Ruas Jalan

**URL:** `/ruas/edit/{id}`

Form yang sama dengan tambah ruas, sudah terisi data yang ada. Setelah diubah klik **"Simpan Perubahan"**.

### 3.6 Hapus Ruas Jalan

Klik tombol **"Hapus"** di tabel daftar ruas → konfirmasi dialog → ruas beserta semua data strip map dan perkerasan terhapus otomatis *(CASCADE DELETE)*.

---

## 4. 📐 Manajemen Strip Map & Perkerasan

**URL:** `/stripmap/{ruas_id}`

Halaman khusus untuk mengelola segmen strip map dan perkerasan dari ruas jalan tertentu secara lebih detail.

### Yang Ditampilkan:
- **Info Ruas**: nama, STA, panjang total, koridor, kabupaten
- **Peta Rute** *(jika data KML/KMZ sudah diimport)*
- **Tabel Strip Map Kondisi Jalan** — daftar segmen dengan aksi Edit, Hapus, Sisipkan Segmen
- **Ringkasan Kondisi** — persentase Baik / Sedang / Rusak Ringan / Rusak Berat
- **Tabel Perkerasan** — daftar segmen dengan aksi Edit, Hapus
- **Ringkasan Perkerasan** — persentase Rigid / Aspal / Agregat / Belum Tembus

### 4.1 Tambah Segmen Strip Map / Perkerasan

**URL:** `/stripmap/create/{ruas_id}`

1. Klik **"+ Tambah Segmen"** di halaman strip map
2. Isi tabel segmen strip map dan / atau perkerasan
3. Klik **"Simpan Segmen"**

### 4.2 Sisipkan Segmen di Antara Dua Segmen

Klik ikon **"Sisipkan"** pada baris segmen tertentu → form create terbuka dengan STA Awal otomatis terisi dari STA Akhir segmen sebelumnya.

### 4.3 Edit Segmen Strip Map

**URL:** `/stripmap/edit/{id}`

Ubah nilai STA atau kondisi satu segmen.

### 4.4 Edit Segmen Perkerasan

**URL:** `/perkerasan/edit/{id}`

Ubah nilai STA atau jenis perkerasan satu segmen.

### 4.5 Hapus Segmen

Tombol **"Hapus"** pada baris segmen → konfirmasi → segmen dihapus, STA ruas otomatis tersinkronisasi ulang.

### 4.6 Import Rute KML / KMZ

Di halaman strip map, terdapat tombol **"Import KML/KMZ"**:
1. Pilih file `.kml` atau `.kmz` rute jalan
2. Sistem mengekstrak koordinat polyline
3. Rute tampil di peta

> Data strip map & perkerasan yang sudah ada **tidak ikut terhapus** saat import KML.

### 4.7 Preview Strip Map Visual

**URL:** `/stripmap/preview/{ruas_id}`

Menampilkan visualisasi strip map dalam format diagram horizontal berwarna:
- 🟢 **Hijau** = Baik
- 🟡 **Kuning** = Sedang
- 🟠 **Orange** = Rusak Ringan
- 🔴 **Merah** = Rusak Berat

---

## 5. 📋 Rekapitulasi Eksekutif

### 5.1 Kemantapan Jalan

**URL:** `/rekap/kemantapan`

Laporan agregat kondisi kemantapan jalan dengan breakdown:
- Per Ruas Jalan (tabel lengkap)
- Per Kabupaten / Kota
- Per Koridor
- Per UPTD (Unit Pelaksana Teknis Daerah)

### 5.2 Jenis Perkerasan

**URL:** `/rekap/perkerasan`

Laporan agregat jenis perkerasan dengan breakdown:
- Per Ruas Jalan
- Per Kabupaten / Kota
- Per Koridor
- Per UPTD

---

## 6. 📤 Export & Cetak

**URL:** `/export`

Pusat export laporan untuk keperluan dokumentasi dan pelaporan resmi.

| Format | Konten |
|---|---|
| **Print / PDF** | Dashboard ringkasan (via browser print) |
| **Preview Strip Map** | Visualisasi strip map per ruas, siap cetak |

---

## 🔄 Alur Kerja Lengkap (Happy Path)

```
1. Buka Aplikasi
        ↓
2. Dashboard → Lihat ringkasan kondisi jalan
        ↓
3. Sidebar: "Data Ruas" → Daftar Ruas Jalan
        ↓
4. Klik "+ Tambah Ruas" → Isi form ruas + strip map + perkerasan → Simpan
        ↓
5. Klik "Strip Map" pada ruas → Kelola segmen secara individual
        ↓
6. (Opsional) Import KML/KMZ → Rute tampil di peta
        ↓
7. (Opsional) Preview Strip Map → Cek visualisasi diagram
        ↓
8. Sidebar: "Rekapitulasi" → Cek laporan kemantapan & perkerasan
        ↓
9. Sidebar: "Export" → Cetak / ekspor laporan
```

---

## ⌨️ Notasi STA (Station)

Aplikasi ini menggunakan format **KM+M** untuk menyatakan posisi sepanjang ruas jalan:

| Input | Arti |
|---|---|
| `0+000` | 0 meter dari awal ruas |
| `1+500` | 1.500 meter = 1,5 km |
| `10+750` | 10.750 meter = 10,75 km |

> Sistem otomatis menghitung panjang segmen dari selisih STA Awal dan STA Akhir.
