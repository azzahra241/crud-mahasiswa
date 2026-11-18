<?php

declare(strict_types=1);

$e = static fn (mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$url = static fn (string $path): string => function_exists('appUrl') ? appUrl($path) : $path;

$mahasiswa = isset($mahasiswa) && is_array($mahasiswa) ? $mahasiswa : [];
$errors = isset($errors) && is_array($errors) ? $errors : [];
$old = isset($old) && is_array($old) ? $old : [];

$id = (int) ($mahasiswa['id'] ?? 0);
$nimValue = $old['nim'] ?? ($mahasiswa['nim'] ?? '');
$namaValue = $old['nama'] ?? ($mahasiswa['nama'] ?? '');
$emailValue = $old['email'] ?? ($mahasiswa['email'] ?? '');
$prodiValue = $old['prodi'] ?? ($mahasiswa['prodi'] ?? '');
$angkatanValue = $old['angkatan'] ?? ($mahasiswa['angkatan'] ?? '2026');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Mahasiswa — Sistem Akademik</title>
    <link rel="stylesheet" href="<?= $e($url('/css/style.css')) ?>">
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="<?= $e($url('/mahasiswa')) ?>">Sistem Akademik</a>
            <span class="brand-sub">Pengelolaan Data Mahasiswa (MVC)</span>
        </div>
    </header>

    <main class="container container-narrow">
        <section class="card">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Edit Mahasiswa</h1>
                    <p class="page-subtitle">Perbarui informasi mahasiswa yang dipilih.</p>
                </div>
                <a class="btn btn-secondary" href="<?= $e($url('/mahasiswa')) ?>">&larr; Kembali</a>
            </div>

            <?php if ($errors !== []): ?>
                <div class="alert alert-error" role="alert">
                    <p class="alert-title">Periksa kembali input Anda:</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= $e($url('/mahasiswa/' . $id . '/update')) ?>" novalidate>
                <div class="form-group">
                    <label class="form-label" for="nim">NIM <span class="required-mark">*</span></label>
                    <input
                        class="form-control"
                        id="nim"
                        name="nim"
                        type="text"
                        maxlength="15"
                        required
                        value="<?= $e($nimValue) ?>"
                    >
                    <p class="form-hint">Maksimal 15 karakter dan harus unik.</p>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nama">Nama Lengkap <span class="required-mark">*</span></label>
                    <input
                        class="form-control"
                        id="nama"
                        name="nama"
                        type="text"
                        maxlength="100"
                        required
                        value="<?= $e($namaValue) ?>"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input
                        class="form-control"
                        id="email"
                        name="email"
                        type="email"
                        maxlength="120"
                        value="<?= $e($emailValue) ?>"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="prodi">Program Studi</label>
                    <input
                        class="form-control"
                        id="prodi"
                        name="prodi"
                        type="text"
                        maxlength="80"
                        value="<?= $e($prodiValue) ?>"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="angkatan">Angkatan</label>
                    <input
                        class="form-control"
                        id="angkatan"
                        name="angkatan"
                        type="number"
                        min="2000"
                        max="2099"
                        value="<?= $e($angkatanValue) ?>"
                    >
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                    <a class="btn btn-secondary" href="<?= $e($url('/mahasiswa')) ?>">Batal</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
