<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/rate_limit.php';

$errors = [];
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf  = $_POST['csrf'] ?? '';
    $email = is_string($_POST['email'] ?? null) ? trim(strtolower($_POST['email'])) : '';
    $pass  = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    // Limit: 10 failed sign-ins per IP address and 5 per email address in 15 minutes
    $limitSubjects = [rate_limit_ip_hash(), rate_limit_email_hash($email)];
	
    if (!csrf_validate(is_string($csrf) ? $csrf : '')) {
        $errors[] = t('err.invalid_session');
    } elseif (rate_limit_exceeded(db(), 'login_failed', [[$limitSubjects[0], 10], [$limitSubjects[1], 5]], 15 * 60)) {
        $errors[] = t('err.too_many');
    } else {
		
		$pdo = db();

		$stmt = $pdo->prepare('SELECT id, email, username, password_hash, role, email_verified_at, session_version, language FROM users WHERE email = :email');
		$stmt->execute([':email' => $email]);
		$user = $stmt->fetch();

        $hashToCheck = $user['password_hash'] ?? password_hash('dummy', PASSWORD_DEFAULT);

        if ($user && password_verify($pass, $hashToCheck)) {
            if (empty($user['email_verified_at'])) {
                $info = t('login.verify_first');
            } else {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'             => (int)$user['id'],
                    'email'          => $user['email'],
					'username'		 => $user['username'],
                    'role'           => $user['role'],
                    'email_verified' => true,
                    'sv'             => (int)$user['session_version'],
                    't'              => time(),
                ];
                // Use the language that the user saved before
                if (i18n_is_supported($user['language'] ?? null)) {
                    set_lang($user['language']);
                }
                redirect('/characteroverview.php');
            }
        } else {
            rate_limit_record($pdo, 'login_failed', $limitSubjects);
            $errors[] = t('login.invalid');
        }
    }
}



?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
<?php $pageTitle = t('login.title'); require __DIR__ . '/partials/page_head.php'; ?>
</head>
<body>
<main class="auth-card">
  <h1><?= e(t('login.title')) ?></h1>
  <?php if (isset($_GET['m']) && $_GET['m']==='auth'): ?>
    <p class="auth-msg info"><?= e(t('login.please_sign_in')) ?></p>
  <?php endif; ?>
  <?php foreach ($errors as $err): ?>
    <p class="auth-msg err"><?= e($err) ?></p>
  <?php endforeach; ?>
  <?php if ($info): ?>
    <p class="auth-msg info"><?= e($info) ?></p>
    <form class="auth-form" method="post" action="/resend.php">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="email" value="<?= e($_POST['email'] ?? '') ?>">
      <button class="auth-button secondary" type="submit"><?= e(t('resend_link')) ?></button>
    </form>
  <?php endif; ?>
  <form class="auth-form" method="post" action="">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label for="email"><?= e(t('email')) ?></label>
    <input id="email" name="email" type="email" required autocomplete="username">
    <label for="password"><?= e(t('password')) ?></label>
    <input id="password" name="password" type="password" required autocomplete="current-password">
    <button class="auth-button" type="submit"><?= e(t('sign_in')) ?></button>
  </form>
  <a class="auth-button secondary" href="/ForgottenPW.php"><?= e(t('forgot_password')) ?></a>
  <p class="auth-links"><?= e(t('no_account')) ?> <a href="/register.php"><?= e(t('register')) ?></a></p>
</main>
<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>
