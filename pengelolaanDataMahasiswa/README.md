# Pengelolaan Data Mahasiswa — Arsitektur MVC (Kasus 1)

Proyek ini adalah hasil refactoring modul **Pengelolaan Data Mahasiswa** (dari kode PHP campuran pada root repository `../index.php`, `../edit.php`, `../update.php`, `../delete.php`) ke dalam pola arsitektur **Model–View–Controller (MVC)** sesuai materi Pertemuan 11 Pemrograman Berbasis Web.

---

## 1. Struktur Folder MVC

```text
pengelolaanDataMahasiswa/
├── app/
│   ├── Controllers/          # (Orang 3) MahasiswaController.php
│   ├── Models/               # (Orang 2) MahasiswaModel.php
│   ├── Views/
│   │   ├── errors/           # (Orang 4) Halaman error opsional (403.php, 404.php)
│   │   └── mahasiswa/        # (Orang 4) index.php, create.php, edit.php
│   └── Router.php            # (Orang 1) Pemetaan URL + HTTP method ke Controller
├── config/
│   └── database.php          # (Orang 1) Koneksi PDO terpusat
├── database/
│   └── schema.sql            # (Orang 1) Skema tabel mahasiswa & data contoh
├── docs/
│   └── alur-request-mvc.md   # (Orang 1) Output 1: Diagram request-response & rute HTTP
├── logs/                     # Log teknis error aplikasi (diabaikan git)
├── public/
│   ├── .htaccess             # (Orang 1) Aturan rewrite Apache
│   └── index.php             # (Orang 1) Front Controller (entry point tunggal)
├── .gitignore
└── README.md
```

---

## 2. Pembagian Tugas Kelompok (5 Orang)

| Peran | Tanggung Jawab Utama |
| :--- | :--- |
| **Orang 1** *(Arsitek, DB & Router)* | Struktur folder MVC, `config/database.php`, `database/schema.sql`, `app/Router.php`, `public/index.php`, `public/.htaccess`, dan `docs/alur-request-mvc.md` (**Output 1**). |
| **Orang 2** *(Model)* | Membuat `app/Models/MahasiswaModel.php` (`all`, `find`, `existsByNim`, `create`, `update`, `delete`) menggunakan `PDO` injection dan *prepared statement*. |
| **Orang 3** *(Controller)* | Membuat `app/Controllers/MahasiswaController.php` (`index`, `create`, `store`, `edit`, `update`, `delete`), validasi input, otorisasi hapus, dan redirect PRG. |
| **Orang 4** *(View & UI)* | Membuat `app/Views/mahasiswa/*.php` (form tambah/edit, tabel daftar, pesan error) dengan `htmlspecialchars()` dan tanpa query SQL. |
| **Orang 5** *(QA & Dokumentasi)* | Menyusun dan menguji skenario valid, invalid, serta keamanan dasar (**Output 2 & Output 3**). |

---

## 3. Cara Menjalankan di Lokal

### A. Persiapan Database
1. Pastikan layanan MySQL (XAMPP/Laragon) aktif.
2. Jika belum mengimpor `../akademik1.sql`, impor file `database/schema.sql` ke MySQL (mis. melalui phpMyAdmin atau CLI).
3. Jika kredensial MySQL lokal berbeda dengan default (`host=localhost`, `dbname=akademik1`, `user=root`, `pass=''`), buat file `config/database.local.php` (sudah di-ignore oleh Git):
   ```php
   <?php
   return [
       'host' => 'localhost',
       'dbname' => 'akademik1',
       'user' => 'root',
       'pass' => '',
   ];
   ```

### B. Menjalankan Web Server Lokal
**Opsi 1 — PHP Built-in Server** (jalankan dari dalam folder `pengelolaanDataMahasiswa`):
```bash
php -S localhost:8000 -t public public/index.php
```
Lalu buka di browser: `http://localhost:8000/mahasiswa`

**Opsi 2 — XAMPP / Laragon (Apache)**
Akses melalui browser sesuai lokasi folder di `htdocs`/`www`:
```text
http://localhost/crud-mahasiswa/pengelolaanDataMahasiswa/public/mahasiswa
```

---

## 4. Kontrak Antarmuka untuk Orang 2, 3, dan 4

Agar integrasi antaranggota berjalan lancar tanpa bentrok:

1. **Model (`app/Models/MahasiswaModel.php`) — Orang 2**:
   - Constructor wajib menerima `PDO`: `public function __construct(private PDO $pdo)`
   - Method yang disepakati:
     - `all(): array`
     - `find(int $id): ?array`
     - `existsByNim(string $nim, ?int $excludeId = null): bool`
     - `create(...)`
     - `update(int $id, ...)`
     - `delete(int $id): void`

2. **Controller (`app/Controllers/MahasiswaController.php`) — Orang 3**:
   - Constructor disarankan menerima Model: `public function __construct(private MahasiswaModel $model)`
   - Action yang dipanggil oleh Router (`public/index.php`):
     - `index(): void`
     - `create(): void`
     - `store(): void`
     - `edit(int $id): void`
     - `update(int $id): void`
     - `delete(int $id): void`
   - Gunakan fungsi helper `appUrl('/mahasiswa')` yang sudah disediakan oleh `public/index.php` untuk membangun URL link atau `header('Location: ' . appUrl('/mahasiswa'))`.

3. **View (`app/Views/mahasiswa/*.php`) — Orang 4**:
   - Tidak boleh ada `PDO` atau query SQL di dalam View.
   - Semua output wajib memakai `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
   - Gunakan `appUrl(...)` pada `action` form dan `href` tautan agar bekerja baik di `php -S` maupun subdirektori XAMPP.
   - Variabel standar yang dikirim dari Controller: `$rows`, `$mahasiswa`, `$errors`, `$old`.
