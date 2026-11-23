<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/tinymailer.php'; // send_mail()

$info = '';
$errors = [];
$formEmail = '';    // shown again in the form after an error
$formUsername = '';

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
    $formEmail    = trim(post_text('email'));
    $formUsername = $username;

    // 1) Validate the form. Stop here if anything is wrong.
    if (!csrf_validate($csrf))                      $errors[] = t('err.invalid_session');
    if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = t('err.invalid_email');
    }
    if (!preg_match('/^[A-Za-z0-9_.\-]{3,40}$/D', $username)) {
        $errors[] = t('register.username_rule');
    }

    // 2) Username check: the result does not depend on the email
    if (empty($errors)) {
        $pdo = db();
        delete_expired_unverified_users($pdo); // frees the email and username of expired registrations
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        if ($stmt->fetchColumn()) {
            $errors[] = t('register.username_taken');
        }
    }

    // 3) Handle the email. Same message and same work in every case.
    if (empty($errors)) {
        $info = t('register.sent');

        // A random password that nobody knows. The user sets the real password with the link in the mail (verify.php).
        // Hash it in every case, so that all cases take the same time.
        $hash      = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
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
            // Unverified account exists: keep its username, send a new link only
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
  <input id="email" name="email" type="email" required autocomplete="email" value="<?= e($errors ? $formEmail : '') ?>">
  <label for="username"><?= e(t('username')) ?></label>
  <input id="username" name="username" type="text" value="<?= e($errors ? $formUsername : '') ?>" required minlength="3" maxlength="40" pattern="[A-Za-z0-9_.\-]{3,40}" title="<?= e(t('register.username_hint')) ?>" autocomplete="username">
  <button type="submit"><?= e(t('register')) ?></button>
</form>

<p><?= e(t('register.have_account')) ?> <a href="/login.php"><?= e(t('sign_in')) ?></a></p>
<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>