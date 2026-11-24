<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

// The link contains a token: do not send it to other sites
header('Referrer-Policy: no-referrer');

$pdo = db();

/** Text value of a GET or POST field ('' if missing or not text) */
function request_text(array $source, string $key): string {
    $v = $source[$key] ?? '';
    return is_string($v) ? $v : '';
}

$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
$src    = $isPost ? $_POST : $_GET;
$email  = trim(strtolower(request_text($src, 'email')));
$token  = request_text($src, 'token');

$user   = null;  // the unverified account of a valid link
$ok     = false; // true after the password is saved
$errors = [];

// 1) Check the link: unverified account, token correct, not expired
if ($email !== '' && $token !== '') {
    $stmt = $pdo->prepare('SELECT id, username, verification_token_hash, verification_expires_at, email_verified_at FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch();

    if ($row && empty($row['email_verified_at'])) {
        $expectedHash = $row['verification_token_hash'] ?? '';
        $expiresAt    = $row['verification_expires_at'] ?? null;
        if ($expectedHash && $expiresAt
            && new DateTimeImmutable($expiresAt) >= new DateTimeImmutable('now')
            && hash_equals($expectedHash, hash('sha256', $token))) {
            $user = $row;
        }
    }
}

// 2) Form sent: set the password and mark the email as verified.
//    Opening the link (GET) changes nothing, because some mail programs open links automatically.
if ($isPost && $user) {
    $pass  = request_text($_POST, 'password');
    $pass2 = request_text($_POST, 'password2');

    if (!csrf_validate(request_text($_POST, 'csrf'))) $errors[] = t('err.invalid_session');
    if (strlen($pass) < 8)                            $errors[] = t('err.password_short');
    if ($pass !== $pass2)                             $errors[] = t('err.password_mismatch');

    if (empty($errors)) {
        $stmt = $pdo->prepare('
            UPDATE users
            SET password_hash = :ph,
                email_verified_at = NOW(),
                verification_token_hash = NULL,
                verification_expires_at = NULL,
                session_version = session_version + 1
            WHERE id = :id AND email_verified_at IS NULL
        ');
        $stmt->execute([':ph' => password_hash($pass, PASSWORD_DEFAULT), ':id' => (int)$user['id']]);
        $ok = ($stmt->rowCount() === 1);
        if (!$ok) {
            $user = null; // verified in the meantime: show the normal error
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
<?php $pageTitle = t('verify.title'); require __DIR__ . '/partials/page_head.php'; ?>
</head>
<body>
<main class="auth-card">
  <h1><?= e(t('verify.title')) ?></h1>
  <?php if ($ok): ?>
    <p class="auth-msg ok"><?= e(t('verify.ok')) ?></p>
    <a class="auth-button" href="/login.php"><?= e(t('sign_in')) ?></a>
  <?php elseif ($user): ?>
    <h2><?= e(t('verify.set_password')) ?></h2>
    <p><?= e(t('verify.account')) ?> <strong><?= e($user['username']) ?></strong></p>
    <?php foreach ($errors as $err): ?>
      <p class="auth-msg err"><?= e($err) ?></p>
    <?php endforeach; ?>
    <form class="auth-form" method="post" action="/verify.php">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="email" value="<?= e($email) ?>">
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <label for="password"><?= e(t('password')) ?></label>
      <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
      <label for="password2"><?= e(t('repeat_password')) ?></label>
      <input id="password2" name="password2" type="password" required minlength="8" autocomplete="new-password">
      <button class="auth-button" type="submit"><?= e(t('verify.submit')) ?></button>
    </form>
  <?php else: ?>
    <p class="auth-msg err"><?= e(t('verify.failed')) ?></p>
    <form class="auth-form" method="post" action="/resend.php">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label for="email"><?= e(t('email')) ?></label>
      <input id="email" name="email" type="email" required>
      <button class="auth-button" type="submit"><?= e(t('resend_link')) ?></button>
    </form>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>
