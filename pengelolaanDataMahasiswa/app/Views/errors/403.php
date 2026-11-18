<?php

declare(strict_types=1);

$e = static fn (mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$url = static fn (string $path): string => function_exists('appUrl') ? appUrl($path) : $path;

$title = isset($errorTitle) && is_string($errorTitle) && $errorTitle !== ''
    ? $errorTitle
    : 'Akses Ditolak';
$message = isset($errorMessage) && is_string($errorMessage) && $errorMessage !== ''
    ? $errorMessage
    : 'Anda tidak memiliki hak akses untuk melakukan aksi ini.';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — <?= $e($title) ?></title>
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
        <section class="card error-box">
            <p class="error-code error-code-403">403</p>
            <h1 class="error-title"><?= $e($title) ?></h1>
            <p class="error-message"><?= $e($message) ?></p>
            <a class="btn btn-primary" href="<?= $e($url('/mahasiswa')) ?>">&larr; Kembali ke Data Mahasiswa</a>
        </section>
    </main>
</body>
</html>
