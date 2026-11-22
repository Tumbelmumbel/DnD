<?php
declare(strict_types=1);

/**
 * ajax/i18n.php – change the language of the interface
 *
 * POST  csrf=<token>&lang=<code>
 *   - Saves the language in the session and in the cookie "lang".
 *   - Signed-in user: also saves it in users.language.
 *   - Answer: {"lang": "<code>", "texts": {key: text, ...}}
 *
 * Works also without a sign-in (login and registration pages).
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$csrf = $_POST['csrf'] ?? '';
$lang = $_POST['lang'] ?? '';
if (!is_string($csrf) || !csrf_validate($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid session']);
    exit;
}
if (!is_string($lang) || !set_lang($lang)) {
    http_response_code(400);
    echo json_encode(['error' => 'Unknown language']);
    exit;
}

$user = current_user();
if ($user) {
    $stmt = db()->prepare('UPDATE users SET language = :lang WHERE id = :id');
    $stmt->execute([':lang' => $lang, ':id' => (int)$user['id']]);
}

echo json_encode(['lang' => $lang, 'texts' => i18n_merged($lang)], JSON_UNESCAPED_UNICODE);
