<?php
session_start();

require __DIR__ . '/config/database.php';

$_SESSION['role'] ??= 'admin';   
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['flash'] = ['tipe' => 'error', 'pesan' => 'Anda tidak memiliki izin menghapus data.'];
    header('Location: index.php');
    exit;
}

// 1. Validasi Metode Request (Hanya izinkan POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = [
        'tipe' => 'error',
        'pesan' => 'Metode request tidak diizinkan.'
    ];
    header('Location: index.php');
    exit;
}

// 2. Validasi CSRF Token
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])
) {
    $_SESSION['flash'] = [
        'tipe' => 'error',
        'pesan' => 'Token keamanan (CSRF) tidak valid.'
    ];
    header('Location: index.php');
    exit;
}

// 3. Validasi Input ID Mahasiswa
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $_SESSION['flash'] = [
        'tipe' => 'error',
        'pesan' => 'ID mahasiswa tidak valid.'
    ];
    header('Location: index.php');
    exit;
}

try {
    // 4. Cek Keberadaan Data & Relasi KRS (dengan penyamaan collation)
    $cekStmt = $pdo->prepare(
        "SELECT m.id, m.nim, m.nama,
                (SELECT COUNT(*) FROM krs k WHERE k.nim COLLATE utf8mb4_unicode_ci = m.nim COLLATE utf8mb4_unicode_ci) AS jumlah_krs
         FROM mahasiswa m
         WHERE m.id = ? AND m.deleted_at IS NULL"
    );
    $cekStmt->execute([$id]);
    $mahasiswa = $cekStmt->fetch();

    if (!$mahasiswa) {
        $_SESSION['flash'] = [
            'tipe' => 'error',
            'pesan' => 'Data mahasiswa tidak ditemukan atau sudah dihapus.'
        ];
        header('Location: index.php');
        exit;
    }

    // Cek relasi: cegah penghapusan jika mahasiswa masih terdaftar di tabel KRS
    if ((int) $mahasiswa['jumlah_krs'] > 0) {
        $_SESSION['flash'] = [
            'tipe' => 'error',
            'pesan' => 'Data ' . $mahasiswa['nama'] . ' tidak dapat dihapus karena masih memiliki data KRS.'
        ];
        header('Location: index.php');
        exit;
    }

    // 5. Eksekusi Soft Delete
    $deleteStmt = $pdo->prepare(
        "UPDATE mahasiswa
         SET deleted_at = NOW()
         WHERE id = ? AND deleted_at IS NULL"
    );
    $deleteStmt->execute([$id]);

    $_SESSION['flash'] = [
        'tipe' => 'ok',
        'pesan' => 'Data mahasiswa (' . $mahasiswa['nama'] . ') berhasil dihapus.'
    ];

    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    $_SESSION['flash'] = [
        'tipe' => 'error',
        'pesan' => 'Terjadi kesalahan saat menghapus data mahasiswa.'
    ];

    header('Location: index.php');
    exit;
}
