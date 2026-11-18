# Bagian Controllers pada Output 2 — Struktur & Fitur Utama

**Peran:** Orang 3 (Controller Engineer)  
**Komponen:** `app/Controllers/MahasiswaController.php`  
**Materi:** Pertemuan 11 — Kasus 1: Pengelolaan Data Mahasiswa (Arsitektur MVC)

---

## 1. Deskripsi Peran & Tanggung Jawab Controller Engineer

Sesuai dengan prinsip **Separation of Concerns (SoC)** pada Slide 11, 12, dan 32:
- **Menerima Request:** Controller menjadi titik temu pertama setelah dipanggil oleh `Router.php` (Front Controller).
- **Validasi di Batas Request:** Membaca, membersihkan (sanitasi), dan memvalidasi seluruh input pengguna dari `$_POST` atau parameter route sebelum menyentuh lapisan Model.
- **Pemeriksaan Otorisasi:** Memastikan operasi berbahaya/sensitif (khususnya aksi **Hapus/Delete**) hanya dapat dieksekusi oleh pengguna dengan hak akses yang berwenang (Role: Admin).
- **Delegasi ke Model (Nol Query SQL):** Controller tidak menulis string query SQL satu pun (`SELECT`, `INSERT`, `UPDATE`, `DELETE`). Akses basis data sepenuhnya didelegasikan ke `MahasiswaModel` via method bermakna (`all`, `find`, `existsByNim`, `create`, `update`, `delete`).
- **Penerapan Pola Post/Redirect/Get (PRG):** Mengarahkan kembali pengguna ke URL `GET` setelah operasi `POST` berhasil menggunakan respons HTTP `302 Redirect` dan *session flash message* untuk mencegah pengiriman ulang form ganda saat *refresh*.
- **Penyedia Data ke View:** Menyiapkan variabel yang telah disepakati dalam kontrak (`$rows`, `$mahasiswa`, `$errors`, `$old`, `$flash`) sebelum memanggil template View.

---

## 2. Struktur Class & Dependency Injection

File: [`app/Controllers/MahasiswaController.php`](file:///c:/Users/ASUS/Documents/GitHub/crud-mahasiswa/pengelolaanDataMahasiswa/app/Controllers/MahasiswaController.php)

```php
final class MahasiswaController
{
    /**
     * Dependency Injection Model melalui constructor (Slide 28).
     * Memudahkan pengujian unit dan menjaga loose coupling antar komponen.
     */
    public function __construct(
        private MahasiswaModel $model
    ) {
    }
    
    // Actions: index(), create(), store(), edit(int $id), update(int $id), delete(int $id)
}
```

Instance controller dibangun oleh fungsi pabrik `instantiateController()` di `public/index.php`, di mana koneksi PDO terpusat diinjeksi ke `MahasiswaModel`, lalu model tersebut diinjeksi ke constructor `MahasiswaController`.

---

## 3. Rincian Action Controller & Alur Kerja

### 3.1 `index(): void` (Rute: `GET /mahasiswa`)
- **Tujuan:** Menampilkan seluruh data mahasiswa.
- **Alur Kerja:**
  1. Memanggil `$this->model->all()` untuk mengambil daftar record mahasiswa aktif (`deleted_at IS NULL`).
  2. Mengambil pesan notifikasi *flash session* (`$_SESSION['flash']`) bila ada, lalu menghapusnya agar pesan hanya muncul sekali.
  3. Mengambil data `$errors` dan `$old` jika ada pengalihan dari form yang gagal.
  4. Menyiapkan token CSRF (`$_SESSION['csrf_token']`).
  5. Mengirimkan variabel `$rows`, `$flash`, `$errors`, dan `$old` ke view `app/Views/mahasiswa/index.php`.

### 3.2 `create(): void` (Rute: `GET /mahasiswa/create`)
- **Tujuan:** Menampilkan halaman formulir tambah data mahasiswa baru.
- **Alur Kerja:**
  1. Menyiapkan variabel `$errors = []` dan `$old = []`.
  2. Menyiapkan token CSRF dalam sesi aktif.
  3. Merender view `app/Views/mahasiswa/create.php` (atau mengarahkan ke form index jika tim menggabungkan form create pada tabel index sesuai Slide 44).

### 3.3 `store(): void` (Rute: `POST /mahasiswa`)
- **Tujuan:** Memvalidasi dan menyimpan data mahasiswa baru.
- **Alur Kerja & Validasi:**
  1. **Validasi CSRF Token:** Memeriksa kesesuaian token form dengan sesi.
  2. **Validasi Batas Request:**
     - `nim`: Wajib diisi (tidak boleh string kosong atau hanya spasi) dan panjang maksimal 15 karakter.
     - `nama`: Wajib diisi dan panjang maksimal 100 karakter.
     - `email`: Validasi sintaks email jika diisi (`FILTER_VALIDATE_EMAIL`).
     - `ipk`: Rentang valid antara `0.00` sampai `4.00`.
  3. **Pengecekan Keunikan NIM:** Memanggil `$this->model->existsByNim($nim)`. Jika sudah ada, tambahkan pesan error: *"NIM sudah terdaftar dalam sistem."*
  4. **Penanganan Validasi Gagal:**
     - Jika terdapat error pada `$errors`, Controller tidak memanggil Model.
     - Menyimpan nilai input ke `$old` dan pesan ke `$errors`, lalu merender ulang view form agar input pengguna tidak hilang (Slide 27).
  5. **Penanganan Validasi Sukses:**
     - Memanggil `$this->model->create(...)`.
     - Menyimpan flash message sukses ke `$_SESSION['flash']`.
     - Melakukan HTTP `302 Redirect` ke `appUrl('/mahasiswa')` (Pola PRG).

### 3.4 `edit(int $id): void` (Rute: `GET /mahasiswa/{id}/edit`)
- **Tujuan:** Memuat record mahasiswa tertentu dan menampilkan form pengubahan data.
- **Alur Kerja:**
  1. Memvalidasi parameter integer `$id > 0`.
  2. Memanggil `$this->model->find($id)`.
  3. Jika data tidak ditemukan (`null`), memicu respon HTTP `404 Not Found` via `renderHttpError(404, ...)`.
  4. Jika data ditemukan, menyiapkan data `$mahasiswa`, `$old = $mahasiswa`, serta `$errors = []`.
  5. Merender template `app/Views/mahasiswa/edit.php`.

### 3.5 `update(int $id): void` (Rute: `POST /mahasiswa/{id}/update`)
- **Tujuan:** Memvalidasi perubahan dan memperbarui data record mahasiswa.
- **Alur Kerja & Validasi:**
  1. Memvalidasi token CSRF dan memastikan record dengan `$id` tersebut ada di database.
  2. Memvalidasi input wajib: `nim` dan `nama`.
  3. **Pengecekan Keunikan NIM (Kecuali ID Sendiri):** Memanggil `$this->model->existsByNim($nim, $id)`. Hal ini memastikan mahasiswa dapat menyimpan form tanpa bentrok dengan dirinya sendiri, namun mencegah penggunaan NIM yang sudah dipakai mahasiswa lain.
  4. **Jika Validasi Gagal:** Mengembalikan tampilan form edit dengan `$errors`, `$old`, dan data `$mahasiswa`.
  5. **Jika Validasi Lolos:**
     - Memanggil `$this->model->update($id, ...)`.
     - Mengisi notifikasi: `$_SESSION['flash'] = ['tipe' => 'ok', 'pesan' => 'Data mahasiswa berhasil diperbarui.']`.
     - Menjalankan Pola PRG (`Location: /mahasiswa`).

### 3.6 `delete(int $id): void` (Rute: `POST /mahasiswa/{id}/delete`)
- **Tujuan:** Menghapus data mahasiswa secara aman.
- **Alur Kerja & Keamanan:**
  1. **Pemeriksaan Otorisasi (Slide 42 & 49):**
     - Memeriksa hak akses: `$_SESSION['role'] === 'admin'`.
     - Jika peran pengguna bukan Admin, operasi langsung dibatalkan dengan respon **HTTP 403 Forbidden** (*Akses Ditolak*).
  2. **Validasi Token CSRF:** Menolak request jika token tidak valid untuk mencegah serangan CSRF dari web pihak ketiga.
  3. **Pemeriksaan Keberadaan Data:** Memeriksa apakah mahasiswa ada via `$this->model->find($id)`.
  4. **Eksekusi Model:** Memanggil `$this->model->delete($id)`.
  5. **Pola PRG:** Menyimpan flash message sukses dan me-redirect ke `/mahasiswa`.

---

## 4. Mekanisme Keamanan yang Diterapkan di Controller (Slide 34)

| Fitur Keamanan | Implementasi di MahasiswaController | Manfaat & Tujuan |
| :--- | :--- | :--- |
| **Request Validation** | `validateMahasiswaInput()` memeriksa field wajib, panjang batas karakter, dan tipe data. | Mencegah data kotor (*garbage data*) dan buffer overflow logika sebelum ke database. |
| **Business Rule Enforcement** | Memanggil `$this->model->existsByNim()` sebelum `create` dan `update`. | Mencegah duplikasi data NIM dan kegagalan SQL constraint error (PDO Exception) di level pengguna. |
| **Role-Based Authorization** | `authorizeAdminAction()` memverifikasi `$_SESSION['role'] === 'admin'`. | Menjamin aturan simulasi Kasus 1: *Aksi hapus hanya untuk pengguna berwenang*. |
| **CSRF Protection** | `validateCsrf()` memverifikasi `hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])`. | Melindungi endpoint `POST` dari eksekusi tanpa izin oleh script pihak ketiga. |
| **Post/Redirect/Get (PRG)** | Header `Location: /mahasiswa` setelah setiap aksi mutasi data (`POST`). | Mencegah pengiriman berulang data formulir (*duplicate submit*) ketika pengguna menekan tombol F5/Refresh. |
| **Zero Direct SQL** | Tidak ada string query SQL di dalam Controller. | Menghindari celah SQL Injection di controller dan menjaga kepatuhan arsitektur MVC. |

---

## 5. Kontrak Data untuk Lapisan View (Orang 4)

Variabel-variabel berikut dijamin selalu dikirim oleh `MahasiswaController` ke View:

1. **`$rows`** *(array of associative array)*:
   Berisi daftar record mahasiswa dari tabel (misal: `[['id' => 1, 'nim' => '231001', 'nama' => 'Alya', ...], ...]`). Digunakan pada file `app/Views/mahasiswa/index.php`.
2. **`$mahasiswa`** *(associative array)*:
   Berisi data record mahasiswa tunggal yang sedang diedit. Digunakan pada file `app/Views/mahasiswa/edit.php`.
3. **`$errors`** *(array of string, key => message)*:
   Daftar pesan kesalahan per field, contoh: `['nim' => 'NIM wajib diisi.', 'nama' => 'Nama lengkap wajib diisi.']`. View dapat langsung menampilkan `$errors['nim']` di bawah kolom input bersangkutan.
4. **`$old`** *(array of string, key => value)*:
   Nilai input pengguna sebelumnya. View menggunakannya pada atribut `value="<?= htmlspecialchars($old['nim'] ?? '') ?>"` agar pengguna tidak perlu mengetik ulang saat form gagal validasi.
5. **`$flash`** *(array{tipe: string, pesan: string}|null)*:
   Pesan notifikasi satu kali tayang setelah redirect PRG (`tipe`: `'ok'` atau `'error'`).
