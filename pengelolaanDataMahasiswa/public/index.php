<?php

declare(strict_types=1);

/**
 * Front Controller (Entry Point Tunggal Aplikasi MVC)
 * Pertemuan 11 — Kasus 1: Pengelolaan Data Mahasiswa
 */

// Jika dijalankan memakai `php -S localhost:8000 -t public public/index.php`,
// biarkan built-in server melayani file statis (CSS/JS/gambar) secara langsung.
if (PHP_SAPI === 'cli-server') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $requestedFile = realpath(__DIR__ . $uriPath);
    $publicRoot = realpath(__DIR__);
    if (
        $requestedFile !== false
        && $publicRoot !== false
        && str_starts_with($requestedFile, $publicRoot . DIRECTORY_SEPARATOR)
        && $requestedFile !== __FILE__
        && is_file($requestedFile)
    ) {
        return false;
    }
}

define('APP_ROOT', dirname(__DIR__));

// Konfigurasi error & logging: detail teknis masuk log, tidak bocor ke browser (Slide 33)
ini_set('display_errors', '0');
ini_set('log_errors', '1');
$logDir = APP_ROOT . '/logs';
if (is_dir($logDir) && is_writable($logDir)) {
    ini_set('error_log', $logDir . '/app.log');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/app/Router.php';

define('APP_BASE_URL', Router::detectBaseUrl($_SERVER['SCRIPT_NAME'] ?? ''));

/**
 * Helper pembentuk URL relatif terhadap root aplikasi (`APP_BASE_URL`).
 */
function appUrl(string $path = '/'): string
{
    $normalized = '/' . ltrim($path, '/');
    return APP_BASE_URL . $normalized;
}

/**
 * Menampilkan halaman error aman tanpa detail teknis sensitif (Slide 33).
 */
function renderHttpError(int $statusCode, string $title, string $message): void
{
    http_response_code($statusCode);

    $errorView = APP_ROOT . '/app/Views/errors/' . $statusCode . '.php';
    if (is_file($errorView)) {
        $errorTitle = $title;
        $errorMessage = $message;
        require $errorView;
        return;
    }

    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $homeUrl = htmlspecialchars(appUrl('/mahasiswa'), ENT_QUOTES, 'UTF-8');

    echo "<!doctype html>\n<html lang=\"id\">\n<head>\n"
        . "    <meta charset=\"utf-8\">\n"
        . "    <title>{$statusCode} - {$safeTitle}</title>\n"
        . "    <style>body{font-family:sans-serif;max-width:640px;margin:48px auto;padding:0 16px;line-height:1.6;color:#1e293b;}</style>\n"
        . "</head>\n<body>\n"
        . "    <h1>{$statusCode} — {$safeTitle}</h1>\n"
        . "    <p>{$safeMessage}</p>\n"
        . "    <p><a href=\"{$homeUrl}\">&larr; Kembali ke Data Mahasiswa</a></p>\n"
        . "</body>\n</html>";
}

/**
 * Membuat instance controller sesuai kontrak DI (Slide 28),
 * sambil tetap fleksibel bila constructor menerima Model, PDO, atau tanpa argumen.
 */
function instantiateController(string $controllerClass, PDO $pdo): object
{
    $modelFile = APP_ROOT . '/app/Models/MahasiswaModel.php';
    if (!is_file($modelFile)) {
        throw new RuntimeException('Komponen Model belum tersedia: app/Models/MahasiswaModel.php');
    }
    require_once $modelFile;

    if (!class_exists('MahasiswaModel')) {
        throw new RuntimeException('Class MahasiswaModel tidak ditemukan di app/Models/MahasiswaModel.php');
    }

    $reflection = new ReflectionClass($controllerClass);
    $constructor = $reflection->getConstructor();

    if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
        return $reflection->newInstance();
    }

    $firstParamType = $constructor->getParameters()[0]->getType();
    if ($firstParamType instanceof ReflectionNamedType && $firstParamType->getName() === PDO::class) {
        return $reflection->newInstance($pdo);
    }

    $model = new MahasiswaModel($pdo);
    return $reflection->newInstance($model);
}

// =============================================================================
// Pendaftaran Rute HTTP Aplikasi (Slide 16, 37, 39, 43)
// =============================================================================
$router = new Router();

$router->get('/', static function (): void {
    header('Location: ' . appUrl('/mahasiswa'));
    exit;
});

$router->get('/mahasiswa', ['MahasiswaController', 'index']);
$router->get('/mahasiswa/create', ['MahasiswaController', 'create']);
$router->post('/mahasiswa', ['MahasiswaController', 'store']);
$router->post('/mahasiswa/role', ['MahasiswaController', 'switchRole']);
$router->get('/mahasiswa/{id}/edit', ['MahasiswaController', 'edit']);
$router->post('/mahasiswa/{id}/update', ['MahasiswaController', 'update']);
$router->post('/mahasiswa/{id}/delete', ['MahasiswaController', 'delete']);

// =============================================================================
// Eksekusi Routing & Dispatch
// =============================================================================
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = Router::extractPath($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['SCRIPT_NAME'] ?? '');
$match = $router->resolve($method, $path);

if ($match['status'] === 404) {
    renderHttpError(404, 'Halaman Tidak Ditemukan', 'Rute URL yang Anda minta tidak tersedia.');
    exit;
}

if ($match['status'] === 405) {
    $allowed = $match['allowed'] ?? [];
    if ($allowed !== []) {
        header('Allow: ' . implode(', ', $allowed));
    }
    renderHttpError(405, 'Metode Tidak Diizinkan', 'Metode HTTP tidak diizinkan untuk rute ini.');
    exit;
}

$handler = $match['handler'];
$params = $match['params'] ?? [];

if ($handler instanceof Closure) {
    $handler(...$params);
    exit;
}

[$controllerClass, $action] = $handler;
$controllerFile = APP_ROOT . '/app/Controllers/' . $controllerClass . '.php';

if (!is_file($controllerFile)) {
    renderHttpError(
        503,
        'Modul Controller Belum Siap',
        "File {$controllerClass}.php belum tersedia. Pastikan Orang 2 (Model) dan Orang 3 (Controller) sudah menggabungkan kodenya."
    );
    exit;
}

require_once $controllerFile;

if (!class_exists($controllerClass) || !method_exists($controllerClass, $action)) {
    error_log("Controller atau method tidak ditemukan: {$controllerClass}::{$action}");
    renderHttpError(500, 'Terjadi Kesalahan Sistem', 'Konfigurasi controller atau action belum lengkap.');
    exit;
}

try {
    $pdo = createPdoConnection();
    $controller = instantiateController($controllerClass, $pdo);
    $typedParams = array_map(
        static fn (string $val): int|string => ctype_digit($val) ? (int) $val : $val,
        $params
    );
    $controller->$action(...$typedParams);
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    renderHttpError(500, 'Gangguan Koneksi Data', 'Layanan basis data sedang mengalami gangguan. Silakan coba lagi.');
} catch (Throwable $e) {
    error_log('Application Error: ' . $e->getMessage());
    renderHttpError(500, 'Terjadi Kesalahan Sistem', 'Permintaan gagal diproses.');
}
