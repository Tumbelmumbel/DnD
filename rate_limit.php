<?php
declare(strict_types=1);

/**
 * rate_limit.php – limits for sign-in, registration, new verification links and password resets
 *
 * - The table rate_limit stores one row for each attempt: the action, a keyed hash (HMAC-SHA-256)
 *   of the IP address or of the email address, and the time.
 * - The IP address and the email address are never stored in plain text.
 * - Rows older than 24 hours are deleted.
 * - The key comes from RATE_LIMIT_KEY in .env.
 */

const RATE_LIMIT_KEEP_SECONDS = 24 * 60 * 60;

/** Key for the hashes. Without RATE_LIMIT_KEY: a key from the database password (also a secret of the server). */
function rate_limit_key(): string {
    $key = (string)($_ENV['RATE_LIMIT_KEY'] ?? '');
    if ($key === '') {
        $key = hash('sha256', 'rate_limit|' . (string)($_ENV['DB_PASS'] ?? ''));
    }
    return $key;
}

function rate_limit_hash(string $kind, string $value): string {
    return hash_hmac('sha256', $kind . '|' . $value, rate_limit_key());
}

/** Hash of the IP address of this request */
function rate_limit_ip_hash(): string {
    return rate_limit_hash('ip', (string)($_SERVER['REMOTE_ADDR'] ?? ''));
}

/** Hash of an email address (lowercase, without spaces) */
function rate_limit_email_hash(string $email): string {
    return rate_limit_hash('email', strtolower(trim($email)));
}

/** PHP time, minus $seconds, in the format of the database */
function rate_limit_time(int $seconds = 0): string {
    return (new DateTimeImmutable('now'))->modify('-' . $seconds . ' seconds')->format('Y-m-d H:i:s');
}

/**
 * true if one of the limits is reached.
 * $limits: [[subject_hash, max_attempts], ...] for the last $windowSeconds.
 */
function rate_limit_exceeded(PDO $pdo, string $action, array $limits, int $windowSeconds): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM rate_limit
        WHERE action = :action AND subject_hash = :subject AND created_at >= :since');
    foreach ($limits as [$subject, $max]) {
        $stmt->execute([':action' => $action, ':subject' => $subject, ':since' => rate_limit_time($windowSeconds)]);
        if ((int)$stmt->fetchColumn() >= $max) {
            return true;
        }
    }
    return false;
}

/** Store one attempt for each subject hash, and delete the rows older than 24 hours */
function rate_limit_record(PDO $pdo, string $action, array $subjects): void {
    $stmt = $pdo->prepare('INSERT INTO rate_limit (action, subject_hash, created_at) VALUES (:action, :subject, :now)');
    foreach ($subjects as $subject) {
        $stmt->execute([':action' => $action, ':subject' => $subject, ':now' => rate_limit_time()]);
    }
    $stmt = $pdo->prepare('DELETE FROM rate_limit WHERE created_at < :old');
    $stmt->execute([':old' => rate_limit_time(RATE_LIMIT_KEEP_SECONDS)]);
}
