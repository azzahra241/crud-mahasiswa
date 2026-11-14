<?php

session_start();
require __DIR__ . '/config/database.php';

$mhs = $pdo->query(
    'SELECT nim, nama FROM mahasiswa WHERE deleted_at IS NULL ORDER BY nama'
)->fetchAll();
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>Isi KRS</title></head>
<body>
<h2>Isi KRS</h2>

<?php if (!empty($_SESSION['flash'])): ?>
    <p style="color:<?= $_SESSION['flash']['tipe'] === 'ok' ? 'green' : 'red' ?>">
        <?= htmlspecialchars($_SESSION['flash']['pesan'], ENT_QUOTES, 'UTF-8') ?>
    </p>
    <?php unset($_SESSION['flash']); ?>
<?php endif ?>

<form method="post" action="krs_store.php">
    <p><label>Mahasiswa<br>
        <select name="nim">
            <?php foreach ($mhs as $m): ?>
                <option value="<?= htmlspecialchars($m['nim'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($m['nim'] . ' - ' . $m['nama'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach ?>
        </select></label></p>

    <p><label>Semester<br>
        <input name="semester" type="number" min="1" max="14" value="4"></label></p>

    <p><label>Tahun ajaran<br>
        <input name="tahun_ajaran" value="2026/2027" placeholder="2026/2027"></label></p>

    <p>Kode mata kuliah (valid: IF201, IF202, IF203. Coba kode salah: IF999)<br>
        <input name="kode_mk[]" value="IF201"><br>
        <input name="kode_mk[]" value="IF202"><br>
        <input name="kode_mk[]" value=""></p>

    <button type="submit">Simpan KRS</button>
    <a href="index.php">Kembali</a>
</form>
</body>
</html>