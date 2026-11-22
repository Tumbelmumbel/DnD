<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

// Database connection via bootstrap
$pdo  = db();

$email = trim(strtolower($_GET['email'] ?? ''));
$token = $_GET['token'] ?? '';
$ok = false;

if ($email && $token) {
    $stmt = $pdo->prepare('SELECT id, verification_token_hash, verification_expires_at, email_verified_at FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && empty($user['email_verified_at'])) {
        $expectedHash = $user['verification_token_hash'] ?? '';
        $expiresAt = $user['verification_expires_at'] ?? null;

        if ($expectedHash && $expiresAt && new DateTimeImmutable($expiresAt) >= new DateTimeImmutable('now')) {
            if (hash_equals($expectedHash, hash('sha256', $token))) {
                // Mark verified and clear token
                $stmt = $pdo->prepare('
                    UPDATE users
                    SET email_verified_at = NOW(), verification_token_hash = NULL, verification_expires_at = NULL
                    WHERE id = :id
                ');
                $stmt->execute([':id' => (int)$user['id']]);
                $ok = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Email verification</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>body{font-family:system-ui,Segoe UI,Arial,sans-serif;margin:2rem;max-width:640px}.ok{color:#2e7d32}.bad{color:#a40000}</style>
</head>
<body>
<h1>Email verification</h1>
<?php if ($ok): ?>
  <p class="ok">Your email has been verified. You can now <a href="/login.php">sign in</a>.</p>
<?php else: ?>
  <p class="bad">Verification failed or link expired. You can request a new link below.</p>
  <form method="post" action="/resend.php">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" required>
    <button type="submit">Resend verification link</button>
  </form>
<?php endif; ?>
<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>