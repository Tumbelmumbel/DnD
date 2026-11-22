<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

$info = 'If your email exists and is unverified, a new verification link has been sent.';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid session.';
    } else {
        $email = trim(strtolower($_POST['email'] ?? ''));
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $pdo->prepare('SELECT id, email_verified_at FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && empty($user['email_verified_at'])) {
                $token = new_token();
                $tokenHash = hash('sha256', $token);
                $expires = (new DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');

                $stmt = $pdo->prepare('
                  UPDATE users
                  SET verification_token_hash = :th, verification_expires_at = :exp
                  WHERE id = :id
                ');
                $stmt->execute([':th'=>$tokenHash, ':exp'=>$expires, ':id'=>(int)$user['id']]);

                $verifyUrl = BASE_URL . '/verify.php?email=' . urlencode($email) . '&token=' . urlencode($token);
                $subject = 'Your verification link';
                $html = '<p>Hello,</p><p>Click to verify your email:</p><p><a href="'.e($verifyUrl).'">'.e($verifyUrl).'</a></p>';
                $text = "Verify your email:\n$verifyUrl";
                send_mail($email, $subject, $html, $text);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Verification link</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>body{font-family:system-ui,Segoe UI,Arial,sans-serif;margin:2rem;max-width:640px}.info{background:#eef5ff;color:#0a47a0;padding:.6rem;border-radius:6px}</style>
</head>
<body>
<h1>Verification</h1>
<?php if ($errors): ?>
  <div class="info"><?= e(implode(' ', $errors)) ?></div>
<?php else: ?>
  <div class="info"><?= e($info) ?></div>
<?php endif; ?>
<p><a href="/login.php">Back to sign in</a></p>
</body>
</html>