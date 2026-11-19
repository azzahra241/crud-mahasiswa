# Output 1 — Diagram Request–Response MVC dan Rute HTTP

Dokumen ini merupakan bagian dari tugas kelompok **Pertemuan 11 — Kasus 1: Pengelolaan Data Mahasiswa (MVC)** untuk memetakan alur *request–response* dan seluruh rute HTTP aplikasi.

---

## 1. Diagram Alur Request–Response MVC (Umum)

```mermaid
flowchart LR
    B["Browser<br/>(HTTP Request)"] --> FC["Front Controller<br/>(public/index.php)"]
    FC --> R["Router<br/>(app/Router.php)"]
    R -- "Rute tidak cocok" --> E404["HTTP 404 / 405<br/>Error View Aman"]
    R -- "Cocok: Controller@Action" --> C["MahasiswaController<br/>(Validasi & Otorisasi)"]
    C -- "Panggil method data" --> M["MahasiswaModel<br/>(Prepared Statement PDO)"]
    M <--> DB[("MySQL: akademik1<br/>Tabel mahasiswa")]
    M -- "Data / Exception" --> C
    C -- "GET / Validasi Gagal" --> V["View<br/>(app/Views/mahasiswa/*.php<br/>htmlspecialchars)"]
    V -- "HTML Response" --> B
    C -- "POST Sukses (PRG)" --> RD["HTTP 302 Redirect<br/>Location: /mahasiswa"]
    RD --> B
```

---

## 2. Diagram Sequence Per Operasi CRUD & Aturan Simulasi

### 2.1 Menampilkan Daftar Mahasiswa (`GET /mahasiswa`)

```mermaid
sequenceDiagram
    autonumber
    actor User as Browser
    participant FC as public/index.php & Router
    participant Ctrl as MahasiswaController
    participant Mdl as MahasiswaModel
    participant DB as MySQL (mahasiswa)
    participant View as Views/mahasiswa/index.php

    User->>FC: GET /mahasiswa
    FC->>Ctrl: index()
    Ctrl->>Mdl: all()
    Mdl->>DB: SELECT ... FROM mahasiswa WHERE deleted_at IS NULL
    DB-->>Mdl: array $rows
    Mdl-->>Ctrl: $rows
    Ctrl->>View: render dengan data $rows
    View-->>User: 200 OK (HTML tabel + htmlspecialchars)
```

### 2.2 Tambah & Validasi Mahasiswa (`POST /mahasiswa`)

Aturan simulasi yang diuji pada alur ini:
1. **NIM dan nama wajib diisi**.
2. **NIM harus unik pada database**.

```mermaid
sequenceDiagram
    autonumber
    actor User as Browser
    participant FC as public/index.php & Router
    participant Ctrl as MahasiswaController
    participant Mdl as MahasiswaModel
    participant DB as MySQL (mahasiswa)
    participant View as View Form Create

    User->>FC: POST /mahasiswa (nim, nama, ...)
    FC->>Ctrl: store()
    Ctrl->>Ctrl: Validasi input wajib (NIM & nama != '')
    alt Input wajib kosong / format tidak valid
        Ctrl->>View: Render ulang form + pesan error + $old
        View-->>User: HTML Form dengan pesan error
    else Input awal valid
        Ctrl->>Mdl: existsByNim($nim)
        Mdl->>DB: SELECT COUNT(*) ... WHERE nim = :nim
        DB-->>Mdl: jumlah
        alt NIM sudah terdaftar (duplikat)
            Ctrl->>View: Render ulang form + pesan "NIM sudah digunakan"
            View-->>User: HTML Form dengan pesan error
        else NIM unik
            Ctrl->>Mdl: create(...)
            Mdl->>DB: INSERT INTO mahasiswa ... (Prepared Statement)
            DB-->>Mdl: Sukses
            Ctrl-->>User: 302 Redirect GET /mahasiswa (Pola PRG)
        end
    end
```

### 2.3 Ubah Data Mahasiswa (`GET /mahasiswa/{id}/edit` & `POST /mahasiswa/{id}/update`)

```mermaid
sequenceDiagram
    autonumber
    actor User as Browser
    participant FC as public/index.php & Router
    participant Ctrl as MahasiswaController
    participant Mdl as MahasiswaModel
    participant DB as MySQL (mahasiswa)

    User->>FC: GET /mahasiswa/{id}/edit
    FC->>Ctrl: edit($id)
    Ctrl->>Mdl: find($id)
    Mdl->>DB: SELECT ... WHERE id = :id AND deleted_at IS NULL
    alt Data tidak ditemukan
        Ctrl-->>User: 404 Halaman Tidak Ditemukan
    else Data ditemukan
        Ctrl-->>User: Render Views/mahasiswa/edit.php
    end

    User->>FC: POST /mahasiswa/{id}/update
    FC->>Ctrl: update($id)
    Ctrl->>Ctrl: Validasi wajib isi & cek existsByNim($nim, $id)
    alt Validasi gagal / NIM bentrok
        Ctrl-->>User: Render ulang form edit + pesan error
    else Validasi lolos
        Ctrl->>Mdl: update($id, ...)
        Mdl->>DB: UPDATE mahasiswa SET ... WHERE id = :id
        Ctrl-->>User: 302 Redirect GET /mahasiswa (Pola PRG)
    end
```

### 2.4 Hapus Data Mahasiswa (`POST /mahasiswa/{id}/delete`)

Aturan simulasi yang diuji pada alur ini:
- **Aksi hapus hanya untuk pengguna berwenang** (otorisasi dicek di Controller sebelum memanggil Model).

```mermaid
sequenceDiagram
    autonumber
    actor User as Browser
    participant FC as public/index.php & Router
    participant Ctrl as MahasiswaController
    participant Mdl as MahasiswaModel
    participant DB as MySQL (mahasiswa)

    User->>FC: POST /mahasiswa/{id}/delete
    FC->>Ctrl: delete($id)
    Ctrl->>Ctrl: Cek otorisasi role/hak akses
    alt Pengguna tidak berwenang
        Ctrl-->>User: 403 Forbidden / Redirect + pesan ditolak
    else Pengguna berwenang
        Ctrl->>Mdl: find($id) & cek relasi (mis. KRS)
        alt Data tidak ada / masih berelasi
            Ctrl-->>User: Redirect GET /mahasiswa + flash error
        else Aman dihapus
            Ctrl->>Mdl: delete($id)
            Mdl->>DB: UPDATE mahasiswa SET deleted_at = NOW() / DELETE
            Ctrl-->>User: 302 Redirect GET /mahasiswa + flash sukses
        end
    end
```

---

## 3. Tabel Pemetaan Rute HTTP

Seluruh rute masuk melalui *entry point* tunggal `public/index.php` dan dipetakan oleh `app/Router.php`:

| No | HTTP Method | Pola URL | Handler (`Controller@Action`) | Parameter Route | Deskripsi & Respons Sukses | Respons Error / Kasus Kritis |
| :-: | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | `GET` | `/` | Closure Redirect | - | Redirect `302` ke `/mahasiswa` | - |
| 2 | `GET` | `/mahasiswa` | `MahasiswaController::index()` | - | Menampilkan tabel daftar mahasiswa (dan form tambah sesuai UI Kasus 1) | Daftar kosong menampilkan pesan info; tidak ada SQL di View |
| 3 | `GET` | `/mahasiswa/create` | `MahasiswaController::create()` | - | Menampilkan halaman/form tambah mahasiswa | - |
| 4 | `POST` | `/mahasiswa` | `MahasiswaController::store()` | - | Validasi input → simpan data baru → Redirect `302` ke `/mahasiswa` (PRG) | Render ulang form jika NIM/nama kosong atau NIM duplikat |
| 5 | `GET` | `/mahasiswa/{id}/edit` | `MahasiswaController::edit(int $id)` | `{id}` (`\d+`) | Memuat data awal berdasarkan `id` dan menampilkan form edit | `404` jika `{id}` bukan angka atau record tidak ditemukan |
| 6 | `POST` | `/mahasiswa/{id}/update` | `MahasiswaController::update(int $id)` | `{id}` (`\d+`) | Validasi input & keunikan NIM → update data → Redirect `302` ke `/mahasiswa` | Render ulang form edit jika input kosong/duplikat; `404` jika data tidak ada |
| 7 | `POST` | `/mahasiswa/{id}/delete` | `MahasiswaController::delete(int $id)` | `{id}` (`\d+`) | Cek otorisasi → hapus data (soft/hard delete) → Redirect `302` ke `/mahasiswa` | `403`/penolakan jika bukan pengguna berwenang; `405` jika diakses dengan `GET` |
| 8 | `POST` | `/mahasiswa/role` | `MahasiswaController::switchRole()` | - | Mengubah mode peran sesi (`admin` / `guest`) untuk simulasi otorisasi → Redirect `302` ke `/mahasiswa` | Flash error jika nilai role tidak valid; `405` jika diakses dengan `GET` |
| 9 | `*` | Rute lain yang tidak terdaftar | `renderHttpError(404, ...)` | - | - | HTTP `404` halaman error aman tanpa detail teknis |

---

## 4. Alur Diagnosis Bug Berdasarkan Lapisan MVC (Slide 35)

Jika terjadi kesalahan saat integrasi atau pengujian, penelusuran dilakukan berurutan sesuai lapisan:

1. **Route (`public/index.php` & `app/Router.php`)** → Apakah URL, base path, atau HTTP method (`GET`/`POST`) salah?
2. **Controller (`app/Controllers/MahasiswaController.php`)** → Apakah validasi input, pemeriksaan otorisasi, atau tujuan redirect PRG keliru?
3. **Model (`app/Models/MahasiswaModel.php`)** → Apakah query SQL, binding prepared statement, atau pengecekan unik NIM salah?
4. **View (`app/Views/mahasiswa/*.php`)** → Apakah nama variabel dari Controller cocok, apakah `htmlspecialchars()` terpasang, dan apakah ada query SQL bocor ke View?
