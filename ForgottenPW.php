<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/tinymailer.php';
require_once __DIR__ . '/rate_limit.php';

// Database connection via bootstrap
$pdo  = db();

$errors = [];
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf  = $_POST['csrf'] ?? '';
	$email = is_string($_POST['email'] ?? null) ? trim(strtolower($_POST['email'])) : '';
    // Limit: 5 requests per IP address and 3 per email address in one hour
    $limitSubjects = [rate_limit_ip_hash(), rate_limit_email_hash($email)];
	
    if (!csrf_validate(is_string($csrf) ? $csrf : '')) {
        $errors[] = t('err.invalid_session');
    } elseif (rate_limit_exceeded($pdo, 'forgot', [[$limitSubjects[0], 5], [$limitSubjects[1], 3]], 60 * 60)) {
        $errors[] = t('err.too_many');
    } else {
        rate_limit_record($pdo, 'forgot', $limitSubjects);
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
            $subject  = t('mail.reset.subject');
            $html = '<p>'.e(t('mail.hello')).'</p>
                     <p>'.e(t('mail.reset.body')).'</p>
                     <p><a href="'.e($resetUrl).'">'.e($resetUrl).'</a></p>
                     <p>'.e(t('mail.link_expires')).' '.e(t('mail.reset.ignore')).'</p>';
            $text = t('mail.hello') . "\n\n" . t('mail.reset.body') . "\n" . $resetUrl . "\n\n"
                  . t('mail.link_expires') . ' ' . t('mail.reset.ignore');

            // send_mail() logs SMTP errors itself; the user sees no difference
            send_mail($email, $subject, $html, $text);
        }

        // Always the same message, known email or not (avoid user enumeration)
        $info = t('forgot.sent');
	}
}
?>

<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
<meta charset="utf-8">
<title><?= e(t('forgot.title')) ?></title>
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
<h1><?= e(t('forgot.title')) ?></h1>
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
  <button type="submit"><?= e(t('reset_button')) ?></button>
</form>

<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>