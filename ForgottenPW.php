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
       $stmt = $pdo->prepare('SELECT email FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
		
		if (empty($user)) {
			$errors[] = 'No mail registered';
		}
			else {
				$token   = new_token(); // from functions.php
				$tokenHash = hash('sha256', $token);
				$expires = (new DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');
				$pass = generateRandomPassword(12);
				$pwhash  = password_hash($pass, PASSWORD_DEFAULT);
				$stmt = $pdo->prepare('UPDATE users SET PWreset_token_hash = :th, PWreset_expires_at = :exp, password_hash = :pwh WHERE email = :email');
				$stmt->execute([
				':th' => $tokenHash, 
				':exp' => $expires, 
				':pwh' => $pwhash,
				':email' => $email
				]);
				
				// Send verification email
				$verifyUrl = BASE_URL . '/reset.php?email=' . urlencode($email) . '&token=' . urlencode($token);
				$subject = 'Change your PW';
				$html = '<p>Hello,</p>
						 <p>Please reset your password following the link below:</p>
						 <p><a href="'.e($verifyUrl).'">'.e($verifyUrl).'</a></p>';
				$text = "Hello,\n\nPlease reset your password following the link below:\n$verifyUrl\n\nThis link expires in 24 hours.";

				// Send without exposing SMTP errors to the user
				if (!send_mail($email, $subject, $html, $text)) {
					// Optional: log already done in send_mail(); provide neutral info
				}

				// Always generic message (avoid user enumeration)
				$info = 'If your email is valid, we have sent a verification link. Please check your inbox.';
			}
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