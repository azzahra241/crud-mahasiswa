<?php

declare(strict_types=1);

$e = static fn (mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$url = static fn (string $path): string => function_exists('appUrl') ? appUrl($path) : $path;

$errors = isset($errors) && is_array($errors) ? $errors : [];
$old = isset($old) && is_array($old) ? $old : [];
$csrfToken = (string) ($_SESSION['csrf_token'] ?? '');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Mahasiswa — Sistem Akademik</title>
    <link rel="stylesheet" href="<?= $e($url('/css/style.css')) ?>">
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="<?= $e($url('/mahasiswa')) ?>">Sistem Akademik</a>
        </div>
    </header>

    <main class="container container-narrow">
        <section class="card">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Tambah Mahasiswa</h1>
                    <p class="page-subtitle">Isi data mahasiswa baru dengan lengkap dan benar.</p>
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

            <form method="post" action="<?= $e($url('/mahasiswa')) ?>" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                <div class="form-group">
                    <label class="form-label" for="nim">NIM <span class="required-mark">*</span></label>
                    <input
                        class="form-control"
                        id="nim"
                        name="nim"
                        type="text"
                        maxlength="15"
                        required
                        value="<?= $e($old['nim'] ?? '') ?>"
                        placeholder="Contoh: 231004"
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
                        value="<?= $e($old['nama'] ?? '') ?>"
                        placeholder="Contoh: Dewi Sartika"
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
                        value="<?= $e($old['email'] ?? '') ?>"
                        placeholder="Contoh: dewi@kampus.ac.id"
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
                        value="<?= $e($old['prodi'] ?? '') ?>"
                        placeholder="Contoh: Teknik Informatika"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="angkatan">Angkatan</label>
                    <input
                        class="form-control"
                        id="angkatan"
                        name="angkatan"
                        type="number"
                        min="1901"
                        max="2155"
                        value="<?= $e($old['angkatan'] ?? '2026') ?>"
                        placeholder="2026"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="ipk">IPK</label>
                    <input
                        class="form-control"
                        id="ipk"
                        name="ipk"
                        type="number"
                        min="0"
                        max="4"
                        step="0.01"
                        value="<?= $e($old['ipk'] ?? '0.00') ?>"
                        placeholder="0.00"
                    >
                    <p class="form-hint">Rentang nilai IPK antara 0.00 hingga 4.00.</p>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Simpan Data</button>
                    <a class="btn btn-secondary" href="<?= $e($url('/mahasiswa')) ?>">Batal</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
