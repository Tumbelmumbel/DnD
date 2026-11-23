<?php
declare(strict_types=1);

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Same check as csrf_validate() in bootstrap.php (same session key) */
function csrf_check(string $token): bool {
    return csrf_validate($token);
}

function require_login(): void {
    if (empty($_SESSION['user'])) {
        redirect('/login.php?m=auth');
    }
}

/** Ensure the logged-in user has verified their email */
function require_verified(): void {
    require_login();
    if (empty($_SESSION['user']['email_verified'])) {
        redirect('/login.php?m=verify');
    }
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

/** Create a random password, when user sends reset request*/
function generateRandomPassword($length): string {
    // Allowed characters: upper, lower, digits, and symbols
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_=+[]{};:,.<>?';
    $charactersLength = strlen($characters);

    if ($length < 4) {
        throw new InvalidArgumentException("Password length must be at least 4 characters.");
    }

    $password = '';
    try {
        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $charactersLength - 1); // Secure random index
            $password .= $characters[$index];
        }
    } catch (Exception $e) {
        // random_int() failed (very rare)
        die("Error generating password: " . $e->getMessage());
    }

    return $password;
}
