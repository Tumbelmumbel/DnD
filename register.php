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
    $subject = 'Verify your email';
    $html = '<p>Hello,</p>
             <p>Please verify your email by clicking the link below:</p>
             <p><a href="'.e($verifyUrl).'">'.e($verifyUrl).'</a></p>
             <p>This link expires in 24 hours.</p>';
    $text = "Hello,\n\nPlease verify your email by opening this link:\n$verifyUrl\n\nThis link expires in 24 hours.";
    send_mail($email, $subject, $html, $text); // logs SMTP errors itself
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf     = post_text('csrf');
    $email    = trim(strtolower(post_text('email')));
    $username = trim(post_text('username'));
    $pass     = post_text('password');
    $pass2    = post_text('password2');

    // 1) Validate the form. Stop here if anything is wrong.
    if (!csrf_validate($csrf))                      $errors[] = 'Invalid session, please try again.';
    if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email.';
    }
    if (!preg_match('/^[A-Za-z0-9_.\-]{3,40}$/D', $username)) {
        $errors[] = 'Username must be 3 to 40 characters: A-Z, a-z, 0-9, ".", "_" or "-".';
    }
    if (strlen($pass) < 8)                          $errors[] = 'Password must be at least 8 characters.';
    if ($pass !== $pass2)                           $errors[] = 'Passwords do not match.';

    // 2) Username check: the result does not depend on the email
    if (empty($errors)) {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        if ($stmt->fetchColumn()) {
            $errors[] = 'Username already taken. Please try another.';
        }
    }

    // 3) Handle the email. Same message and same work in every case.
    if (empty($errors)) {
        $info = 'If your email is valid, we have sent a verification link. Please check your inbox.';

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
            $html = '<p>Hello,</p>
                     <p>Somebody tried to register with this email address. You already have an account.</p>
                     <p>Sign in here: <a href="'.e($loginUrl).'">'.e($loginUrl).'</a></p>
                     <p>If you do not know your password: <a href="'.e($resetUrl).'">'.e($resetUrl).'</a></p>
                     <p>If this was not you, you can ignore this email. Your account does not change.</p>';
            $text = "Hello,\n\nSomebody tried to register with this email address. You already have an account.\n\nSign in: $loginUrl\nForgot password: $resetUrl\n\nIf this was not you, you can ignore this email. Your account does not change.";
            send_mail($email, 'You already have an account', $html, $text);

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
                    $errors[] = 'Username already taken. Please try another.';
                }
                // Same email: keep the neutral message
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Register</title>
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
<h1>Create account</h1>

<?php foreach ($errors as $err): ?>
  <div class="alert err"><?= e($err) ?></div>
<?php endforeach; ?>

<?php if ($info): ?>
  <div class="alert info"><?= e($info) ?></div>
<?php endif; ?>

<form method="post" action="">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label for="email">Email</label>
  <input id="email" name="email" type="email" required autocomplete="email">
  <label for="username">Username</label>
  <input id="username" name="username" type="text" required minlength="3" maxlength="40" pattern="[A-Za-z0-9_.\-]{3,40}" title="3 to 40 characters: A-Z, a-z, 0-9, . _ -" autocomplete="username">
  <label for="password">Password</label>
  <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
  <label for="password2">Repeat password</label>
  <input id="password2" name="password2" type="password" required minlength="8" autocomplete="new-password">
  <button type="submit">Register</button>
</form>

<p>Already have an account? /login.phpSign in</a></p>
</body>
</html>