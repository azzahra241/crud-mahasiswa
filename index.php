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

function buildPageUrl(int $page, string $q): string
{
    $params = [];
    if ($q !== '') {
        $params['q'] = $q;
    }
    $params['page'] = $page;

    return 'index.php?' . http_build_query($params);
}

$q = trim((string) ($_GET['q'] ?? ''));
$perPage = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));

$where = 'WHERE deleted_at IS NULL';
$searchParams = [];

if ($q !== '') {
    $where .= ' AND (nim LIKE ? OR nama LIKE ? OR prodi LIKE ? OR email LIKE ?)';
    $like = '%' . $q . '%';
    $searchParams = [$like, $like, $like, $like];
}

$countSql = "SELECT COUNT(*) FROM mahasiswa {$where}";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($searchParams);
$totalRows = (int) $countStmt->fetchColumn();

$totalPages = max(1, (int) ceil($totalRows / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

$dataSql = "SELECT id, nim, nama, email, prodi, angkatan, ipk
            FROM mahasiswa
            {$where}
            ORDER BY id ASC
            LIMIT ? OFFSET ?";
$dataStmt = $pdo->prepare($dataSql);

$paramIndex = 1;
foreach ($searchParams as $val) {
    $dataStmt->bindValue($paramIndex++, $val, PDO::PARAM_STR);
}
$dataStmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
$dataStmt->bindValue($paramIndex, $offset, PDO::PARAM_INT);
$dataStmt->execute();
$mahasiswa = $dataStmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Mahasiswa</title>
    <style>
        body { font-family: sans-serif; margin: 24px; line-height: 1.5; }
        .toolbar { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 16px; }
        .toolbar form { display: flex; gap: 8px; align-items: center; }
        table { border-collapse: collapse; width: 100%; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background: #f4f4f4; }
        .aksi { white-space: nowrap; }
        .aksi form { display: inline; }
        .pagination { margin-top: 16px; display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
        .pagination a, .pagination span {
            padding: 6px 10px;
            border: 1px solid #ccc;
            text-decoration: none;
            color: #222;
        }
        .pagination .active { background: #222; color: #fff; border-color: #222; }
        .pagination .disabled { color: #999; border-color: #e5e5e5; }
    </style>
</head>
<body>
    <h2>Data Mahasiswa</h2>

    <?php if (!empty($_SESSION['flash'])): ?>
        <p style="color:<?= $_SESSION['flash']['tipe'] === 'ok' ? 'green' : 'red' ?>">
            <?= e((string) $_SESSION['flash']['pesan']) ?>
        </p>
        <?php unset($_SESSION['flash']); ?>
    <?php endif ?>

    <div class="toolbar">
        <form method="get" action="index.php">
            <input
                type="text"
                name="q"
                value="<?= e($q) ?>"
                placeholder="Cari NIM, nama, prodi, email..."
            >
            <button type="submit">Cari</button>
            <?php if ($q !== ''): ?>
                <a href="index.php">Reset</a>
            <?php endif ?>
        </form>

        <a href="krs-create.php">+ Isi KRS</a>
    </div>

    <p>
        Menampilkan <?= count($mahasiswa) ?> dari <?= $totalRows ?> data
        (Halaman <?= $page ?> dari <?= $totalPages ?>).
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Prodi</th>
                <th>Angkatan</th>
                <th>IPK</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($mahasiswa)): ?>
                <tr>
                    <td colspan="8">Data mahasiswa tidak ditemukan.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($mahasiswa as $i => $m): ?>
                    <tr>
                        <td><?= $offset + $i + 1 ?></td>
                        <td><?= e((string) $m['nim']) ?></td>
                        <td><?= e((string) $m['nama']) ?></td>
                        <td><?= e((string) $m['email']) ?></td>
                        <td><?= e((string) $m['prodi']) ?></td>
                        <td><?= e((string) $m['angkatan']) ?></td>
                        <td><?= e((string) $m['ipk']) ?></td>
                        <td class="aksi">
                            <a href="edit.php?id=<?= (int) $m['id'] ?>">Edit</a>
                            <form
                                method="post"
                                action="delete.php"
                                onsubmit="return confirm('Yakin ingin menghapus data <?= e((string) $m['nama']) ?>?');"
                            >
                                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                <button type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php endif ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= e(buildPageUrl($page - 1, $q)) ?>">&laquo; Prev</a>
            <?php else: ?>
                <span class="disabled">&laquo; Prev</span>
            <?php endif ?>

            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p === $page): ?>
                    <span class="active"><?= $p ?></span>
                <?php else: ?>
                    <a href="<?= e(buildPageUrl($p, $q)) ?>"><?= $p ?></a>
                <?php endif ?>
            <?php endfor ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= e(buildPageUrl($page + 1, $q)) ?>">Next &raquo;</a>
            <?php else: ?>
                <span class="disabled">Next &raquo;</span>
            <?php endif ?>
        </div>
    <?php endif ?>
</body>
</html>

