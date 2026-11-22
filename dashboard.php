<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
guard_protect();

$pdo  = db();
$user = current_user();


require_verified();
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Dashboard</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>body{font-family:system-ui,Segoe UI,Arial,sans-serif;margin:2rem}</style>
</head>
<body>
<h1>Welcome, <?= e($user['email']) ?> 🎉</h1>
<p>Your role: <strong><?= e($user['role']) ?></strong></p>

<p><a href="/logout.php">Log out</a></p>
<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>
