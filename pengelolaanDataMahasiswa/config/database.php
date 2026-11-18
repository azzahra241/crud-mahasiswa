<?php

declare(strict_types=1);

/**
 * Mengembalikan konfigurasi database dengan dukungan override lokal
 * (`config/database.local.php`) atau environment variable.
 *
 * @return array{host: string, port: string, dbname: string, charset: string, user: string, pass: string}
 */
function getDatabaseConfig(): array
{
    $config = [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'dbname' => getenv('DB_NAME') ?: 'akademik1',
        'charset' => 'utf8mb4',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
    ];

    $localConfigPath = __DIR__ . '/database.local.php';
    if (is_file($localConfigPath)) {
        $localConfig = require $localConfigPath;
        if (is_array($localConfig)) {
            $config = array_merge($config, $localConfig);
        }
    }

    return $config;
}

/**
 * Membuat instance PDO terpusat untuk diinjeksi ke Model melalui constructor.
 *
 * @throws PDOException Jika koneksi ke database gagal.
 */
function createPdoConnection(): PDO
{
    $config = getDatabaseConfig();
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['dbname'],
        $config['charset']
    );

    return new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
