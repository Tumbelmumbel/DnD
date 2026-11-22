<?php
declare(strict_types=1);

/**
 * config.php
 * Loads .env and builds $APP_CONFIG — NO session_start(), NO PDO connection.
 */

/* ---------- Load .env ---------- */
$envPath = __DIR__ . '/.env';
if (is_readable($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $_ENV[$k] = $v;
    }
}

/* ---------- Optional constants ---------- */
define('BASE_URL',       $_ENV['BASE_URL']       ?? 'https://rudolph.cologne');
define('MAIL_FROM',      $_ENV['MAIL_FROM']      ?? 'no-reply@rudolph.cologne');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'Rudolph Cologne');

/* ---------- Build global config array ---------- */
$APP_CONFIG = [
    'app' => [
        'env'      => $_ENV['APP_ENV'] ?? 'prod',
        'base_url' => BASE_URL,
    ],

    'db' => [
        'host'    => $_ENV['DB_HOST'] ?? 'db5020006586.hosting-data.io',
        'name'    => $_ENV['DB_NAME'] ?? 'dbs15432866',
        'user'    => $_ENV['DB_USER'] ?? 'dbu980466',
        'pass'    => $_ENV['DB_PASS'] ?? '100%GreatWhite',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],

    'session' => [
        'name'            => $_ENV['SESSION_NAME'] ?? 'sid',
        'inactivity_limit'=> 30 * 60,
        'absolute_limit'  => 8 * 60 * 60,
        'rotate_every'    => 5 * 60,
        'samesite'        => 'Lax',
    ],

    'mail' => [
        'smtp_host'   => $_ENV['SMTP_HOST'] ?? '',
        'smtp_port'   => $_ENV['SMTP_PORT'] ?? '587',
        'smtp_user'   => $_ENV['SMTP_USER'] ?? '',
        'smtp_pass'   => $_ENV['SMTP_PASS'] ?? '',
        'smtp_secure' => $_ENV['SMTP_SECURE'] ?? 'tls',
        'from'        => MAIL_FROM,
        'from_name'   => MAIL_FROM_NAME,
        'reply_to'    => $_ENV['MAIL_REPLYTO'] ?? MAIL_FROM,
    ],
];