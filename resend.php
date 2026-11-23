<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/tinymailer.php'; // send_mail()

$info = t('resend.info');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';
    if (!csrf_validate($csrf)) {
        $errors[] = t('err.invalid_session');
    } else {
        $email = is_string($_POST['email'] ?? null) ? trim(strtolower($_POST['email'])) : '';
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $pdo = db();
            delete_expired_unverified_users($pdo); // an expired registration gets no new link
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
                $subject = t('mail.verify.subject');
                $html = '<p>'.e(t('mail.hello')).'</p><p>'.e(t('mail.verify.body')).'</p><p><a href="'.e($verifyUrl).'">'.e($verifyUrl).'</a></p><p>'.e(t('mail.link_expires')).'</p>';
                $text = t('mail.hello') . "\n\n" . t('mail.verify.body') . "\n" . $verifyUrl . "\n\n" . t('mail.link_expires');
                send_mail($email, $subject, $html, $text);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
<meta charset="utf-8">
<title><?= e(t('resend.title')) ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>body{font-family:system-ui,Segoe UI,Arial,sans-serif;margin:2rem;max-width:640px}.info{background:#eef5ff;color:#0a47a0;padding:.6rem;border-radius:6px}</style>
</head>
<body>
<h1><?= e(t('resend.title')) ?></h1>
<?php if ($errors): ?>
  <div class="info"><?= e(implode(' ', $errors)) ?></div>
<?php else: ?>
  <div class="info"><?= e($info) ?></div>
<?php endif; ?>
<p><a href="/login.php"><?= e(t('back_to_sign_in')) ?></a></p>
<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>