<?php
if (!function_exists('app_default_settings')) {
    function app_default_settings(): array
    {
        $mailUsername = getenv('APP_MAIL_USERNAME') ?: null;

        return [
            'db' => [
                'host' => getenv('APP_DB_HOST') ?: null,
                'user' => getenv('APP_DB_USER') ?: null,
                'pass' => getenv('APP_DB_PASS'),
                'name' => getenv('APP_DB_NAME') ?: null,
            ],
            'mail' => [
                'host' => getenv('APP_MAIL_HOST') ?: null,
                'port' => (int) (getenv('APP_MAIL_PORT') ?: 587),
                'security' => getenv('APP_MAIL_SECURITY') ?: 'tls',
                'username' => $mailUsername,
                'password' => getenv('APP_MAIL_PASSWORD'),
                'from_email' => getenv('APP_MAIL_FROM_EMAIL') ?: $mailUsername,
                'from_name' => getenv('APP_MAIL_FROM_NAME') ?: 'K-Pop Universe',
                'admin_email' => getenv('APP_MAIL_ADMIN_EMAIL') ?: $mailUsername,
            ],
            'app' => [
                'base_url' => rtrim((string) (getenv('APP_BASE_URL') ?: ''), '/'),
                'reset_token_ttl_minutes' => (int) (getenv('APP_RESET_TOKEN_TTL_MINUTES') ?: 30),
            ],
        ];
    }

    function load_app_settings(): array
    {
        static $settings = null;

        if ($settings !== null) {
            return $settings;
        }

        $settings = app_default_settings();
        $localFile = __DIR__ . '/app_settings.local.php';

        // O ficheiro local permite ter credenciais fora do Git sem mexer no codigo principal.
        if (is_file($localFile)) {
            $overrides = require $localFile;
            if (is_array($overrides)) {
                $settings = array_replace_recursive($settings, $overrides);
            }
        }

        return $settings;
    }

    function app_setting(array $settings, string $path, $default = null)
    {
        $segments = explode('.', $path);
        $value = $settings;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    function app_base_url(array $settings): string
    {
        $configured = app_setting($settings, 'app.base_url', '');
        if (is_string($configured) && $configured !== '') {
            return rtrim($configured, '/');
        }

        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = trim(dirname($scriptName), '/.');

        return $scheme . '://' . $host . ($basePath !== '' ? '/' . $basePath : '');
    }

    function app_join_url(string $baseUrl, string $path): string
    {
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}
