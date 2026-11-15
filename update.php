
<?php
session_start();

require __DIR__ . '/config/database.php';

// Memastikan request menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Memeriksa CSRF Token
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    exit('Token CSRF tidak valid.');
}

// Mengambil dan memvalidasi ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('ID mahasiswa tidak valid.');
}

// Mengambil data dari form
$nim = trim($_POST['nim'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$prodi = trim($_POST['prodi'] ?? '');
$angkatan = filter_var(
    $_POST['angkatan'] ?? '',
    FILTER_VALIDATE_INT
);
$ipk = filter_var(
    $_POST['ipk'] ?? '',
    FILTER_VALIDATE_FLOAT
);

// Validasi data
if (
    $nim === '' ||
    $nama === '' ||
    $email === '' ||
    $prodi === '' ||
    strlen($nim) > 15 ||
    strlen($nama) > 100 ||
    strlen($email) > 120 ||
    strlen($prodi) > 80 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $angkatan === false ||
    $angkatan < 1901 ||
    $angkatan > 2155 ||
    $ipk === false ||
    $ipk < 0 ||
    $ipk > 4
) {
    exit('Data yang dimasukkan tidak valid.');
}

try {

    // Memperbarui data berdasarkan ID
    $sql = "UPDATE mahasiswa
            SET nim = ?,
                nama = ?,
                email = ?,
                prodi = ?,
                angkatan = ?,
                ipk = ?
            WHERE id = ?
            AND deleted_at IS NULL";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $nim,
        $nama,
        $email,
        $prodi,
        $angkatan,
        $ipk,
        $id
    ]);

    // Kembali ke halaman utama
    $_SESSION['flash'] = [
        'tipe' => 'ok',
        'pesan' => 'Data mahasiswa berhasil diperbarui!'
    ];

    header('Location: index.php');
    exit;

} catch (PDOException $e) {

    error_log($e->getMessage());

    $_SESSION['flash'] = [
        'tipe' => 'error',
        'pesan' => 'Gagal memperbarui data mahasiswa.'
    ];

    header('Location: index.php');
    exit;
}
