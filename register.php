<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/tinymailer.php'; // send_mail()

$info = '';
$errors = [];

/** Text value of a POST field ('' if missing or not text) */
function post_text(string $key): string {
    $v = $_POST[$key] ?? '';
    return is_string($v) ? $v : '';
}

/** Send the verification link to $email */
function send_verification_mail(string $email, string $token): void {
    $verifyUrl = BASE_URL . '/verify.php?email=' . urlencode($email) . '&token=' . urlencode($token);
    $subject = t('mail.verify.subject');
    $html = '<p>'.e(t('mail.hello')).'</p>
             <p>'.e(t('mail.verify.body')).'</p>
             <p><a href="'.e($verifyUrl).'">'.e($verifyUrl).'</a></p>
             <p>'.e(t('mail.link_expires')).'</p>';
    $text = t('mail.hello') . "\n\n" . t('mail.verify.body') . "\n" . $verifyUrl . "\n\n" . t('mail.link_expires');
    send_mail($email, $subject, $html, $text); // logs SMTP errors itself
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf     = post_text('csrf');
    $email    = trim(strtolower(post_text('email')));
    $username = trim(post_text('username'));
    $pass     = post_text('password');
    $pass2    = post_text('password2');

    // 1) Validate the form. Stop here if anything is wrong.
    if (!csrf_validate($csrf))                      $errors[] = t('err.invalid_session');
    if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = t('err.invalid_email');
    }
    if (!preg_match('/^[A-Za-z0-9_.\-]{3,40}$/D', $username)) {
        $errors[] = t('register.username_rule');
    }
    if (strlen($pass) < 8)                          $errors[] = t('err.password_short');
    if ($pass !== $pass2)                           $errors[] = t('err.password_mismatch');

    // 2) Username check: the result does not depend on the email
    if (empty($errors)) {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        if ($stmt->fetchColumn()) {
            $errors[] = t('register.username_taken');
        }
    }

    // 3) Handle the email. Same message and same work in every case.
    if (empty($errors)) {
        $info = t('register.sent');

        $hash      = password_hash($pass, PASSWORD_DEFAULT); // always, so that all cases take the same time
        $token     = new_token();
        $tokenHash = hash('sha256', $token);
        $expires   = (new DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare('SELECT id, email_verified_at FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $existing = $stmt->fetch();

        if ($existing && !empty($existing['email_verified_at'])) {
            // Verified account exists: change nothing, tell only the mailbox owner
            $loginUrl = BASE_URL . '/login.php';
            $resetUrl = BASE_URL . '/ForgottenPW.php';
            $html = '<p>'.e(t('mail.hello')).'</p>
                     <p>'.e(t('mail.exists.body')).'</p>
                     <p>'.e(t('mail.exists.sign_in')).' <a href="'.e($loginUrl).'">'.e($loginUrl).'</a></p>
                     <p>'.e(t('mail.exists.forgot')).' <a href="'.e($resetUrl).'">'.e($resetUrl).'</a></p>
                     <p>'.e(t('mail.exists.ignore')).'</p>';
            $text = t('mail.hello') . "\n\n" . t('mail.exists.body') . "\n\n"
                  . t('mail.exists.sign_in') . ' ' . $loginUrl . "\n" . t('mail.exists.forgot') . ' ' . $resetUrl . "\n\n"
                  . t('mail.exists.ignore');
            send_mail($email, t('mail.exists.subject'), $html, $text);

        } elseif ($existing) {
            // Unverified account exists: keep its password and username, send a new link only
            $stmt = $pdo->prepare('
                UPDATE users
                SET verification_token_hash = :th, verification_expires_at = :exp
                WHERE id = :id
            ');
            $stmt->execute([':th' => $tokenHash, ':exp' => $expires, ':id' => (int)$existing['id']]);
            send_verification_mail($email, $token);

        } else {
            // New account
            try {
                $stmt = $pdo->prepare('
                    INSERT INTO users (email, username, password_hash, role, verification_token_hash, verification_expires_at)
                    VALUES (:email, :username, :ph, \'user\', :th, :exp)
                ');
                $stmt->execute([
                    ':email'    => $email,
                    ':username' => $username,
                    ':ph'       => $hash,
                    ':th'       => $tokenHash,
                    ':exp'      => $expires,
                ]);
                send_verification_mail($email, $token);
            } catch (PDOException $ex) {
                if ($ex->getCode() !== '23000') {
                    throw $ex;
                }
                // Another registration with the same email or username came first
                $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = :username');
                $stmt->execute([':username' => $username]);
                if ($stmt->fetchColumn()) {
                    $info = '';
                    $errors[] = t('register.username_taken');
                }
                // Same email: keep the neutral message
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
<meta charset="utf-8">
<title><?= e(t('register.title')) ?></title>
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
<h1><?= e(t('register.title')) ?></h1>

<?php foreach ($errors as $err): ?>
  <div class="alert err"><?= e($err) ?></div>
<?php endforeach; ?>

<?php if ($info): ?>
  <div class="alert info"><?= e($info) ?></div>
<?php endif; ?>

<form method="post" action="">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label for="email"><?= e(t('email')) ?></label>
  <input id="email" name="email" type="email" required autocomplete="email">
  <label for="username"><?= e(t('username')) ?></label>
  <input id="username" name="username" type="text" required minlength="3" maxlength="40" pattern="[A-Za-z0-9_.\-]{3,40}" title="<?= e(t('register.username_hint')) ?>" autocomplete="username">
  <label for="password"><?= e(t('password')) ?></label>
  <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
  <label for="password2"><?= e(t('repeat_password')) ?></label>
  <input id="password2" name="password2" type="password" required minlength="8" autocomplete="new-password">
  <button type="submit"><?= e(t('register')) ?></button>
</form>

<p><?= e(t('register.have_account')) ?> <a href="/login.php"><?= e(t('sign_in')) ?></a></p>
<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>