<?php
session_start();

require __DIR__ . '/config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('ID mahasiswa tidak valid.');
}

$stmt = $pdo->prepare(
    "SELECT id, nim, nama, email, prodi, angkatan, ipk
     FROM mahasiswa
     WHERE id = ? AND deleted_at IS NULL"
);

$stmt->execute([$id]);
$mahasiswa = $stmt->fetch();

if (!$mahasiswa) {
    exit('Data mahasiswa tidak ditemukan.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Mahasiswa</title>
</head>
<body>

<h2>Edit Data Mahasiswa</h2>

<form action="update.php" method="POST">

    <input type="hidden" name="id"
           value="<?= (int) $mahasiswa['id'] ?>">

    <input type="hidden" name="csrf_token"
           value="<?= e($_SESSION['csrf_token']) ?>">

    <label>NIM</label><br>
    <input type="text" name="nim"
           value="<?= e($mahasiswa['nim']) ?>" required>
    <br><br>

    <label>Nama</label><br>
    <input type="text" name="nama"
           value="<?= e($mahasiswa['nama']) ?>" required>
    <br><br>

    <label>Email</label><br>
    <input type="email" name="email"
           value="<?= e($mahasiswa['email']) ?>" required>
    <br><br>

    <label>Program Studi</label><br>
    <input type="text" name="prodi"
           value="<?= e($mahasiswa['prodi']) ?>" required>
    <br><br>

    <label>Angkatan</label><br>
    <input type="number" name="angkatan"
           value="<?= e((string) $mahasiswa['angkatan']) ?>"
           min="1901" max="2155" required>
    <br><br>

    <label>IPK</label><br>
    <input type="number" name="ipk"
           value="<?= e((string) $mahasiswa['ipk']) ?>"
           min="0" max="4" step="0.01" required>
    <br><br>

    <button type="submit">Simpan Perubahan</button>
    <a href="index.php">Batal</a>

</form>

</body>
</html>
