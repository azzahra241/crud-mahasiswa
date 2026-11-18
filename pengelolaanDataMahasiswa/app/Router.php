<?php

declare(strict_types=1);

/**
 * Router sederhana untuk memetakan HTTP method dan path URL ke Controller/Action.
 * Mengikuti pola Front Controller pada materi Pertemuan 11 (Slide 13–16 & 37).
 */
final class Router
{
    /** @var array<int, array{method: string, pattern: string, regex: string, handler: mixed}> */
    private array $routes = [];

    public function get(string $pattern, mixed $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, mixed $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, mixed $handler): void
    {
        $normalizedMethod = strtoupper(trim($method));
        $normalizedPattern = self::normalizePath($pattern);

        $this->routes[] = [
            'method' => $normalizedMethod,
            'pattern' => $normalizedPattern,
            'regex' => $this->compilePattern($normalizedPattern),
            'handler' => $handler,
        ];
    }

    /**
     * Mencocokkan request HTTP method + path terhadap route yang terdaftar.
     *
     * @return array{status: int, handler?: mixed, params?: array<int, string>, allowed?: array<int, string>}
     */
    public function resolve(string $method, string $path): array
    {
        $normalizedMethod = strtoupper(trim($method));
        $normalizedPath = self::normalizePath($path);
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $normalizedPath, $matches) !== 1) {
                continue;
            }

            if ($route['method'] === $normalizedMethod) {
                array_shift($matches);
                return [
                    'status' => 200,
                    'handler' => $route['handler'],
                    'params' => $matches,
                ];
            }

            $allowedMethods[] = $route['method'];
        }

        if ($allowedMethods !== []) {
            return [
                'status' => 405,
                'allowed' => array_values(array_unique($allowedMethods)),
            ];
        }

        return ['status' => 404];
    }

    /**
     * Mendeteksi base URL aplikasi agar bekerja baik di `php -S -t public`
     * maupun di subdirektori XAMPP/Apache (`/folder/public`).
     */
    public static function detectBaseUrl(string $scriptName = ''): string
    {
        $normalizedScript = str_replace('\\', '/', $scriptName);
        if ($normalizedScript === '' || basename($normalizedScript) !== 'index.php') {
            return '';
        }

        $dir = rtrim(str_replace('\\', '/', dirname($normalizedScript)), '/');
        return $dir === '/' ? '' : $dir;
    }

    /**
     * Mengambil path bersih dari REQUEST_URI setelah menghapus query string,
     * base URL subdirektori, dan prefix `/index.php`.
     */
    public static function extractPath(string $requestUri, string $scriptName = ''): string
    {
        $rawPath = parse_url($requestUri, PHP_URL_PATH);
        $path = is_string($rawPath) ? $rawPath : '/';

        $baseUrl = self::detectBaseUrl($scriptName);
        if ($baseUrl !== '' && str_starts_with($path, $baseUrl)) {
            $path = substr($path, strlen($baseUrl));
        }

        if (str_starts_with($path, '/index.php')) {
            $path = substr($path, strlen('/index.php'));
        }

        return self::normalizePath($path === false ? '/' : $path);
    }

    private static function normalizePath(string $path): string
    {
        $trimmed = '/' . trim($path, '/');
        return $trimmed === '//' ? '/' : $trimmed;
    }

    private function compilePattern(string $pattern): string
    {
        $parts = explode('/', trim($pattern, '/'));
        if ($parts === ['']) {
            return '#^/$#';
        }

        $regexSegments = [];
        foreach ($parts as $segment) {
            if ($segment === '{id}') {
                $regexSegments[] = '(\d+)';
            } elseif (preg_match('/^\{[a-zA-Z_][a-zA-Z0-9_]*\}$/', $segment) === 1) {
                $regexSegments[] = '([^/]+)';
            } else {
                $regexSegments[] = preg_quote($segment, '#');
            }
        }

        return '#^/' . implode('/', $regexSegments) . '$#';
    }
}
