<?php

declare(strict_types=1);

/**
 * Controller Mahasiswa (Peran: Orang 3 — Controller Engineer)
 * Pertemuan 11 — Kasus 1: Pengelolaan Data Mahasiswa (Arsitektur MVC)
 *
 * Tanggung Jawab (Slide 11, 17, 21, 22, 24, 25, 26, 27, 32, 34):
 * 1. Menerima request dari Router (public/index.php).
 * 2. Membaca dan memvalidasi input di batas request (NIM & Nama wajib, cek duplikat NIM).
 * 3. Memeriksa otorisasi (khusus aksi delete: hanya pengguna berwenang).
 * 4. Memanggil Model (MahasiswaModel) untuk akses data (TIDAK ADA QUERY SQL DI CONTROLLER).
 * 5. Menerapkan pola PRG (Post/Redirect/Get) setelah aksi POST berhasil.
 * 6. Mengirim variabel yang disepakati ke View ($rows, $mahasiswa, $errors, $old, $flash).
 */
final class MahasiswaController
{
    /**
     * Dependency Injection Model melalui constructor (Slide 28).
     * Controller tidak menginstansiasi koneksi database secara langsung.
     */
    public function __construct(
        private MahasiswaModel $model
    ) {
    }

    /**
     * Menampilkan daftar data mahasiswa.
     * Rute HTTP: GET /mahasiswa
     */
    public function index(): void
    {
        // 1. Ambil data mahasiswa dari Model (Slide 17)
        $rows = $this->model->all();

        // 2. Ambil flash message dari session jika ada (pola PRG)
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        // 3. Ambil data form lama / error jika redirect dari validasi gagal
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        // 4. Siapkan token CSRF dan role simulasi untuk form di halaman index
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $currentRole = $_SESSION['role'] ?? 'admin';

        // 5. Muat View (Slide 17, 19)
        $viewFile = APP_ROOT . '/app/Views/mahasiswa/index.php';
        if (is_file($viewFile)) {
            require $viewFile;
            return;
        }

        // Fallback informatif jika Orang 4 (View) belum selesai membuat file view
        $this->renderViewFallback('Daftar Mahasiswa (Fallback View)', [
            'rows' => $rows,
            'flash' => $flash,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    /**
     * Menampilkan form untuk menambah mahasiswa baru.
     * Rute HTTP: GET /mahasiswa/create
     */
    public function create(): void
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $viewFile = APP_ROOT . '/app/Views/mahasiswa/create.php';
        if (is_file($viewFile)) {
            require $viewFile;
            return;
        }

        // Jika Orang 4 menggabungkan form create di dalam index.php (Slide 44),
        // redirect pengguna ke /mahasiswa
        header('Location: ' . appUrl('/mahasiswa'));
        exit;
    }

    /**
     * Memproses data form penambahan mahasiswa (POST).
     * Rute HTTP: POST /mahasiswa
     */
    public function store(): void
    {
        // 1. Validasi CSRF Token bila tersedia
        $this->validateCsrf();

        // 2. Ambil dan bersihkan data request input
        $payload = $this->extractFormData();
        $errors = $this->validateMahasiswaInput($payload);

        // 3. Validasi aturan data: Cek keunikan NIM pada Model (Slide 42 & Kontrak)
        if (empty($errors['nim']) && $this->model->existsByNim($payload['nim'])) {
            $errors['nim'] = 'NIM sudah terdaftar dalam sistem. Silakan gunakan NIM lain.';
        }

        // 4. Jika validasi gagal: kembalikan ke form beserta pesan error dan nilai lama
        if (!empty($errors)) {
            $this->handleValidationError($errors, $payload, 'create');
            return;
        }

        // 5. Input valid -> Panggil Model untuk menyimpan data (Prepared Statement di Model)
        $this->invokeModelMethod('create', $payload);

        // 6. Pola Post/Redirect/Get (PRG) (Slide 22, 27):
        // Simpan feedback ke flash session dan redirect ke URL GET /mahasiswa
        $_SESSION['flash'] = [
            'tipe' => 'ok',
            'pesan' => 'Data mahasiswa (' . $payload['nama'] . ') berhasil ditambahkan.',
        ];

        header('Location: ' . appUrl('/mahasiswa'));
        exit;
    }

    /**
     * Menampilkan form edit data mahasiswa.
     * Rute HTTP: GET /mahasiswa/{id}/edit
     */
    public function edit(int $id): void
    {
        if ($id <= 0) {
            renderHttpError(404, 'Data Tidak Ditemukan', 'ID mahasiswa tidak valid.');
            exit;
        }

        // 1. Ambil data record mahasiswa dari Model (Slide 24)
        $mahasiswa = $this->model->find($id);
        if ($mahasiswa === null) {
            renderHttpError(404, 'Mahasiswa Tidak Ditemukan', 'Data mahasiswa dengan ID tersebut tidak ditemukan.');
            exit;
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? $mahasiswa;
        unset($_SESSION['errors'], $_SESSION['old']);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // 2. Render view edit (Slide 24)
        $viewFile = APP_ROOT . '/app/Views/mahasiswa/edit.php';
        if (is_file($viewFile)) {
            require $viewFile;
            return;
        }

        $this->renderViewFallback('Edit Mahasiswa (Fallback View)', [
            'mahasiswa' => $mahasiswa,
            'old' => $old,
            'errors' => $errors,
        ]);
    }

    /**
     * Memproses pembaharuan data mahasiswa (POST).
     * Rute HTTP: POST /mahasiswa/{id}/update
     */
    public function update(int $id): void
    {
        if ($id <= 0) {
            renderHttpError(404, 'Data Tidak Ditemukan', 'ID mahasiswa tidak valid.');
            exit;
        }

        // 1. Validasi CSRF Token
        $this->validateCsrf();

        // 2. Pastikan record yang ingin diupdate ada di database (Slide 24)
        $mahasiswa = $this->model->find($id);
        if ($mahasiswa === null) {
            renderHttpError(404, 'Mahasiswa Tidak Ditemukan', 'Data mahasiswa yang ingin diubah tidak ditemukan.');
            exit;
        }

        // 3. Ambil dan validasi input request
        $payload = $this->extractFormData();
        $payload['id'] = $id;

        $errors = $this->validateMahasiswaInput($payload);

        // 4. Validasi keunikan NIM: pastikan NIM tidak dipakai oleh mahasiswa lain ($excludeId = $id)
        if (empty($errors['nim']) && $this->model->existsByNim($payload['nim'], $id)) {
            $errors['nim'] = 'NIM sudah digunakan oleh mahasiswa lain.';
        }

        // 5. Jika validasi gagal: render kembali form edit dengan pesan error (Slide 27)
        if (!empty($errors)) {
            $this->handleValidationError($errors, $payload, 'edit', $mahasiswa);
            return;
        }

        // 6. Input valid -> Delegasikan update ke Model
        $this->invokeModelMethod('update', $payload);

        // 7. Pola PRG: Redirect GET /mahasiswa dengan pesan feedback sukses
        $_SESSION['flash'] = [
            'tipe' => 'ok',
            'pesan' => 'Data mahasiswa (' . $payload['nama'] . ') berhasil diperbarui.',
        ];

        header('Location: ' . appUrl('/mahasiswa'));
        exit;
    }

    /**
     * Menghapus data mahasiswa (POST).
     * Rute HTTP: POST /mahasiswa/{id}/delete
     */
    public function delete(int $id): void
    {
        if ($id <= 0) {
            renderHttpError(404, 'Data Tidak Ditemukan', 'ID mahasiswa tidak valid.');
            exit;
        }

        // 1. Otorisasi Akses (Slide 25, 34, 42 — "Aksi hapus hanya untuk pengguna berwenang")
        $this->authorizeAdminAction();

        // 2. Validasi CSRF Token
        $this->validateCsrf();

        // 3. Pastikan record mahasiswa ada dan tidak terikat data KRS sebelum dihapus
        $mahasiswa = $this->model->find($id);
        if ($mahasiswa === null) {
            $_SESSION['flash'] = [
                'tipe' => 'error',
                'pesan' => 'Data mahasiswa tidak ditemukan atau sudah dihapus.',
            ];
            header('Location: ' . appUrl('/mahasiswa'));
            exit;
        }

        $namaMhs = (string) ($mahasiswa['nama'] ?? 'NIM ' . $mahasiswa['nim']);
        if ($this->model->hasKrs($id)) {
            $_SESSION['flash'] = [
                'tipe' => 'error',
                'pesan' => 'Data ' . $namaMhs . ' tidak dapat dihapus karena masih memiliki data KRS.',
            ];
            header('Location: ' . appUrl('/mahasiswa'));
            exit;
        }

        // 4. Panggil Model untuk menjalankan penghapusan data (soft/hard delete di Model)
        $this->model->delete($id);

        // 5. Pola PRG: Beri feedback dan redirect ke daftar mahasiswa (Slide 25)
        $_SESSION['flash'] = [
            'tipe' => 'ok',
            'pesan' => 'Data mahasiswa (' . $namaMhs . ') berhasil dihapus.',
        ];

        header('Location: ' . appUrl('/mahasiswa'));
        exit;
    }

    /**
     * Mengganti mode peran sesi (Admin vs Tamu) untuk simulasi otorisasi aksi hapus.
     * Rute HTTP: POST /mahasiswa/role
     */
    public function switchRole(): void
    {
        $this->validateCsrf();

        $role = trim((string) ($_POST['role'] ?? ''));
        if (!in_array($role, ['admin', 'guest'], true)) {
            $_SESSION['flash'] = [
                'tipe' => 'error',
                'pesan' => 'Pilihan peran tidak valid.',
            ];
            header('Location: ' . appUrl('/mahasiswa'));
            exit;
        }

        $_SESSION['role'] = $role;
        $_SESSION['flash'] = [
            'tipe' => 'ok',
            'pesan' => $role === 'admin'
                ? 'Mode akses diubah ke Admin (berwenang menghapus data).'
                : 'Mode akses diubah ke Tamu (tidak berwenang menghapus data).',
        ];

        header('Location: ' . appUrl('/mahasiswa'));
        exit;
    }

    // =========================================================================
    // Helper Methods & Aturan Bisnis di Batas Request (Slide 26 & 34)
    // =========================================================================

    /**
     * Memeriksa otorisasi role pengguna.
     * Sesuai aturan simulasi Kasus 1: "Aksi hapus hanya untuk pengguna berwenang".
     */
    private function authorizeAdminAction(): void
    {
        // Default role disimulasikan sebagai 'admin' jika belum diset di session,
        // namun jika session role secara eksplisit diset bukan 'admin' (mis. 'user'/'guest' oleh QA),
        // maka aksi tolak dengan HTTP 403 Forbidden.
        $currentRole = $_SESSION['role'] ?? 'admin';

        if ($currentRole !== 'admin') {
            renderHttpError(
                403,
                'Akses Ditolak (403 Forbidden)',
                'Hanya pengguna dengan wewenang Admin yang diizinkan untuk menghapus data mahasiswa.'
            );
            exit;
        }
    }

    /**
     * Memvalidasi token CSRF pada request POST.
     * Jika session memiliki csrf_token (akses via form browser), token wajib dikirim dan cocok.
     * Tetap menolak apabila csrf_token dikirim dengan nilai tidak valid.
     */
    private function validateCsrf(): void
    {
        $hasSessionToken = !empty($_SESSION['csrf_token']);
        $hasPostToken = isset($_POST['csrf_token']);

        if (!$hasSessionToken && !$hasPostToken) {
            return;
        }

        if (
            !$hasSessionToken
            || !$hasPostToken
            || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])
        ) {
            renderHttpError(
                403,
                'Token Keamanan Tidak Valid',
                'Permintaan ditolak demi keamanan (Token CSRF tidak valid atau sudah kadaluarsa).'
            );
            exit;
        }
    }

    /**
     * Mengambil dan membersihkan input form dari POST data.
     * Field opsional yang tidak dikirim pada form bernilai null agar tidak menimpa data lama saat update.
     *
     * @return array{id?: int, nim: string, nama: string, email: ?string, prodi: ?string, angkatan: int|false|null, ipk: float|false|null}
     */
    private function extractFormData(): array
    {
        $nim = trim((string) ($_POST['nim'] ?? ''));
        $nama = trim((string) ($_POST['nama'] ?? ''));
        $email = array_key_exists('email', $_POST) ? trim((string) $_POST['email']) : null;
        $prodi = array_key_exists('prodi', $_POST) ? trim((string) $_POST['prodi']) : null;

        $angkatan = null;
        if (array_key_exists('angkatan', $_POST)) {
            $angkatanRaw = trim((string) $_POST['angkatan']);
            if ($angkatanRaw !== '') {
                $parsed = filter_var($angkatanRaw, FILTER_VALIDATE_INT);
                $angkatan = $parsed === false ? false : $parsed;
            }
        }

        $ipk = null;
        if (array_key_exists('ipk', $_POST)) {
            $ipkRaw = trim((string) $_POST['ipk']);
            if ($ipkRaw !== '') {
                $parsed = filter_var($ipkRaw, FILTER_VALIDATE_FLOAT);
                $ipk = $parsed === false ? false : $parsed;
            }
        }

        return [
            'nim' => $nim,
            'nama' => $nama,
            'email' => $email,
            'prodi' => $prodi,
            'angkatan' => $angkatan,
            'ipk' => $ipk,
        ];
    }

    /**
     * Memvalidasi input mahasiswa di batas request (Slide 26, 34).
     * Aturan: NIM & Nama wajib diisi; validasi panjang string, tahun angkatan & format.
     *
     * @param array<string, mixed> $payload
     * @return array<string, string> Daftar error per field
     */
    private function validateMahasiswaInput(array $payload): array
    {
        $errors = [];

        // 1. Validasi NIM (Wajib diisi & panjang max 15 karakter)
        $nim = (string) ($payload['nim'] ?? '');
        if ($nim === '') {
            $errors['nim'] = 'NIM wajib diisi.';
        } elseif (strlen($nim) > 15) {
            $errors['nim'] = 'NIM maksimal 15 karakter.';
        }

        // 2. Validasi Nama (Wajib diisi & panjang max 100 karakter)
        $nama = (string) ($payload['nama'] ?? '');
        if ($nama === '') {
            $errors['nama'] = 'Nama lengkap wajib diisi.';
        } elseif (strlen($nama) > 100) {
            $errors['nama'] = 'Nama lengkap maksimal 100 karakter.';
        }

        // 3. Validasi opsional email & prodi
        $email = $payload['email'] ?? null;
        if (is_string($email) && $email !== '') {
            if (strlen($email) > 120) {
                $errors['email'] = 'Alamat email maksimal 120 karakter.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Format alamat email tidak valid.';
            }
        }

        $prodi = $payload['prodi'] ?? null;
        if (is_string($prodi) && strlen($prodi) > 80) {
            $errors['prodi'] = 'Program studi maksimal 80 karakter.';
        }

        // 4. Validasi opsional angkatan (rentang tipe YEAR MySQL: 1901 - 2155)
        $angkatan = $payload['angkatan'] ?? null;
        if ($angkatan === false || (is_int($angkatan) && ($angkatan < 1901 || $angkatan > 2155))) {
            $errors['angkatan'] = 'Tahun angkatan harus berupa angka antara 1901 hingga 2155.';
        }

        // 5. Validasi opsional IPK (0.00 - 4.00)
        $ipk = $payload['ipk'] ?? null;
        if ($ipk === false || (is_float($ipk) && ($ipk < 0.0 || $ipk > 4.0))) {
            $errors['ipk'] = 'Nilai IPK harus berada pada rentang 0.00 hingga 4.00.';
        }

        return $errors;
    }

    /**
     * Menangani respon ketika validasi input gagal.
     * Mendukung render view langsung (Slide 27) atau redirect ke form dengan flash session.
     *
     * @param array<string, string> $errors
     * @param array<string, mixed> $payload
     * @param string $formType 'create' atau 'edit'
     * @param array<string, mixed>|null $mahasiswa
     */
    private function handleValidationError(
        array $errors,
        array $payload,
        string $formType,
        ?array $mahasiswa = null
    ): void {
        $old = $payload;
        if (($old['angkatan'] ?? null) === false) {
            $old['angkatan'] = (string) ($_POST['angkatan'] ?? '');
        }
        if ($old['ipk'] === false) {
            $old['ipk'] = (string) ($_POST['ipk'] ?? '');
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($formType === 'create') {
            $createView = APP_ROOT . '/app/Views/mahasiswa/create.php';
            if (is_file($createView)) {
                require $createView;
                return;
            }

            // Jika form create berada di dalam index.php (Slide 44)
            $indexView = APP_ROOT . '/app/Views/mahasiswa/index.php';
            if (is_file($indexView)) {
                $rows = $this->model->all();
                $currentRole = $_SESSION['role'] ?? 'admin';
                require $indexView;
                return;
            }
        }

        if ($formType === 'edit') {
            $editView = APP_ROOT . '/app/Views/mahasiswa/edit.php';
            if (is_file($editView)) {
                require $editView;
                return;
            }
        }

        // Jika view belum dibuat oleh Orang 4, tampilkan fallback UI dengan pesan error
        $this->renderViewFallback("Form Mahasiswa — Validasi Gagal ($formType)", [
            'errors' => $errors,
            'old' => $old,
            'mahasiswa' => $mahasiswa,
            'rows' => $this->model->all(),
        ]);
    }

    /**
     * Memanggil method pada MahasiswaModel menggunakan Reflection
     * agar kompatibel secara dinamis baik jika Orang 2 (Model Engineer) menggunakan
     * signature sederhana ($nim, $nama) maupun signature lengkap dengan kolom tambahan.
     *
     * @param string $method Nama method model ('create' atau 'update')
     * @param array<string, mixed> $payload
     */
    private function invokeModelMethod(string $method, array $payload): void
    {
        $reflection = new ReflectionMethod($this->model, $method);
        $args = [];

        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            if (array_key_exists($paramName, $payload)) {
                $args[] = $payload[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                $args[] = null;
            }
        }

        $this->model->$method(...$args);
    }

    /**
     * Fallback UI sederhana jika file view dari Orang 4 belum tersedia.
     * Memastikan sistem tidak fatal error selama proses integrasi modular.
     *
     * @param string $title
     * @param array<string, mixed> $vars
     */
    private function renderViewFallback(string $title, array $vars): void
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $homeUrl = htmlspecialchars(appUrl('/mahasiswa'), ENT_QUOTES, 'UTF-8');

        echo "<!doctype html>\n<html lang=\"id\">\n<head>\n"
            . "    <meta charset=\"utf-8\">\n"
            . "    <title>{$safeTitle}</title>\n"
            . "    <style>body{font-family:sans-serif;max-width:800px;margin:32px auto;padding:0 16px;line-height:1.6;}table{width:100%;border-collapse:collapse;margin-top:16px;}th,td{border:1px solid #ccc;padding:8px;text-align:left;}th{background:#f1f5f9;}.flash{padding:10px;background:#e0f2fe;border:1px solid #bae6fd;margin-bottom:12px;}</style>\n"
            . "</head>\n<body>\n"
            . "    <h2>{$safeTitle}</h2>\n";

        if (!empty($vars['flash']['pesan'])) {
            $msg = htmlspecialchars((string) $vars['flash']['pesan'], ENT_QUOTES, 'UTF-8');
            echo "    <div class=\"flash\">{$msg}</div>\n";
        }

        if (!empty($vars['errors'])) {
            echo "    <div style=\"padding:12px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;margin-bottom:12px;border-radius:4px;\">\n"
                . "        <strong>Pemberitahuan Validasi:</strong><ul style=\"margin:8px 0 0 20px;padding:0;\">\n";
            foreach ($vars['errors'] as $fld => $err) {
                $safeFld = htmlspecialchars((string) $fld, ENT_QUOTES, 'UTF-8');
                $safeErr = htmlspecialchars((string) $err, ENT_QUOTES, 'UTF-8');
                echo "            <li><strong>{$safeFld}:</strong> {$safeErr}</li>\n";
            }
            echo "        </ul>\n    </div>\n";
        }

        if (isset($vars['rows']) && is_array($vars['rows'])) {
            echo "    <p><a href=\"{$homeUrl}\">Refresh Data</a></p>\n"
                . "    <table><thead><tr><th>NIM</th><th>Nama</th><th>Prodi</th></tr></thead><tbody>\n";
            foreach ($vars['rows'] as $r) {
                $nim = htmlspecialchars((string) ($r['nim'] ?? ''), ENT_QUOTES, 'UTF-8');
                $nama = htmlspecialchars((string) ($r['nama'] ?? ''), ENT_QUOTES, 'UTF-8');
                $prodi = htmlspecialchars((string) ($r['prodi'] ?? '-'), ENT_QUOTES, 'UTF-8');
                echo "        <tr><td>{$nim}</td><td>{$nama}</td><td>{$prodi}</td></tr>\n";
            }
            echo "    </tbody></table>\n";
        }

        echo "    <p style=\"color:#64748b;margin-top:24px;\"><small><em>Catatan: Menampilkan fallback view karena file view di app/Views/mahasiswa/ belum dibuat oleh Orang 4.</em></small></p>\n"
            . "</body>\n</html>";
    }
}
