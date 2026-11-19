<?php

declare(strict_types=1);

$e = static fn (mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$url = static fn (string $path): string => function_exists('appUrl') ? appUrl($path) : $path;

$rows = isset($rows) && is_array($rows) ? $rows : [];
$errors = isset($errors) && is_array($errors) ? $errors : [];
$flashData = isset($flash) && is_array($flash) ? $flash : [];
$flashMessage = $success ?? ($flashData['pesan'] ?? ($flashData['success'] ?? null));
$flashType = in_array(($flashData['tipe'] ?? 'ok'), ['err', 'error'], true) ? 'error' : 'success';
$currentRole = isset($currentRole) && is_string($currentRole) ? $currentRole : ($_SESSION['role'] ?? 'admin');
$csrfToken = (string) ($_SESSION['csrf_token'] ?? '');
$isAdmin = $currentRole === 'admin';
$targetRole = $isAdmin ? 'guest' : 'admin';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Mahasiswa — Sistem Akademik</title>
    <link rel="stylesheet" href="<?= $e($url('/css/style.css')) ?>">
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="<?= $e($url('/mahasiswa')) ?>">Sistem Akademik</a>
            <div class="role-switcher">
                <span class="role-badge <?= $isAdmin ? 'role-badge-admin' : 'role-badge-guest' ?>">
                    <?= $isAdmin ? 'Mode: Admin (Berwenang Hapus)' : 'Mode: Tamu (Tanpa Hak Hapus)' ?>
                </span>
                <form class="inline-form" method="post" action="<?= $e($url('/mahasiswa/role')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                    <input type="hidden" name="role" value="<?= $e($targetRole) ?>">
                    <button class="btn btn-sm btn-topbar" type="submit">
                        <?= $isAdmin ? 'Simulasi Tamu' : 'Kembali ke Admin' ?>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="card">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Daftar Mahasiswa</h1>
                    <p class="page-subtitle">Total data aktif: <?= $e((string) count($rows)) ?> mahasiswa</p>
                </div>
                <a class="btn btn-primary" href="<?= $e($url('/mahasiswa/create')) ?>">+ Tambah Mahasiswa</a>
            </div>

            <?php if (is_string($flashMessage) && $flashMessage !== ''): ?>
                <div class="alert alert-<?= $e($flashType) ?>" role="status">
                    <?= $e($flashMessage) ?>
                </div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="alert alert-error" role="alert">
                    <p class="alert-title">Terjadi kesalahan:</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($rows === []): ?>
                <div class="empty-state">
                    <p>Belum ada data mahasiswa yang tersimpan.</p>
                    <a class="btn btn-primary" href="<?= $e($url('/mahasiswa/create')) ?>">Tambah Data Pertama</a>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Program Studi</th>
                                <th>Angkatan</th>
                                <th>IPK</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $index => $row): ?>
                                <?php $id = (int) ($row['id'] ?? 0); ?>
                                <tr>
                                    <td><?= $e((string) ($index + 1)) ?></td>
                                    <td><?= $e($row['nim'] ?? '') ?></td>
                                    <td><?= $e($row['nama'] ?? '') ?></td>
                                    <td><?= $e($row['email'] ?? '') ?></td>
                                    <td><?= $e($row['prodi'] ?? '') ?></td>
                                    <td><?= $e($row['angkatan'] ?? '') ?></td>
                                    <td><?= $e($row['ipk'] ?? '') ?></td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn btn-sm btn-edit" href="<?= $e($url('/mahasiswa/' . $id . '/edit')) ?>">Edit</a>
                                            <form class="inline-form" method="post" action="<?= $e($url('/mahasiswa/' . $id . '/delete')) ?>" onsubmit="return confirm('Yakin ingin menghapus data mahasiswa ini?');">
                                                <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                                <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
