# Dokumentasi QA, Keamanan, dan Pengujian Sistem Akademik

## 1. Pendahuluan

Dokumen ini berisi rencana pengujian, dokumentasi hasil pengujian, serta rangkuman struktur MVC dan fitur utama pada Sistem Akademik/Data Mahasiswa.

Pengujian dilakukan untuk memastikan fungsi sistem berjalan sesuai kebutuhan dan menangani kondisi input maupun akses yang tidak sesuai.

## 2. Tujuan Pengujian

- Memastikan validasi input berjalan dengan benar.
- Memastikan NIM tidak dapat didaftarkan lebih dari satu kali.
- Memeriksa pembatasan hak akses pengguna.
- Memeriksa perlindungan dasar terhadap XSS dan SQL Injection.
- Memastikan route yang tidak tersedia menampilkan halaman 404.
- Memastikan sistem menangani kondisi data kosong dan data dalam jumlah banyak.
- Memastikan alur Post-Redirect-Get (PRG) berjalan dengan benar.

## 3. Metode Pengujian

Pengujian direncanakan menggunakan **Black Box Testing**, yaitu menguji fungsi sistem berdasarkan masukan dan keluaran yang terlihat tanpa berfokus pada detail implementasi kode.

> Isi status dan bukti berdasarkan pengujian yang benar-benar dilakukan. Jangan menandai pengujian sebagai Pass sebelum hasilnya diverifikasi.

## 4. Skenario Pengujian

| No. | Skenario | Langkah Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | Input kosong | Kirim form tambah atau edit tanpa mengisi kolom wajib. | Sistem menolak data dan menampilkan pesan validasi. | Belum diuji |
| 2 | NIM duplikat | Masukkan NIM yang sudah terdaftar. | Sistem menolak penyimpanan dan menampilkan pesan bahwa NIM sudah digunakan. | Belum diuji |
| 3 | Hak hapus non-berwenang | Coba akses fungsi hapus menggunakan akun yang tidak memiliki izin. | Sistem menolak akses; halaman atau respons 403 ditampilkan sesuai rancangan. | Belum diuji |
| 4 | XSS | Masukkan teks uji yang berisi markup/script pada kolom input. | Input ditampilkan sebagai teks yang aman dan tidak dieksekusi sebagai script. | Belum diuji |
| 5 | SQL Injection | Lakukan pengujian input tidak valid pada kolom yang diproses oleh aplikasi. | Sistem memproses input secara aman dan tidak menjalankan perintah SQL yang tidak diinginkan. | Belum diuji |
| 6 | Route 404 | Buka alamat route yang tidak tersedia. | Sistem menampilkan halaman 404 Not Found. | Belum diuji |
| 7 | Data kosong | Buka halaman daftar ketika belum ada data mahasiswa. | Sistem menampilkan informasi bahwa data mahasiswa belum tersedia. | Belum diuji |
| 8 | Data banyak | Buka halaman daftar ketika terdapat banyak data mahasiswa. | Daftar tetap ditampilkan dengan baik dan fungsi halaman tetap dapat digunakan. | Belum diuji |
| 9 | Alur PRG | Tambahkan atau ubah data, kemudian refresh halaman hasil proses. | Sistem melakukan redirect setelah proses sehingga pengiriman form tidak terulang hanya karena halaman di-refresh. | Belum diuji |

## 5. Rekapitulasi Hasil Pengujian

Lengkapi tabel berikut setelah setiap skenario dijalankan.

| No. | Skenario | Hasil Aktual | Status (Pass/Fail) | Bukti |
|---|---|---|---|---|
| 1 | Input kosong | Belum diisi | Belum diuji | Screenshot 1 |
| 2 | NIM duplikat | Belum diisi | Belum diuji | Screenshot 2 |
| 3 | Hak hapus non-berwenang | Belum diisi | Belum diuji | Screenshot 3 |
| 4 | XSS | Belum diisi | Belum diuji | Screenshot 4 |
| 5 | SQL Injection | Belum diisi | Belum diuji | Screenshot 5 |
| 6 | Route 404 | Belum diisi | Belum diuji | Screenshot 6 |
| 7 | Data kosong | Belum diisi | Belum diuji | Screenshot 7 |
| 8 | Data banyak | Belum diisi | Belum diuji | Screenshot 8 |
| 9 | Alur PRG | Belum diisi | Belum diuji | Screenshot 9 |

**Keterangan status:**
- **Pass:** Hasil aktual sesuai dengan hasil yang diharapkan.
- **Fail:** Hasil aktual tidak sesuai dengan hasil yang diharapkan.
- **Belum diuji:** Pengujian belum dilakukan atau belum memiliki bukti yang cukup.

## 6. Dokumentasi Bukti Pengujian

Lampirkan screenshot hasil setiap skenario pada bagian ini atau simpan di folder dokumentasi proyek.

Contoh susunan folder:

```text
docs/
└── pengujian/
    ├── 01-input-kosong.png
    ├── 02-nim-duplikat.png
    ├── 03-hak-akses.png
    ├── 04-xss.png
    ├── 05-sql-injection.png
    ├── 06-route-404.png
    ├── 07-data-kosong.png
    ├── 08-data-banyak.png
    └── 09-alur-prg.png
```

## 7. Rangkuman Struktur MVC

Sistem menggunakan pola arsitektur **Model-View-Controller (MVC)** untuk memisahkan pengelolaan data, tampilan, dan alur pemrosesan aplikasi.

### Model

Model bertanggung jawab mengelola data mahasiswa dan berinteraksi dengan database, termasuk proses pengambilan, penambahan, perubahan, dan penghapusan data.

### View

View bertanggung jawab menampilkan antarmuka kepada pengguna, seperti:
- Halaman daftar mahasiswa.
- Form tambah mahasiswa.
- Form edit mahasiswa.
- Pesan validasi dan notifikasi.
- Halaman error 403 dan 404.

### Controller

Controller menerima permintaan pengguna, menjalankan proses aplikasi, berkoordinasi dengan Model, kemudian menentukan View atau respons yang akan ditampilkan.

## 8. Rangkuman Fitur Utama

Fitur utama yang menjadi cakupan pengujian:

1. Menampilkan daftar data mahasiswa.
2. Menambahkan data mahasiswa.
3. Mengedit data mahasiswa.
4. Menghapus data mahasiswa sesuai hak akses.
5. Validasi kolom wajib pada form.
6. Pemeriksaan NIM agar tidak duplikat.
7. Penanganan pesan error dan halaman 403/404.
8. Perlindungan dasar terhadap XSS dan SQL Injection.
9. Penanganan data kosong dan data dalam jumlah banyak.
10. Penggunaan alur Post-Redirect-Get (PRG).

## 9. Kesimpulan

Pengujian QA dan keamanan dilakukan untuk memeriksa kesesuaian fungsi sistem, validasi data, pembatasan hak akses, penanganan error, serta keamanan input. Hasil setiap skenario perlu dicatat dalam tabel Pass/Fail dan dilengkapi screenshot sebagai bukti.

Kesimpulan akhir mengenai kelayakan sistem dibuat setelah seluruh skenario pengujian dijalankan dan hasil aktualnya diverifikasi.

---

**Penanggung jawab:** Orang 5 — QA, Keamanan & Dokumentasi  
**Nama proyek:** Sistem Akademik / Data Mahasiswa  
**Tanggal pengujian:** [18 November 2026]
