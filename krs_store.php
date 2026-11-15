<?php

declare(strict_types=1);

session_start();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: krs-create.php');
    exit;
}

function redirectWithFlash(string $target, string $tipe, string $pesan): void
{
    $_SESSION['flash'] = [
        'tipe'  => $tipe,
        'pesan' => $pesan,
    ];
    session_write_close();
    header('Location: ' . $target);
    exit;
}

function isValidUtf8WithoutControl(string $value): bool
{
    return preg_match('//u', $value) === 1
        && preg_match('/[\x00-\x1F\x7F]/', $value) === 0;
}

function utf8Length(string $value): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : (int) preg_match_all('/./us', $value);
}

function toUpperUtf8(string $value): string
{
    return function_exists('mb_strtoupper')
        ? mb_strtoupper($value, 'UTF-8')
        : strtoupper($value);
}

if (
    isset($_POST['csrf_token']) &&
    (
        !is_string($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals((string) $_SESSION['csrf_token'], $_POST['csrf_token'])
    )
) {
    redirectWithFlash('krs-create.php', 'error', 'Token CSRF tidak valid.');
}

require __DIR__ . '/config/database.php';

$nimRaw         = $_POST['nim'] ?? null;
$semesterRaw    = $_POST['semester'] ?? null;
$tahunAjaranRaw = $_POST['tahun_ajaran'] ?? null;
$kodeMkRaw      = $_POST['kode_mk'] ?? null;

if (!is_string($nimRaw)) {
    redirectWithFlash('krs-create.php', 'error', 'NIM mahasiswa wajib diisi.');
}

$nim = trim($nimRaw);
if ($nim === '' || !isValidUtf8WithoutControl($nim)) {
    redirectWithFlash('krs-create.php', 'error', 'Format NIM mahasiswa tidak valid.');
}
if (utf8Length($nim) > 15) {
    redirectWithFlash('krs-create.php', 'error', 'NIM mahasiswa maksimal 15 karakter.');
}

$semester = false;
if (is_int($semesterRaw)) {
    if ($semesterRaw >= 1 && $semesterRaw <= 14) {
        $semester = $semesterRaw;
    }
} elseif (is_string($semesterRaw)) {
    $semesterStr = trim($semesterRaw);
    if (preg_match('/^\d+$/', $semesterStr) === 1) {
        $semesterDigits = ltrim($semesterStr, '0');
        if ($semesterDigits !== '' && strlen($semesterDigits) <= 2) {
            $parsedSemester = (int) $semesterDigits;
            if ($parsedSemester >= 1 && $parsedSemester <= 14) {
                $semester = $parsedSemester;
            }
        }
    }
}

if ($semester === false) {
    redirectWithFlash('krs-create.php', 'error', 'Semester harus berupa bilangan bulat antara 1 sampai 14.');
}

if (!is_string($tahunAjaranRaw)) {
    redirectWithFlash(
        'krs-create.php',
        'error',
        'Format tahun ajaran tidak valid. Gunakan format YYYY/YYYY yang berurutan (contoh: 2026/2027).'
    );
}

$tahunAjaran = trim($tahunAjaranRaw);
if (
    !isValidUtf8WithoutControl($tahunAjaran) ||
    !preg_match('/^(\d{4})\/(\d{4})$/', $tahunAjaran, $tahunMatch) ||
    (int) $tahunMatch[1] < 1900 ||
    (int) $tahunMatch[1] > 2100 ||
    (int) $tahunMatch[2] !== (int) $tahunMatch[1] + 1
) {
    redirectWithFlash(
        'krs-create.php',
        'error',
        'Format tahun ajaran tidak valid. Gunakan format YYYY/YYYY yang berurutan (contoh: 2026/2027).'
    );
}

if (!is_array($kodeMkRaw)) {
    redirectWithFlash('krs-create.php', 'error', 'Daftar kode mata kuliah tidak valid.');
}

$kodeMkList = [];
$seenKode   = [];

foreach ($kodeMkRaw as $item) {
    if (!is_string($item)) {
        redirectWithFlash('krs-create.php', 'error', 'Format kode mata kuliah tidak valid.');
    }

    $trimmed = trim($item);
    if ($trimmed === '') {
        continue;
    }

    if (!isValidUtf8WithoutControl($trimmed)) {
        redirectWithFlash('krs-create.php', 'error', 'Format kode mata kuliah tidak valid.');
    }

    $kode = toUpperUtf8($trimmed);
    if (utf8Length($kode) > 12) {
        redirectWithFlash('krs-create.php', 'error', 'Kode mata kuliah maksimal 12 karakter.');
    }

    if (isset($seenKode[$kode])) {
        redirectWithFlash(
            'krs-create.php',
            'error',
            'Kode mata kuliah ' . $kode . ' duplikat dalam satu pengisian KRS.'
        );
    }

    $seenKode[$kode] = true;
    $kodeMkList[]    = $kode;
}

if (empty($kodeMkList)) {
    redirectWithFlash('krs-create.php', 'error', 'Minimal satu kode mata kuliah harus diisi.');
}

try {
    $pdo->beginTransaction();

    $stmtMhs = $pdo->prepare(
        'SELECT nim FROM mahasiswa WHERE nim = :nim AND deleted_at IS NULL LIMIT 1 FOR UPDATE'
    );
    $stmtMhs->execute(['nim' => $nim]);
    $mhsRow = $stmtMhs->fetch();
    if (!$mhsRow) {
        throw new DomainException('Data mahasiswa tidak ditemukan atau sudah dihapus.');
    }
    $nimCanonical = (string) $mhsRow['nim'];

    $stmtMk = $pdo->prepare(
        'SELECT kode_mk FROM mata_kuliah WHERE kode_mk = :kode_mk LIMIT 1'
    );
    $stmtInsertKrs = $pdo->prepare(
        "INSERT INTO krs (nim, kode_mk, semester, tahun_ajaran, status_krs)
         VALUES (:nim, :kode_mk, :semester, :tahun_ajaran, 'aktif')"
    );

    foreach ($kodeMkList as $kodeMk) {
        $stmtMk->execute(['kode_mk' => $kodeMk]);
        $mkRow = $stmtMk->fetch();
        if (!$mkRow) {
            throw new DomainException(
                'Kode mata kuliah ' . $kodeMk . ' tidak ditemukan. Seluruh penyimpanan KRS dibatalkan (rollback).'
            );
        }
        $kodeMkCanonical = (string) $mkRow['kode_mk'];

        $stmtInsertKrs->execute([
            'nim'          => $nimCanonical,
            'kode_mk'      => $kodeMkCanonical,
            'semester'     => $semester,
            'tahun_ajaran' => $tahunAjaran,
        ]);
    }

    $pdo->commit();

    redirectWithFlash(
        'index.php',
        'ok',
        sprintf(
            'KRS berhasil disimpan (%d mata kuliah untuk NIM %s, semester %d, tahun ajaran %s).',
            count($kodeMkList),
            $nimCanonical,
            $semester,
            $tahunAjaran
        )
    );
} catch (DomainException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    redirectWithFlash('krs-create.php', 'error', $e->getMessage());
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('KRS store PDOException: ' . $e->getMessage());

    $driverCode = (int) ($e->errorInfo[1] ?? 0);
    if ($driverCode === 1062) {
        $pesan = 'Gagal menyimpan KRS: salah satu mata kuliah sudah terdaftar pada semester dan tahun ajaran ini. Seluruh perubahan dibatalkan (rollback).';
    } elseif ($driverCode === 1452) {
        $pesan = 'Gagal menyimpan KRS: data mahasiswa atau kode mata kuliah tidak valid. Seluruh perubahan dibatalkan (rollback).';
    } else {
        $pesan = 'Gagal menyimpan KRS karena kesalahan database. Seluruh perubahan dibatalkan (rollback).';
    }

    redirectWithFlash('krs-create.php', 'error', $pesan);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('KRS store Throwable: ' . $e->getMessage());
    redirectWithFlash(
        'krs-create.php',
        'error',
        'Terjadi kesalahan saat menyimpan KRS. Seluruh perubahan dibatalkan (rollback).'
    );
}
