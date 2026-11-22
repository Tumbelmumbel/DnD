<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1'); // TEMP for debugging; remove in production

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/tinymailer.php'; // send_mail()

// Database connection via bootstrap
$pdo  = db();

$info = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$info = '';
    $errors = [];
	
	$csrf  = $_POST['csrf'] ?? '';
	$email = trim(strtolower($_POST['email'] ?? ''));
	$username = $_POST['username'] ?? '';
	$pass  = $_POST['password'] ?? '';
	$pass2 = $_POST['password2'] ?? '';

	if (!csrf_validate($csrf))                         $errors[] = 'Invalid session, please try again.';
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
	if (strlen($pass) < 8)                          $errors[] = 'Password must be at least 8 characters.';
	if ($pass !== $pass2)                           $errors[] = 'Passwords do not match.';

	if (empty($errors)) {
	// Check if email already exists
	$stmt = $pdo->prepare('SELECT id, email, email_verified_at, username FROM users WHERE email = :email or username = :username');
	$stmt->execute([
	':email' => $email,
	':username' => $username
	]);
	$existing = $stmt->fetch();
	}

	$hash    = password_hash($pass, PASSWORD_DEFAULT);
	$token   = new_token(); // from functions.php
	$tokenHash = hash('sha256', $token);
	$expires = (new DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');

	if ($existing) {
	if (strcmp($existing['username'], $username)=== 0){
		$errors[] = 'Username already taken. Please try another.';
		//exit ("Username already taken. Please try another.");
	}
	if ((strcmp(trim(strtolower($existing['email'])), $email)=== 0)) {
		$errors[] = 'This email is already registered';
		//exit ("This email is already registered and verified. Please sign in.");
	} 
	if (empty($errors)) {
		$info = 'keine Fehler gefunden' .$existing['username'] . $username . $existing['email'] . $email ;
		// Create or replace unverified user
		// Update existing unverified user
			$stmt = $pdo->prepare('
				UPDATE users
				SET password_hash = :ph,
					verification_token_hash = :th,
					verification_expires_at = :exp
				WHERE email = :email
			');
			$stmt->execute([
				':ph'   => $hash,
				':th'   => $tokenHash,
				':exp'  => $expires,
				':email'=> $email
			]);
			$userId = (int)$existing['id'];
		} 
	} else {
			// Insert new user
			$stmt = $pdo->prepare('
				INSERT INTO users (email, username, password_hash, role, verification_token_hash, verification_expires_at)
				VALUES (:email, :username, :ph, "user", :th, :exp)
			');
			$stmt->execute([
				':email'=> $email,
				':username'=> $username,
				':ph'   => $hash,
				':th'   => $tokenHash,
				':exp'  => $expires
			]);
			$userId = (int)$pdo->lastInsertId();
			
			
			// Send verification email
			$verifyUrl = BASE_URL . '/verify.php?email=' . urlencode($email) . '&token=' . urlencode($token);

			$subject = 'Verify your email';
			$html = '<p>Hello,</p>
					 <p>Please verify your email by clicking the link below:</p>
					 <p><a href="'.e($verifyUrl).'">'.e($verifyUrl).'</a></p>
					 <p>This link expires in 24 hours.</p>';
			$text = "Hello,\n\nPlease verify your email by opening this link:\n$verifyUrl\n\nThis link expires in 24 hours.";

			// Send without exposing SMTP errors to the user
			if (!send_mail($email, $subject, $html, $text)) {
				// Optional: log already done in send_mail(); provide neutral info
			}

			// Always generic message (avoid user enumeration)
			$info = 'If your email is valid, we have sent a verification link. Please check your inbox.';
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
  <input id="username" name="username" type="text" required autocomplete="username">
  <label for="password">Password</label>
  <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
  <label for="password2">Repeat password</label>
  <input id="password2" name="password2" type="password" required minlength="8" autocomplete="new-password">
  <button type="submit">Register</button>
</form>

<p>Already have an account? <a href="/login.php">Sign in</a></p>
</body>
</html>