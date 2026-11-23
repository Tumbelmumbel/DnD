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
<meta charset="utf-8">
<title><?= e(t('login.title')) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/Assets/style.css">
<style>
form {
    display: flex;
    flex-direction: column;
    align-items: stretch; /* change to stretch if you prefer full-width inputs */
    gap: 0.2rem;
}
</style>
</head>

<body>
 <div class="container_vertical">
	<h1><?= e(t('login.title')) ?></h1>
	<?php if (isset($_GET['m']) && $_GET['m']==='auth'): ?>
	  <div class="alert"><?= e(t('login.please_sign_in')) ?></div>
	<?php endif; ?>

	<?php foreach ($errors as $err): ?>
	  <div class="err"><?= e($err) ?></div>
	<?php endforeach; ?>

	<?php if ($info): ?>
	  <div class="alert"><?= e($info) ?></div>
	  <form method="post" action="/resend.php">
		<input class= label type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
		<input class= label type="hidden" name="email" value="<?= e($_POST['email'] ?? '') ?>">
		<button class= button type="submit"><?= e(t('resend_link')) ?></button>
	  </form>
	<?php endif; ?>

	<form method="post" action="">
	  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
	  <label class= label for="email"><?= e(t('email')) ?></label>
	  <input class= input id="email" name="email" type="email" required autocomplete="username">
	  <label class= label for="password"><?= e(t('password')) ?></label>
	  <input class= input id="password" name="password" type="password" required autocomplete="current-password">
	  <button class= button type="submit"><?= e(t('sign_in')) ?></button>
	</form>

	<form method="get" action="/ForgottenPW.php">
		<button class= button type="submit"><?= e(t('forgot_password')) ?></button>
	</form>	

	<p><?= e(t('no_account')) ?></p>
	<form method="get" action="/register.php">
		<button class= button type="submit"><?= e(t('register')) ?></button>
	</form>	
</div>
<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>