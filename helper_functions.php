<?php
declare(strict_types=1);

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Delete the accounts that were not verified before their link expired.
 * verification_expires_at is written with the PHP time, so the check uses the PHP time too.
 */
function delete_expired_unverified_users(PDO $pdo): void {
    $stmt = $pdo->prepare('
        DELETE FROM users
        WHERE email_verified_at IS NULL
          AND (verification_expires_at IS NULL OR verification_expires_at < :now)
    ');
    $stmt->execute([':now' => (new DateTimeImmutable('now'))->format('Y-m-d H:i:s')]);
}

/** Create a random token (user sees this); store only its hash */
function new_token(): string {
    return bin2hex(random_bytes(32)); // 64 hex chars
}	

/**
 * URL of a file in the web folder with a version number (its file time).
 * Browsers then load the new file after a deploy. Example: asset_url('/Assets/sandbox.css')
 */
function asset_url(string $path): string {
    $time = @filemtime(__DIR__ . $path);
    return $time ? $path . '?v=' . $time : $path;
}
