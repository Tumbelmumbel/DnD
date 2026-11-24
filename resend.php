<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/tinymailer.php'; // send_mail()
require_once __DIR__ . '/rate_limit.php';

$info = t('resend.info');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';
    if (!csrf_validate($csrf)) {
        $errors[] = t('err.invalid_session');
    } else {
        $email = is_string($_POST['email'] ?? null) ? trim(strtolower($_POST['email'])) : '';
        // Limit: 5 requests per IP address and 3 per email address in one hour
        $pdo = db();
        $limitSubjects = [rate_limit_ip_hash(), rate_limit_email_hash($email)];
        if (rate_limit_exceeded($pdo, 'resend', [[$limitSubjects[0], 5], [$limitSubjects[1], 3]], 60 * 60)) {
            $errors[] = t('err.too_many');
        } elseif ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            rate_limit_record($pdo, 'resend', $limitSubjects);
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
<?php $pageTitle = t('resend.title'); require __DIR__ . '/partials/page_head.php'; ?>
</head>
<body>
<main class="auth-card">
  <h1><?= e(t('resend.title')) ?></h1>
  <?php if ($errors): ?>
    <p class="auth-msg info"><?= e(implode(' ', $errors)) ?></p>
  <?php else: ?>
    <p class="auth-msg info"><?= e($info) ?></p>
  <?php endif; ?>
  <p class="auth-links"><a href="/login.php"><?= e(t('back_to_sign_in')) ?></a></p>
</main>
<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>
