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
<meta charset="utf-8">
<title><?= e(t('reset.title')) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:system-ui,Segoe UI,Arial,sans-serif;margin:2rem;max-width:640px}
label{display:block;margin:.5rem 0 .25rem}
input{width:100%;padding:.6rem;border:1px solid #ccc;border-radius:6px}
button{margin-top:1rem;padding:.6rem 1rem;background:#0a66c2;color:#fff;border:0;border-radius:6px;cursor:pointer}
.alert{padding:.6rem;border-radius:6px;margin:.6rem 0}
.err{background:#ffecec;color:#a40000}
.info{background:#eef5ff;color:#0a47a0}
</style>
</head>
<body>
<h1><?= e(t('reset.title')) ?></h1>

<?php foreach ($errors as $err): ?>
  <div class="alert err"><?= e($err) ?></div>
<?php endforeach; ?>

<?php if ($info): ?>
  <div class="alert info"><?= e($info) ?></div>
<?php endif; ?>

<form method="post" action="">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label for="password"><?= e(t('password')) ?></label>
  <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
  <label for="password2"><?= e(t('repeat_password')) ?></label>
  <input id="password2" name="password2" type="password" required minlength="8" autocomplete="new-password">
  <button type="submit"><?= e(t('reset_button')) ?></button>
</form>

<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>