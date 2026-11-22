<?php
declare(strict_types=1);

/**
 * i18n.php – languages of the interface
 *
 * - Each language has one file lang/<code>.php that returns ['key' => 'text', ...].
 * - English (en) is the base language. A key that is missing in a language
 *   falls back to English, and then to the key (or the given fallback) itself.
 * - To add a language: add lang/<code>.php and one entry in i18n_languages().
 *
 * The current language comes from the session, then from the cookie "lang",
 * then the base language. login.php puts users.language into the session.
 */

const I18N_DEFAULT = 'en';

/** Supported languages: code => name in that language (for the language menu) */
function i18n_languages(): array {
    return [
        'en' => 'English',
        'de' => 'Deutsch',
    ];
}

function i18n_is_supported(?string $lang): bool {
    return is_string($lang) && array_key_exists($lang, i18n_languages());
}

/** The texts of one language file (empty array for an unknown language) */
function i18n_dict(string $lang): array {
    static $cache = [];
    if (!array_key_exists($lang, $cache)) {
        $file = __DIR__ . '/lang/' . $lang . '.php';
        $cache[$lang] = (i18n_is_supported($lang) && is_file($file)) ? (array)require $file : [];
    }
    return $cache[$lang];
}

/** All texts for a language, with English for the missing keys */
function i18n_merged(string $lang): array {
    return array_merge(i18n_dict(I18N_DEFAULT), i18n_dict($lang));
}

/** The language of this request */
function current_lang(): string {
    $lang = $_SESSION['lang'] ?? ($_COOKIE['lang'] ?? I18N_DEFAULT);
    return i18n_is_supported($lang) ? $lang : I18N_DEFAULT;
}

/** Translate a key. $fallback is shown if no language has the key. */
function t(string $key, ?string $fallback = null): string {
    $lang = current_lang();
    return i18n_dict($lang)[$key]
        ?? i18n_dict(I18N_DEFAULT)[$key]
        ?? ($fallback ?? $key);
}

/** Remember a language in the session and in a cookie (1 year) */
function set_lang(string $lang): bool {
    if (!i18n_is_supported($lang)) {
        return false;
    }
    $_SESSION['lang'] = $lang;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    setcookie('lang', $lang, [
        'expires'  => time() + 365 * 24 * 60 * 60,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    return true;
}
