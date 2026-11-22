<?php
declare(strict_types=1);

/**
 * BOOTSTRAP.PHP
 * ---------------------------------------------------------
 * Responsibilities:
 *  - Load config.php (which loads .env and builds $APP_CONFIG)
 *  - Initialize error handling
 *  - Initialize secure session cookie settings + session_start()
 *  - Provide helper functions:
 *        redirect(), e(), flash(), csrf_token(), csrf_validate(),
 *        current_user(), db(), guard_protect()
 *  - Provide session lifecycle enforcement (timeouts + rotation)
 *
 * Ensure config.php contains NO session_start() and NO new PDO().
 */

////////////////////////////////////////////////////////////////
// 0) LOAD CONFIGURATION
////////////////////////////////////////////////////////////////
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helper_functions.php';
require_once __DIR__ . '/i18n.php';


////////////////////////////////////////////////////////////////
// 1) ERROR HANDLING (no deprecated E_STRICT in PHP 8+)
////////////////////////////////////////////////////////////////

$isProd = ($APP_CONFIG['app']['env'] ?? 'prod') === 'prod';

if (!$isProd) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    // show everything except deprecated warnings (clean dev experience)
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
} else {
    ini_set('display_errors', '0');
    // hide deprecated notices in production
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}


////////////////////////////////////////////////////////////////
// 2) SESSION INITIALIZATION (secure cookies + hardening)
////////////////////////////////////////////////////////////////

// HTTPS detection
$https =
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (($_SERVER['SERVER_PORT'] ?? null) == 443) ||
    (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

// Cookie domain (optional, typically leave empty unless subdomains needed)
$cookieParams = session_get_cookie_params();
$cookieDomain = $_ENV['SESSION_DOMAIN'] ?? $cookieParams['domain'] ?? '';
$cookiePath   = $cookieParams['path'] ?: '/';

// Apply secure session cookie parameters
session_name($APP_CONFIG['session']['name'] ?? 'sid');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => $cookiePath,
    'domain'   => $cookieDomain ?: '',
    'secure'   => $https,
    'httponly' => true,
    'samesite' => $APP_CONFIG['session']['samesite'] ?? 'Lax',
]);

// Harden session behaviour
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');

// Start the session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


////////////////////////////////////////////////////////////////
// 3) UTILITY FUNCTIONS
////////////////////////////////////////////////////////////////

/**
 * Redirect helper: accepts absolute or app-relative URLs.
 */
function redirect(string $to, int $code = 302): never
{
    // Absolute URL?
    if (preg_match('#^https?://#i', $to)) {
        header("Location: {$to}", true, $code);
        exit;
    }

    // Normalise relative path
    $to = '/' . ltrim($to, '/');

    // Use BASE_URL (absolute host) if defined
    if (defined('BASE_URL') && BASE_URL !== '') {
        $base = rtrim(BASE_URL, '/');
        header("Location: {$base}{$to}", true, $code);
    } else {
        header("Location: {$to}", true, $code);
    }
    exit;
}


/**
 * Flash messages (set once, read once)
 */
function flash(?string $key = null, ?string $message = null): mixed {
    if (!isset($_SESSION['_flash'])) $_SESSION['_flash'] = [];
    if ($key !== null && $message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return true;
    }
    if ($key !== null) {
        $v = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $v;
    }
    return null;
}


/**
 * CSRF token generator
 */
function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}


/**
 * CSRF validation
 */
function csrf_validate(?string $token): bool {
    return is_string($token) && hash_equals($_SESSION['_csrf'] ?? '', $token);
}


/**
 * Return current authenticated user, or null
 */
function current_user(): ?array {
    return (!empty($_SESSION['user']) && !empty($_SESSION['user']['id']))
        ? $_SESSION['user']
        : null;
}


////////////////////////////////////////////////////////////////
// 4) DATABASE HELPER (PDO SINGLETON)
////////////////////////////////////////////////////////////////

/**
 * db() — returns the PDO connection lazily.
 */
function db(): PDO {
    static $pdo = null;
    global $APP_CONFIG;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // Stop with an error if .env is missing or incomplete
    foreach (['host', 'name', 'user', 'pass'] as $key) {
        if (($APP_CONFIG['db'][$key] ?? '') === '') {
            error_log("db(): DB setting '{$key}' is missing. Check the .env file.");
            http_response_code(500);
            exit('Database configuration missing.');
        }
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $APP_CONFIG['db']['host'],
        $APP_CONFIG['db']['name'],
        $APP_CONFIG['db']['charset']
    );

    try {
        $pdo = new PDO(
            $dsn,
            $APP_CONFIG['db']['user'],
            $APP_CONFIG['db']['pass'],
            $APP_CONFIG['db']['options']
        );
    } catch (Throwable $e) {
        http_response_code(500);
        exit('Database connection failed.');
    }

    return $pdo;
}


////////////////////////////////////////////////////////////////
// 5) SESSION GUARD: login required + timeouts + rotation
////////////////////////////////////////////////////////////////

function guard_protect(): void {
    global $APP_CONFIG;

    $u = current_user();
    
	if (!$u) {
		if (is_ajax()) {
			http_response_code(401);
			echo json_encode(['error' => 'not_authenticated']);
			exit;
		}
		redirect('/login.php');
	}

    // Sessions end when the password is reset (session_version changes)
    $stmt = db()->prepare('SELECT session_version FROM users WHERE id = :id');
    $stmt->execute([':id' => (int)$u['id']]);
    $currentVersion = $stmt->fetchColumn();
    if ($currentVersion === false || (int)$currentVersion !== (int)($u['sv'] ?? -1)) {
        end_session_and_redirect('revoked');
    }

    // metadata namespace for session lifecycle
    $_SESSION['meta'] = $_SESSION['meta'] ?? [
        'login_time'    => $u['t'] ?? time(),  // login timestamp from login.php
        'last_activity' => time(),
        'last_rotation' => time(),
    ];

    $now       = time();
    $inactLim  = $APP_CONFIG['session']['inactivity_limit'] ?? (30 * 60);
    $absLim    = $APP_CONFIG['session']['absolute_limit']   ?? (8 * 60 * 60);
    $rotateSec = $APP_CONFIG['session']['rotate_every']     ?? (5 * 60);

    // absolute timeout
    if ($now - $_SESSION['meta']['login_time'] > $absLim) {
        end_session_and_redirect('absolute');
    }

    // inactivity timeout
    if ($now - $_SESSION['meta']['last_activity'] > $inactLim) {
        end_session_and_redirect('inactivity');
    }

    // update sliding activity
    $_SESSION['meta']['last_activity'] = $now;

    // rotate session ID periodically
    if ($now - $_SESSION['meta']['last_rotation'] > $rotateSec) {
        session_regenerate_id(true);
        $_SESSION['meta']['last_rotation'] = $now;
    }
}

function is_ajax(): bool {
    return (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    );
}


/**
 * Helper: destroy session fully + redirect with reason.
 */
function end_session_and_redirect(string $reason): never
{
    // clear session array
    $_SESSION = [];


    // remove cookie
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();

    redirect('/login.php?expired=' . urlencode($reason));
}
