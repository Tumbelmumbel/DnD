<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/tinymailer.php';

// Database connection via bootstrap
$pdo  = db();

$errors = [];
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf  = $_POST['csrf'] ?? '';
	$email = trim(strtolower($_POST['email'] ?? ''));
	
    if (!csrf_validate($csrf)) {
        $errors[] = 'Invalid session. Please try again.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Store ONLY the token hash. The current password stays valid
            // until the user sets a new one with the link (reset.php).
            $token     = new_token();
            $tokenHash = hash('sha256', $token);
            $expires   = (new DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare('UPDATE users SET PWreset_token_hash = :th, PWreset_expires_at = :exp WHERE id = :id');
            $stmt->execute([
                ':th'  => $tokenHash,
                ':exp' => $expires,
                ':id'  => (int)$user['id'],
            ]);

            $resetUrl = BASE_URL . '/reset.php?email=' . urlencode($email) . '&token=' . urlencode($token);
            $subject  = 'Reset your password';
            $html = '<p>Hello,</p>
                     <p>Please reset your password with the link below:</p>
                     <p><a href="'.e($resetUrl).'">'.e($resetUrl).'</a></p>
                     <p>This link expires in 24 hours. If you did not ask for this, ignore this email. Your password does not change.</p>';
            $text = "Hello,\n\nPlease reset your password with the link below:\n$resetUrl\n\nThis link expires in 24 hours. If you did not ask for this, ignore this email. Your password does not change.";

            // send_mail() logs SMTP errors itself; the user sees no difference
            send_mail($email, $subject, $html, $text);
        }

        // Always the same message, known email or not (avoid user enumeration)
        $info = 'If this email is registered, we have sent a reset link. Please check your inbox.';
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Reset password</title>
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
<h1>Forgot Password</h1>
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
  <button type="submit">Reset</button>
</form>

</body>
</html>