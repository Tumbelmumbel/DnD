<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
guard_protect();

// Database connection via bootstrap
$pdo  = db();

$user = current_user();
$info = '';

// Decide what identifies the character owner
$username = $user['username'];




// Fetch characters for this user
$sql = "SELECT Name, image_url FROM Characters WHERE username = :u ORDER BY Name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([":u" => $username]);
$characters = $stmt->fetchAll();

if (!$characters) {
	$info = t('overview.no_characters');
}


?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/Assets/style.css">
    <title><?= e(t('overview.title')) ?></title>

    <style>
		
		/* Modal background */
		.modal {
			display: none; /* hidden by default */
			position: fixed;
			inset: 0;
			background: rgba(0,0,0,0.7);
			z-index: 9999;
			justify-content: center;
			align-items: center;
		}

		/* Modal box */
		.modal-content {
			background: var(--bg-card);
			padding: 25px;
			border-radius: 10px;
			width: 90%;
			max-width: 360px;
			text-align: center;
			color: white;
			box-shadow: 0 8px 20px rgba(0,0,0,0.5);
		}

		/* Buttons container */
		.modal-buttons {
			margin-top: 20px;
			display: flex;
			justify-content: space-between;
		}

    </style>
</head>
<body>

<!-- Sticky header -->
 <div class="container_vertical">
	<div class="container_horizontal">
		<form class = "button-form" method="get">
			<button class="button" type = "button" onclick="openCreateModal()"><?= e(t('overview.create')) ?></button>
		</form>	
			<div class="page-header">
				<h1><?= e(t('overview.title')) ?></h1>
			</div>
	    	<form class = "button-form" method="get" action="/logout.php">
				<button class="button" type="submit"><?= e(t('log_out')) ?></button>
			</form>			

	</div>
		<?php if ($info): ?>
		  <div class="info"><?= e($info) ?></div>
		<?php endif; ?>

		<!-- Tiles -->
		<div class="grid">
		<?php foreach ($characters as $char): ?>
			<?php
				$name = e($char['Name']);
				$img  = !empty($char['image_url']) ? e($char['image_url']) : null;
			?>
			<div class="tile">
				<?php if ($img): ?>
					<div class="bg" style="background-image:url('<?= $img ?>');"></div>
				<?php endif; ?>
				<div class="overlay"></div>
				<div class="name"><?= $name ?></div>
			</div>
		<?php endforeach; ?>
		</div>

		<div id="create-modal" class="modal">
			<div class="modal-content">
				<h2><?= e(t('overview.modal_title')) ?></h2>
				<p><?= e(t('overview.sandbox_text')) ?></p>
				<p><?= e(t('overview.railroad_text')) ?></p>
				<div class="container_horizontal_relative">
					<form class = "button-form" method="get" action="/CharacterSandbox.php">
						<button class="button" type="submit"><?= e(t('overview.sandbox')) ?></button>
					</form>	
					<div class="button-form">
						<button class="button" type="submit" onclick="closeCreateModal()"><?= e(t('overview.railroad')) ?></button>
					</div>
				</div>
			</div>
		</div>

</div>



<script>
function openCreateModal() {
    document.getElementById("create-modal").style.display = "flex";
}

function closeCreateModal() {
    document.getElementById("create-modal").style.display = "none";
}
</script>



<?php require __DIR__ . '/partials/language_footer.php'; ?>
</body>
</html>
