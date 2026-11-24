<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$email = trim(strtolower($_GET['email'] ?? ''));
$token = $_GET['token'] ?? '';
$ok = false;

// Database connection via bootstrap
$pdo  = db();

$errors = [];
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$csrf  = $_POST['csrf'] ?? '';
	$pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';
	
	if (!csrf_validate($csrf))                         $errors[] = t('err.invalid_session');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = t('reset.link_invalid');
    if (strlen($pass) < 8)                          $errors[] = t('err.password_short');
    if ($pass !== $pass2)                           $errors[] = t('err.password_mismatch');
	
	if (empty($errors)) {
		if ($email && $token) {
			$stmt = $pdo->prepare('SELECT id, PWreset_token_hash, PWreset_expires_at FROM users WHERE email = :email');
			$stmt->execute([':email' => $email]);
			$user = $stmt->fetch();

			if ($user && !empty($user['PWreset_token_hash'])) {
				$expectedHash = $user['PWreset_token_hash'] ?? '';
				$expiresAt = $user['PWreset_expires_at'] ?? null;
				$pwhash  = password_hash($pass, PASSWORD_DEFAULT);
				
				if ($expectedHash && $expiresAt && new DateTimeImmutable($expiresAt) >= new DateTimeImmutable('now')) {
					if (hash_equals($expectedHash, hash('sha256', $token))) {
						// Change PW, clear token, end all existing sessions of this user
						$stmt = $pdo->prepare('
							UPDATE users
							SET password_hash = :pwh, PWreset_token_hash = NULL, PWreset_expires_at = NULL,
							    session_version = session_version + 1  -- logs out all existing sessions
							WHERE id = :id
						');
						$stmt->execute([
						':id' => (int)$user['id'],
						':pwh' => $pwhash
						]);
						$info = t('reset.done');
					} else {
						$errors[] = t('reset.link_invalid');
					}
				} else {
					$errors[] = t('reset.link_invalid');
				}
			} else {
				$errors[] = t('reset.link_invalid');
			}
		} else {
			$errors[] = t('reset.link_invalid');
		}
	}
}
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
<?php $pageTitle = t('reset.title'); require __DIR__ . '/partials/page_head.php'; ?>
</head>
<body>
<main class="auth-card">
  <h1><?= e(t('reset.title')) ?></h1>
  <?php foreach ($errors as $err): ?>
    <p class="auth-msg err"><?= e($err) ?></p>
  <?php endforeach; ?>
  <?php if ($info): ?>
    <p class="auth-msg info"><?= e($info) ?></p>
  <?php endif; ?>
  <form class="auth-form" method="post" action="">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label for="password"><?= e(t('password')) ?></label>
    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
    <label for="password2"><?= e(t('repeat_password')) ?></label>
    <input id="password2" name="password2" type="password" required minlength="8" autocomplete="new-password">
    <button class="auth-button" type="submit"><?= e(t('reset_button')) ?></button>
  </form>
  <p class="auth-links"><a href="/login.php"><?= e(t('back_to_sign_in')) ?></a></p>
</main>
<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>
