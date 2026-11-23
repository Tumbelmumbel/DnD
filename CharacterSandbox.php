<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/calculation.php';
require __DIR__ . '/content_translation.php';
guard_protect();

// Database connection via bootstrap
$pdo  = db();

$user = current_user();
$info = '';

// Decide what identifies the character owner
$username = $user['username'];
$abilites = [];
$errors = [];

		$subclasses[]=NULL;

		// Lookup values: "code" identifies the value (never changes).
		// The names come from Lookup_translation in the current language (content_translation.php).
		$stmt = $pdo->prepare('SELECT DISTINCT class_code FROM Classes');
		$stmt->execute();
		$classes = lookup_list($pdo, $stmt->fetchAll(PDO::FETCH_COLUMN), 'class');

		$stmt = $pdo->prepare('SELECT code FROM Species');
		$stmt->execute();
		$species = lookup_list($pdo, $stmt->fetchAll(PDO::FETCH_COLUMN), 'species');

		// Abilities and their skills (codes; the names come from lang/*.php)
		$stmt = $pdo->prepare('SELECT code FROM Abilities ORDER BY sort_order');
		$stmt->execute();
		$abilities = array_fill_keys($stmt->fetchAll(PDO::FETCH_COLUMN), []);

		$stmt = $pdo->prepare('SELECT code, ability_code FROM Skills ORDER BY sort_order');
		$stmt->execute();
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
			if (isset($abilities[$row['ability_code']])) {
				$abilities[$row['ability_code']][] = $row['code'];
			}
		}

		$stmt = $pdo->prepare('SELECT code FROM Backgrounds');
		$stmt->execute();
		$backgrounds = lookup_list($pdo, $stmt->fetchAll(PDO::FETCH_COLUMN), 'background');

		$stmt = $pdo->prepare('SELECT code FROM Damage_influence');
		$stmt->execute();
		$damageInfluences = lookup_list($pdo, $stmt->fetchAll(PDO::FETCH_COLUMN), 'damage_influence');

		$stmt = $pdo->prepare('SELECT code FROM Damagetypes');
		$stmt->execute();
		$damageTypes = lookup_list($pdo, $stmt->fetchAll(PDO::FETCH_COLUMN), 'damagetype');

		$stmt = $pdo->prepare('SELECT code FROM Conditions');
		$stmt->execute();
		$conditions = lookup_list($pdo, $stmt->fetchAll(PDO::FETCH_COLUMN), 'condition');

		// Talents for the talent menu (only id and name; details come from the endpoint)
		$talents = talent_list($pdo);
?>

<html lang="<?= e(current_lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/Assets/style.css">
    <title data-i18n = "sandbox.title"><?= e(t('sandbox.title')) ?></title>
<style>
/* ===== Character sheet: tablet-first layout (page-only rules, prefix cs-) ===== */
.cs-page { --cs-card: #F3EDDA; --cs-field: #FFFDF6; --cs-line: #D8CDA8; --cs-border: #A99E78; --cs-accent: #3B4BC4;
	max-width: 1240px; margin: 0 auto; padding: 16px; display: flex; flex-direction: column; gap: 16px;
	color: var(--text); font-family: var(--fonts); font-size: 16px; text-align: left; }
.cs-card { background: var(--cs-card); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; gap: 10px;
	box-shadow: 0 1px 2px rgba(74, 68, 49, 0.15); min-width: 0; }
.cs-card h2 { margin: 0; font-size: 18px; }
/* Picture of the character (top center) and the larger view */
.cs-picture { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.cs-picture-frame { width: 160px; height: 160px; border-radius: 50%; padding: 0; overflow: hidden; cursor: pointer;
	border: 3px solid var(--cs-border); background: var(--cs-field); color: var(--cs-border);
	display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; font: inherit; }
.cs-picture-frame:focus-visible { outline: 3px solid var(--cs-accent); outline-offset: 3px; }
.cs-picture-frame img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cs-picture [hidden] { display: none; }
.cs-picture-plus { font-size: 44px; line-height: 1; font-weight: 700; }
.cs-picture-text { font-size: 14px; font-weight: 700; color: var(--text); }
.cs-picture-error { min-height: 1.2em; margin: 0; color: #A40000; font-size: 14px; text-align: center; }
.cs-picture-dialog { border: 0; border-radius: 12px; padding: 16px; max-width: min(92vw, 900px); background: #F3EDDA; }
.cs-picture-dialog::backdrop { background: rgba(0, 0, 0, 0.7); }
.cs-picture-dialog img { display: block; max-width: 100%; max-height: 72vh; margin: 0 auto; border-radius: 8px; }
.cs-picture-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 14px; }
.cs-picture-actions button { min-height: 48px; padding: 0 18px; border-radius: 24px; border: 1px solid var(--cs-border, #A99E78);
	background: #FFFDF6; font: inherit; font-weight: 700; cursor: pointer; }
.cs-picture-actions .cs-picture-remove { color: #A40000; }
.cs-card-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.cs-field { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.cs-field > label, .cs-mini { font-size: 13px; font-weight: 700; }
.cs-page input:not([type=checkbox]), .cs-page select { box-sizing: border-box; width: 100%; min-width: 0; min-height: 48px;
	padding: 0 10px; border: 1px solid var(--cs-border); border-radius: 8px; background: var(--cs-field);
	color: var(--text); font: inherit; font-size: 16px; }
.cs-page input[type=checkbox] { width: 24px; height: 24px; margin: 0; flex: 0 0 24px; accent-color: var(--cs-accent); }
.cs-name input { font-size: 20px !important; font-weight: 700; }

/* Grids */
.cs-identity { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.cs-identity .cs-name { grid-column: 1 / -1; }
.cs-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; }
.cs-stat { align-items: center; text-align: center; }
.cs-stat .cs-mini { text-transform: uppercase; letter-spacing: 0.04em; }
.cs-stat input, .cs-stat select { text-align: center; font-size: 20px !important; font-weight: 700; }
.cs-value { min-height: 48px; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; }
.cs-value:empty::before { content: "–"; opacity: 0.5; }
.cs-two { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; align-items: start; }
.cs-hp { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
.cs-hp input { text-align: center; font-size: 20px !important; }
.cs-row { display: flex; align-items: center; gap: 10px; border-top: 1px solid var(--cs-line); padding-top: 10px; }
.cs-row input { width: 72px !important; text-align: center; }
.cs-checks { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 10px; }
.cs-check { display: flex; align-items: center; gap: 10px; min-height: 44px; cursor: pointer; }
.cs-check span { flex: 1; }
.cs-abilities { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.cs-ability { gap: 2px; }
.cs-ability-head { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
.cs-ability-head label { font-size: 17px; font-weight: 700; }
.cs-ability-head input { text-align: center; font-size: 18px !important; }
.cs-mod { width: 56px; height: 56px; flex: 0 0 56px; border-radius: 28px; background: var(--cs-accent); color: #FFFFFF;
	display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; }
.cs-save { font-weight: 700; border-bottom: 1px solid var(--cs-line); min-height: 48px; }
.cs-line { display: flex; align-items: center; gap: 8px; }
.cs-line select { flex: 1; }

/* Buttons from the existing logic, restyled for touch */
.cs-page .button.add, .cs-page .button.delete { flex: 0 0 48px; width: 48px; height: 48px; border-radius: 24px;
	padding: 0; font-size: 22px; font-weight: 700; background: transparent; cursor: pointer; }
.cs-page .button.add { border: 2px solid #2E7D32; color: #2E7D32; }
.cs-page .button.delete { border: 2px solid #C62828; color: #C62828; }
.cs-page #addTalent { flex: 0 0 auto; min-height: 48px; padding: 0 24px; border: 0; border-radius: 8px;
	background: var(--cs-accent); color: #FFFFFF; font-weight: 700; cursor: pointer; }
.cs-talent-add { display: flex; align-items: center; gap: 10px; }
.cs-talent-add select { flex: 1; }

/* Talent cards */
.talent_list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; align-items: start; width: 100%; }
.talent_card { border: 1px solid var(--cs-line); border-radius: 10px; background: var(--cs-field); }
.talent_card_header { display: flex; align-items: center; gap: 10px; min-height: 52px; padding: 0 8px 0 14px; cursor: pointer; user-select: none; }
.talent_card_arrow { display: inline-block; width: 1em; transition: transform 0.15s; }
.talent_card.open .talent_card_arrow { transform: rotate(90deg); }
.talent_card_title { font-weight: 700; }
.talent_card_facts { flex: 1; color: #6B6346; }
.talent_card_body { margin: 0; padding: 0 14px 14px 36px; }
.talent_card_body dd { margin: 0 0 8px 0; white-space: pre-wrap; line-height: 1.5; }
#talent_dropdown option:disabled { color: #999; }

/* Sticky bar: always visible while scrolling */
.cs-bar { position: sticky; top: 0; z-index: 10; background: #4A4431; color: #FFFDF6;
	box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25); font-family: var(--fonts); }
.cs-bar-inner { max-width: 1240px; margin: 0 auto; padding: 8px 16px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.cs-bar-who { flex: 1 1 200px; min-width: 0; display: flex; flex-direction: column; }
.cs-bar-name { font-size: 20px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cs-bar-sub { font-size: 13px; opacity: 0.85; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cs-bar-hp { display: flex; align-items: center; gap: 8px; background: rgba(255, 253, 246, 0.12); border-radius: 28px; padding: 4px; }
.cs-bar-hp button { width: 48px; height: 48px; border-radius: 24px; border: 0; color: #FFFFFF; font-size: 24px; font-weight: 700; cursor: pointer; }
.cs-bar-hp .cs-hp-minus { background: #C62828; }
.cs-bar-hp .cs-hp-plus { background: #2E7D32; }
.cs-bar-stat { display: flex; flex-direction: column; align-items: center; min-width: 64px; }
.cs-bar-stat span:first-child { font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
.cs-bar-stat span:last-child { font-size: 22px; font-weight: 700; }
@media (max-width: 640px) {
	.cs-bar-inner { gap: 8px 12px; padding: 6px 10px; }
	.cs-bar-optional { display: none; }
}

/* Tablet landscape and desktop: two columns */
.cs-main { display: flex; flex-direction: column; gap: 16px; }
.cs-side { display: flex; flex-direction: column; gap: 16px; }
@media (min-width: 1000px) {
	.cs-identity { grid-template-columns: minmax(0, 2fr) minmax(0, 1.2fr) minmax(0, 0.7fr) minmax(0, 1.2fr) minmax(0, 1.5fr) minmax(0, 1.2fr); }
	.cs-identity .cs-name { grid-column: auto; }
	.cs-main { display: grid; grid-template-columns: 360px minmax(0, 1fr); align-items: start; }
	.cs-side .cs-two { grid-template-columns: minmax(0, 1fr); }
	.cs-abilities { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
/* Phone: one column */
@media (max-width: 640px) {
	.cs-page { padding: 10px; gap: 12px; }
	.cs-identity, .cs-two, .talent_list { grid-template-columns: minmax(0, 1fr); }
	.cs-identity .cs-name { grid-column: auto; }
	.cs-abilities { grid-template-columns: minmax(0, 1fr); }
}
</style>
</head>
<body>
<header class = "cs-bar" aria-label = "<?= e(t('character_summary')) ?>" data-i18n-aria = "character_summary">
	<div class = "cs-bar-inner">
		<div class = "cs-bar-who">
			<span class = "cs-bar-name" id = "bar_name">–</span>
			<span class = "cs-bar-sub" id = "bar_sub"></span>
		</div>
		<div class = "cs-bar-hp">
			<button type = "button" class = "cs-hp-minus" id = "hpMinus" aria-label = "<?= e(t('lose_hp')) ?>" data-i18n-aria = "lose_hp">−</button>
			<div class = "cs-bar-stat" aria-live = "polite"><span data-i18n = "hit_points"><?= e(t('hit_points')) ?></span><span id = "bar_hp">– / –</span></div>
			<button type = "button" class = "cs-hp-plus" id = "hpPlus" aria-label = "<?= e(t('gain_hp')) ?>" data-i18n-aria = "gain_hp">+</button>
		</div>
		<div class = "cs-bar-stat"><span data-i18n = "armor_class"><?= e(t('armor_class')) ?></span><span id = "bar_ac">–</span></div>
		<div class = "cs-bar-stat cs-bar-optional"><span data-i18n = "initiative"><?= e(t('initiative')) ?></span><span id = "bar_initiative">–</span></div>
		<div class = "cs-bar-stat cs-bar-optional"><span data-i18n = "speed"><?= e(t('speed')) ?></span><span id = "bar_speed">–</span></div>
		<div class = "cs-bar-stat cs-bar-optional"><span data-i18n = "inspiration"><?= e(t('inspiration')) ?></span><span id = "bar_inspiration">–</span></div>
	</div>
</header>
<div class = "cs-page" id = "character_basics">

	<!-- Picture of the character (only a preview in the browser; the upload comes with the save function) -->
	<section class = "cs-picture" aria-label = "<?= e(t('picture.title')) ?>" data-i18n-aria = "picture.title">
		<button type = "button" class = "cs-picture-frame" id = "pictureButton">
			<span class = "cs-picture-plus" id = "picturePlus" aria-hidden = "true">+</span>
			<span class = "cs-picture-text" id = "pictureText" data-i18n = "picture.add"><?= e(t('picture.add')) ?></span>
			<img id = "pictureThumb" alt = "" hidden>
		</button>
		<input type = "file" id = "pictureFile" accept = "image/png" hidden>
		<p class = "cs-picture-error" id = "pictureError" role = "alert"></p>
	</section>
	<dialog class = "cs-picture-dialog" id = "pictureDialog" aria-label = "<?= e(t('picture.title')) ?>" data-i18n-aria = "picture.title">
		<img id = "pictureLarge" alt = "">
		<div class = "cs-picture-actions">
			<button type = "button" id = "pictureChange" data-i18n = "picture.change"><?= e(t('picture.change')) ?></button>
			<button type = "button" class = "cs-picture-remove" id = "pictureRemove" data-i18n = "picture.remove"><?= e(t('picture.remove')) ?></button>
			<button type = "button" id = "pictureClose" data-i18n = "picture.close"><?= e(t('picture.close')) ?></button>
		</div>
	</dialog>

	<!-- Identity -->
	<section class = "cs-card" aria-labelledby = "cs-basics-title">
		<h2 id = "cs-basics-title" data-i18n = "basics"><?= e(t('basics')) ?></h2>
		<div class = "cs-identity">
			<div class = "cs-field cs-name">
				<label for = "name" data-i18n = "character_name"><?= e(t('character_name')) ?></label>
				<input name = "name" id = "name" autocomplete = "off">
			</div>
			<div class = "cs-field">
				<label for = "species" data-i18n = "species"><?= e(t('species')) ?></label>
				<select class = "class_dropdown" id = "species" name = "species" data-lookup = "species">
					<?php foreach ($species as $char_species): ?>
						<option class = "Dropdown_Entry" value = "<?= e($char_species['code']) ?>"><?= e($char_species['name']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class = "cs-field">
				<label for = "Level" data-i18n = "level"><?= e(t('level')) ?></label>
				<input name = "Level" id = "Level" inputmode = "numeric">
			</div>
			<div class = "cs-field">
				<label for = "class_dropdown" data-i18n = "class"><?= e(t('class')) ?></label>
				<select class = "class_dropdown" id = "class_dropdown" name = "class_dropdown" data-lookup = "class">
					<?php foreach ($classes as $class): ?>
						<option class = "Dropdown_Entry" value = "<?= e($class['code']) ?>"><?= e($class['name']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class = "cs-field">
				<label for = "subclass_dropdown" data-i18n = "subclass"><?= e(t('subclass')) ?></label>
				<select class = "class_dropdown" id = "subclass_dropdown" name = "subclass_dropdown" data-lookup = "subclass"></select>
			</div>
			<div class = "cs-field">
				<label for = "backgrounds" data-i18n = "background"><?= e(t('background')) ?></label>
				<select class = "class_dropdown" id = "backgrounds" name = "backgrounds" data-lookup = "background">
					<?php foreach ($backgrounds as $background): ?>
						<option class = "Dropdown_Entry" value = "<?= e($background['code']) ?>"><?= e($background['name']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</section>

	<!-- Combat values -->
	<section class = "cs-stats" aria-label = "<?= e(t('combat_values')) ?>" data-i18n-aria = "combat_values">
		<div class = "cs-card cs-stat">
			<label class = "cs-mini" for = "armorClass" data-i18n = "armor_class"><?= e(t('armor_class')) ?></label>
			<input name = "armorClass" id = "armorClass" inputmode = "numeric">
		</div>
		<div class = "cs-card cs-stat">
			<span class = "cs-mini" id = "cs-initiative-label" data-i18n = "initiative"><?= e(t('initiative')) ?></span>
			<label class = "cs-value" name = "initiative" id = "initiative" aria-labelledby = "cs-initiative-label"></label>
		</div>
		<div class = "cs-card cs-stat">
			<label class = "cs-mini" for = "Walking_Speed" data-i18n = "walking_speed"><?= e(t('walking_speed')) ?></label>
			<input type = "number" name = "Walking_Speed" id = "Walking_Speed" inputmode = "numeric">
		</div>
		<div class = "cs-card cs-stat">
			<label class = "cs-mini" for = "Proficiency_Bonus" data-i18n = "proficiency_bonus"><?= e(t('proficiency_bonus')) ?></label>
			<input type = "number" name = "Proficiency_Bonus" id = "Proficiency_Bonus" inputmode = "numeric">
		</div>
		<div class = "cs-card cs-stat">
			<label class = "cs-mini" for = "inspiration" data-i18n = "inspiration"><?= e(t('inspiration')) ?></label>
			<select id = "inspiration" name = "inspiration">
				<option id = "empty" class = "Dropdown_Entry" value = "">–</option>
				<option id = "D4" class = "Dropdown_Entry" value = "D4">D4</option>
				<option id = "D6" class = "Dropdown_Entry" value = "D6">D6</option>
				<option id = "D8" class = "Dropdown_Entry" value = "D8">D8</option>
				<option id = "D12" class = "Dropdown_Entry" value = "D12">D12</option>
			</select>
		</div>
	</section>

	<div class = "cs-main">
		<!-- Left column (landscape) -->
		<div class = "cs-side">
			<div class = "cs-two">
				<section class = "cs-card" aria-labelledby = "cs-hp-title">
					<h2 id = "cs-hp-title" data-i18n = "hit_points"><?= e(t('hit_points')) ?></h2>
					<div class = "cs-hp">
						<div class = "cs-field">
							<label for = "currentHitpoints" data-i18n = "hp_current"><?= e(t('hp_current')) ?></label>
							<input name = "currentHitpoints" id = "currentHitpoints" inputmode = "numeric">
						</div>
						<div class = "cs-field">
							<label for = "maxHitpoints" data-i18n = "hp_maximum"><?= e(t('hp_maximum')) ?></label>
							<input name = "maxHitpoints" id = "maxHitpoints" inputmode = "numeric">
						</div>
						<div class = "cs-field">
							<label for = "tempHitpoints" data-i18n = "hp_temporary"><?= e(t('hp_temporary')) ?></label>
							<input name = "tempHitpoints" id = "tempHitpoints" inputmode = "numeric">
						</div>
					</div>
					<div class = "cs-row">
						<span class = "cs-mini" data-i18n = "hit_dice"><?= e(t('hit_dice')) ?></span>
						<label name = "hitDice" id = "hitDice" style = "flex: 1; font-weight: 700;"></label>
						<label class = "cs-mini" for = "HitDiceUsed" data-i18n = "used"><?= e(t('used')) ?></label>
						<input name = "HitDiceUsed" id = "HitDiceUsed" inputmode = "numeric">
					</div>
				</section>

				<section class = "cs-card" aria-labelledby = "cs-armor-title">
					<h2 id = "cs-armor-title" data-i18n = "armor"><?= e(t('armor')) ?></h2>
					<div class = "cs-field">
						<label for = "armor" data-i18n = "armor"><?= e(t('armor')) ?></label>
						<select class = "class_dropdown" name = "armor" id = "armor"></select>
					</div>
					<div class = "cs-field">
						<label for = "shield" data-i18n = "shield"><?= e(t('shield')) ?></label>
						<select class = "class_dropdown" name = "shield" id = "shield"></select>
					</div>
					<div class = "cs-checks">
						<label class = "cs-check"><input type = "checkbox" name = "lightArmorProficiency" id = "lightArmorProficiency"><span data-i18n = "light_armor"><?= e(t('light_armor')) ?></span></label>
						<label class = "cs-check"><input type = "checkbox" name = "mediumArmorProficiency" id = "mediumArmorProficiency"><span data-i18n = "medium_armor"><?= e(t('medium_armor')) ?></span></label>
						<label class = "cs-check"><input type = "checkbox" name = "heavyArmorProficiency" id = "heavyArmorProficiency"><span data-i18n = "heavy_armor"><?= e(t('heavy_armor')) ?></span></label>
						<label class = "cs-check"><input type = "checkbox" name = "shieldProficiency" id = "shieldProficiency"><span data-i18n = "shield"><?= e(t('shield')) ?></span></label>
					</div>
				</section>
			</div>

			<div class = "cs-two">
				<section class = "cs-card" id = "Defense" aria-labelledby = "cs-defense-title">
					<div class = "cs-card-head">
						<h2 id = "cs-defense-title" data-i18n = "defense"><?= e(t('defense')) ?></h2>
						<button type = "button" class = "button add" id = "addDamageImmunity" aria-label = "<?= e(t('add_defense_line')) ?>" data-i18n-aria = "add_defense_line">+</button>
					</div>
					<div class = "cs-line" id = "defense_line0">
						<select class = "class_dropdown" name = "damageInfluence" data-lookup = "damage_influence" aria-label = "<?= e(t('influence')) ?>" data-i18n-aria = "influence">
							<option class = "Dropdown_Entry"></option>
							<?php foreach ($damageInfluences as $damageInfluence): ?>
								<option class = "Dropdown_Entry" value = "<?= e($damageInfluence['code']) ?>"><?= e($damageInfluence['name']) ?></option>
							<?php endforeach; ?>
						</select>
						<select class = "class_dropdown" name = "damageType" data-lookup = "damagetype" aria-label = "<?= e(t('damage_type')) ?>" data-i18n-aria = "damage_type">
							<option class = "Dropdown_Entry"></option>
							<?php foreach ($damageTypes as $damageType): ?>
								<option class = "Dropdown_Entry" value = "<?= e($damageType['code']) ?>"><?= e($damageType['name']) ?></option>
							<?php endforeach; ?>
						</select>
						<button type = "button" class = "button delete defense" aria-label = "<?= e(t('remove_line')) ?>" data-i18n-aria = "remove_line">-</button>
					</div>
				</section>

				<section class = "cs-card" id = "Condition" aria-labelledby = "cs-condition-title">
					<div class = "cs-card-head">
						<h2 id = "cs-condition-title" data-i18n = "conditions"><?= e(t('conditions')) ?></h2>
						<button type = "button" class = "button add" id = "addCondition" aria-label = "<?= e(t('add_condition')) ?>" data-i18n-aria = "add_condition">+</button>
					</div>
					<div class = "cs-line" id = "condition_line0">
						<select class = "class_dropdown" name = "conditions" data-lookup = "condition" aria-label = "<?= e(t('condition')) ?>" data-i18n-aria = "condition">
							<option class = "Dropdown_Entry"></option>
							<?php foreach ($conditions as $condition): ?>
								<option class = "Dropdown_Entry" value = "<?= e($condition['code']) ?>"><?= e($condition['name']) ?></option>
							<?php endforeach; ?>
						</select>
						<button type = "button" class = "button delete condition" aria-label = "<?= e(t('remove_condition')) ?>" data-i18n-aria = "remove_condition">-</button>
					</div>
				</section>
			</div>
		</div>

		<!-- Abilities (order from Abilities.sort_order; ids use the codes) -->
		<section class = "cs-abilities" aria-label = "<?= e(t('abilities')) ?>" data-i18n-aria = "abilities">
			<?php foreach ($abilities as $ability => $skills): ?>
			<div class = "cs-card cs-ability">
				<div class = "cs-ability-head">
					<output class = "cs-mod" name = "ability_<?= e($ability) ?>_mod" id = "ability_<?= e($ability) ?>_mod">0</output>
					<div class = "cs-field" style = "flex: 1;">
						<label for = "ability_<?= e($ability) ?>" data-i18n = "ability.<?= e($ability) ?>"><?= e(t('ability.' . $ability, $ability)) ?></label>
						<input name = "ability_<?= e($ability) ?>" id = "ability_<?= e($ability) ?>" value = "10" inputmode = "numeric" placeholder = "<?= e(t('score')) ?>" data-i18n-placeholder = "score">
					</div>
				</div>
				<label class = "cs-check cs-save">
					<input type = "checkbox" name = "save_<?= e($ability) ?>_prof" id = "save_<?= e($ability) ?>_prof">
					<span data-i18n = "saving_throw"><?= e(t('saving_throw')) ?></span>
					<output name = "save_<?= e($ability) ?>" id = "save_<?= e($ability) ?>">0</output>
				</label>
				<?php foreach ($skills as $skill): ?>
				<label class = "cs-check">
					<input type = "checkbox" name = "skill_<?= e($skill) ?>_prof" id = "skill_<?= e($skill) ?>_prof">
					<span data-i18n = "skill.<?= e($skill) ?>"><?= e(t('skill.' . $skill, $skill)) ?></span>
					<output name = "skill_<?= e($skill) ?>" id = "skill_<?= e($skill) ?>">0</output>
				</label>
				<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
		</section>
	</div>

	<!-- Talents -->
	<section class = "cs-card" id = "Talents" aria-labelledby = "cs-talents-title">
		<h2 id = "cs-talents-title" data-i18n = "talents"><?= e(t('talents')) ?></h2>
		<div class = "cs-talent-add">
			<label for = "talent_dropdown" class = "cs-mini" style = "position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0);" data-i18n = "talent"><?= e(t('talent')) ?></label>
			<select class = "class_dropdown" id = "talent_dropdown" name = "talent_dropdown">
				<option value = "" data-i18n = "select_talent"><?= e(t('select_talent')) ?></option>
				<?php foreach ($talents as $talent): ?>
					<option class = "Dropdown_Entry" value = "<?= (int)$talent['id'] ?>"><?= e($talent['name']) ?></option>
				<?php endforeach; ?>
			</select>
			<button type = "button" id = "addTalent" data-i18n = "add"><?= e(t('add')) ?></button>
		</div>
		<div id = "talent_list" class = "talent_list"></div>
	</section>

</div>

<?php require __DIR__ . '/partials/language_footer.php'; ?>
 <script src="/calculation.js"></script>
</body>

<script>
// --- Languages ---
let I18N = <?= json_encode(i18n_merged(current_lang()), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
const CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;

/** Text for a key in the current language; fallback if the key is unknown */
function tr(key, fallback) {
	return (I18N && Object.prototype.hasOwnProperty.call(I18N, key)) ? I18N[key] : (fallback ?? key);
}

/** Write the texts of the current language into all marked elements */
function applyI18n() {
	document.querySelectorAll("[data-i18n]").forEach(el => { el.textContent = tr(el.dataset.i18n, el.textContent); });
	document.querySelectorAll("[data-i18n-aria]").forEach(el => el.setAttribute("aria-label", tr(el.dataset.i18nAria)));
	document.querySelectorAll("[data-i18n-placeholder]").forEach(el => el.setAttribute("placeholder", tr(el.dataset.i18nPlaceholder)));
	document.querySelectorAll("[data-i18n-title]").forEach(el => el.setAttribute("title", tr(el.dataset.i18nTitle)));
	document.querySelectorAll(".talent_card").forEach(card => refreshTalentCard(card));
	if (typeof updateBar === "function") updateBar();
	if (typeof updatePictureUI === "function") updatePictureUI();
}

// --- Configuration ---
const abilities = <?= json_encode($abilities) ?>;
const classes = <?= json_encode($classes) ?>;
const subclasses = <?= json_encode($subclasses) ?>;


// --- State ---
let state = { Proficiency_Bonus: 0 };
let class_set = {char_class: "", char_subclass: ""};
let level;
let immunitiesCounter = 0;

// Pre-populate state for all abilities, saves, and skills
// An empty ability score counts as 10 (modifier 0)
const DEFAULT_ABILITY_SCORE = 10;

for (const [ability, skills] of Object.entries(abilities)) {
  state[ability] = DEFAULT_ABILITY_SCORE;
  state[`${ability}_Savingthrow_Proficiency`] = 0;
  for (const skill of skills) state[`${skill}_Proficiency`] = 0;
}

document.getElementById("addDamageImmunity").addEventListener("click", () => {
	const container = document.getElementById("Defense")
	const lineitem = document.getElementById("defense_line0");
	const newLineItem = lineitem.cloneNode(true);
	newLineItem.removeAttribute ('id');
	container.appendChild(newLineItem);
});


document.getElementById("Defense").addEventListener("click", function(e) {
	const button = e.target.closest(".button.delete.defense");
	if (button && e.target.parentElement.id != "defense_line0"){
		e.target.parentElement.remove();
	}
});

document.getElementById("addCondition").addEventListener("click", () => {
	const container = document.getElementById("Condition")
	const lineitem = document.getElementById("condition_line0");
	const newLineItem = lineitem.cloneNode(true);
	newLineItem.removeAttribute ('id');
	container.appendChild(newLineItem);
});

document.getElementById("Condition").addEventListener("click", function(e) {
	const button = e.target.closest(".button.delete.condition");
	if (button && e.target.parentElement.id != "condition_line0"){
		e.target.parentElement.remove();
	}
});

// --- Helpers ---
function updateAbilityUI(abilityName) {
  const score = state[abilityName];


  document.getElementById(`ability_${abilityName}_mod`).textContent =
    ability_modifier(score);

  document.getElementById(`save_${abilityName}`).textContent =
    skill_save_modifier(score, state[`${abilityName}_Savingthrow_Proficiency`], state.Proficiency_Bonus);

  for (const skill of abilities[abilityName]) {
    document.getElementById(`skill_${skill}`).textContent =
      skill_save_modifier(score, state[`${skill}_Proficiency`], state.Proficiency_Bonus);
  }

  if (abilityName === "dexterity") {
    updateInitiative();
  }
}

// Initiative = modifier of Dexterity (D&D 5e)
function updateInitiative() {
  document.getElementById("initiative").textContent = ability_modifier(state.dexterity);
}

// --- Event Listeners ---
for (const [ability, skills] of Object.entries(abilities)) {

  // Ability score input
  document.getElementById(`ability_${ability}`).addEventListener("input", function () {
    state[ability] = this.value.trim() === "" ? DEFAULT_ABILITY_SCORE : this.value;
    updateAbilityUI(ability);
  });

  // Saving throw proficiency checkbox
  document.getElementById(`save_${ability}_prof`).addEventListener("input", function () {
    state[`${ability}_Savingthrow_Proficiency`] = this.checked ? 1 : 0;
    updateAbilityUI(ability);
  });

  // Skill proficiency checkboxes
	for (const skill of skills) {
	document.getElementById(`skill_${skill}_prof`).addEventListener("input", function () {
	  state[`${skill}_Proficiency`] = this.checked ? 1 : 0;
	  updateAbilityUI(ability);
	});
	}
}

updateInitiative();

document.getElementById("Level").addEventListener("change", function () {
	class_set.class = document.getElementById("class_dropdown").value;
	level = this.value;	
	if (!level || !class_set.class) {
			return;
	}
	else {
	fetch(`/ajax/sandbox_endpoint.php?action=getHitDice&class=${encodeURIComponent(class_set.class)}&level=${encodeURIComponent(level)}`)
		.then(r => r.json())
		.then (dice => {
		document.getElementById("hitDice").textContent = `${level}D${dice.Hit_Dice}`;
	})

		.catch(err => console.error("Error:", err));
	}
});



//CLASS & SUBCLASS SELECT
	document.getElementById("class_dropdown").addEventListener("change", function () {
		class_set.class = this.value;			
		fetch(`/ajax/sandbox_endpoint.php?action=getSubclasses&class=${encodeURIComponent(class_set.class)}`)
			.then(r => r.json())
			.then(subclasses => populateSelect("subclass_dropdown", subclasses))
			.catch(err => console.error("Error:", err));
	});
	
	function populateSelect(selectId, items) {
		const select = document.getElementById(selectId);
		select.innerHTML = "";

		const placeholder = document.createElement("option");

		items.forEach(item => {

			const opt = document.createElement("option");
			opt.value = item.code;
			opt.textContent = item.name;
			select.appendChild(opt);
		});
	}

//PROFICIENCY BONUS
	document.getElementById("Proficiency_Bonus").addEventListener("input", function () {

		state.Proficiency_Bonus = this.value;
		for (const [ability, { skills }] of Object.entries(abilities)) {
			updateAbilityUI(ability);
		}
	});		
	

//TALENTS
	// Readable labels for the fields of Game_component_library (in the order they are shown)
	// Fields of Game_component_library in the order they are shown; labels come from "field.<name>"
	const componentFields = ["description", "action_type", "range", "attribute", "attack_type", "damage_type",
		"magic_effect", "projectile", "uses", "uses_spent", "duration", "precondition", "components", "material"];

	const talentDropdown = document.getElementById("talent_dropdown");
	const talentList = document.getElementById("talent_list");
	const talentsPending = new Set(); // ids that are loading now (prevents a double add)

	function isFilled(value) {
		// 0 counts as filled
		return value !== null && value !== undefined && String(value).trim() !== "";
	}

	function setTalentOptionDisabled(id, disabled) {
		const option = talentDropdown.querySelector(`option[value="${CSS.escape(String(id))}"]`);
		if (option) {
			option.disabled = disabled;
		}
	}

	function toggleTalentCard(card) {
		const open = !card.classList.contains("open");
		card.classList.toggle("open", open);
		card.querySelector(".talent_card_body").hidden = !open;
		card.querySelector(".talent_card_header").setAttribute("aria-expanded", open ? "true" : "false");
	}

	// Fill the facts in the header and the fields in the body (also after a change of the language)
	function refreshTalentCard(card) {
		const component = card._component;
		if (!component) return;
		card.querySelector(".talent_card_title").textContent = component.name;
		const facts = [];
		if (isFilled(component.action_type)) {
			facts.push(String(component.action_type));
		}
		if (isFilled(component.uses)) {
			facts.push(`${tr("field.uses")} ${isFilled(component.uses_spent) ? component.uses_spent : 0}/${component.uses}`);
		}
		card.querySelector(".talent_card_facts").textContent = facts.length ? "\u00B7 " + facts.join(" \u00B7 ") : "";
		const remove = card.querySelector(".button.delete");
		remove.title = tr("remove_talent");
		remove.setAttribute("aria-label", tr("remove_talent"));

		const body = card.querySelector(".talent_card_body");
		body.replaceChildren();
		for (const field of componentFields) {
			const value = component[field];
			if (!isFilled(value)) {
				continue;
			}
			const dt = document.createElement("dt");
			dt.className = "label";
			dt.textContent = tr("field." + field, field);
			const dd = document.createElement("dd");
			dd.textContent = String(value);
			body.append(dt, dd);
		}
	}

	function buildTalentCard(component) {
		const card = document.createElement("div");
		card.className = "talent_card";
		card.dataset.id = String(component.id);
		card._component = component;

		// Header: arrow, name, short facts, remove button
		const header = document.createElement("div");
		header.className = "talent_card_header";
		header.setAttribute("role", "button");
		header.setAttribute("tabindex", "0");
		header.setAttribute("aria-expanded", "false");

		const arrow = document.createElement("span");
		arrow.className = "talent_card_arrow";
		arrow.textContent = "\u25B8";

		const title = document.createElement("span");
		title.className = "talent_card_title";
		title.textContent = component.name;

		const factsSpan = document.createElement("span");
		factsSpan.className = "talent_card_facts";

		const remove = document.createElement("button");
		remove.type = "button";
		remove.className = "button delete";
		remove.textContent = "-";

		header.append(arrow, title, factsSpan, remove);

		// Body: all filled fields (closed at the start)
		const body = document.createElement("dl");
		body.className = "talent_card_body";
		body.hidden = true;

		card.append(header, body);
		refreshTalentCard(card);

		header.addEventListener("click", () => toggleTalentCard(card));
		header.addEventListener("keydown", e => {
			if (e.target === header && (e.key === "Enter" || e.key === " ")) {
				e.preventDefault();
				toggleTalentCard(card);
			}
		});
		remove.addEventListener("click", e => {
			e.stopPropagation(); // do not open or close the card
			card.remove();
			setTalentOptionDisabled(component.id, false);
		});

		return card;
	}

	document.getElementById("addTalent").addEventListener("click", function () {
		const selectedId = talentDropdown.value;
		if (!selectedId || talentsPending.has(selectedId) ||
			talentList.querySelector(`.talent_card[data-id="${CSS.escape(selectedId)}"]`)) {
			return; // nothing selected, or the talent is already in the list
		}
		talentsPending.add(selectedId);

		fetch(`/ajax/sandbox_endpoint.php?action=getComponent&id=${encodeURIComponent(selectedId)}`)
			.then(r => r.json())
			.then(component => {
				if (component.error) {
					console.error("Error:", component.error);
					return;
				}
				talentList.append(buildTalentCard(component));
				setTalentOptionDisabled(selectedId, true);
				talentDropdown.value = "";
			})
			.catch(err => console.error("Error:", err))
			.finally(() => talentsPending.delete(selectedId));
	});
	
	

//STICKY BAR (summary at the top)
	function barText(id) {
		const el = document.getElementById(id);
		if (el && el.tagName === "SELECT") {
			// A list shows the name of the selected entry; its value is a code
			const option = el.selectedOptions[0];
			return option ? option.textContent.trim() : "";
		}
		const v = el ? (("value" in el && el.tagName !== "LABEL") ? el.value : el.textContent) : "";
		return String(v ?? "").trim();
	}

	function updateBar() {
		document.getElementById("bar_name").textContent = barText("name") || "–";
		const sub = [barText("class_dropdown"), barText("subclass_dropdown"), barText("Level") ? tr("level") + " " + barText("Level") : ""]
			.filter(t => t !== "").join(" · ");
		document.getElementById("bar_sub").textContent = sub;
		document.getElementById("bar_hp").textContent = (barText("currentHitpoints") || "–") + " / " + (barText("maxHitpoints") || "–");
		document.getElementById("bar_ac").textContent = barText("armorClass") || "–";
		document.getElementById("bar_initiative").textContent = barText("initiative") || "–";
		document.getElementById("bar_speed").textContent = barText("Walking_Speed") || "–";
		document.getElementById("bar_inspiration").textContent = barText("inspiration") || "–";
	}

	// Change the current hit points by +1 or -1 (not below 0, not above the maximum)
	function changeHitpoints(delta) {
		const current = document.getElementById("currentHitpoints");
		const max = parseInt(document.getElementById("maxHitpoints").value, 10);
		let value = parseInt(current.value, 10);
		if (Number.isNaN(value)) {
			value = Number.isNaN(max) ? 0 : max; // start from the maximum
		}
		value += delta;
		if (value < 0) value = 0;
		if (!Number.isNaN(max) && value > max) value = max;
		current.value = String(value);
		current.dispatchEvent(new Event("input", { bubbles: true }));
	}

	document.getElementById("hpMinus").addEventListener("click", () => changeHitpoints(-1));
	document.getElementById("hpPlus").addEventListener("click", () => changeHitpoints(1));

	// Update the bar on every change of a field; the subclass list is filled later by the endpoint
	document.addEventListener("input", updateBar);
	document.addEventListener("change", updateBar);
	new MutationObserver(updateBar).observe(document.getElementById("subclass_dropdown"), { childList: true });
	new MutationObserver(updateBar).observe(document.getElementById("initiative"), { childList: true, characterData: true, subtree: true });
	updateBar();


//LANGUAGE MENU (footer): change the texts without a reload; entered values stay
	window.onLanguageChanged = function (data) {
		I18N = data.texts;
		document.documentElement.lang = data.lang;
		applyI18n();
		refreshContentNames();
	};

	// Give the entries of a list new names ({value: name}) and sort them; the empty entry stays first.
	// Moving an option keeps its selection.
	function renameOptions(select, names) {
		for (const option of select.options) {
			if (option.value !== "" && Object.prototype.hasOwnProperty.call(names, option.value)) {
				option.textContent = names[option.value];
			}
		}
		const lang = document.documentElement.lang;
		[...select.options]
			.filter(option => option.value !== "")
			.sort((a, b) => a.textContent.localeCompare(b.textContent, lang, { sensitivity: "base" }))
			.forEach(option => select.appendChild(option));
	}

	// Load the names of the game content (lists, talents) in the new language
	function refreshContentNames() {
		fetch("/ajax/sandbox_endpoint.php?action=getContentNames")
			.then(r => r.json())
			.then(content => {
				if (content.error) {
					console.error("Error:", content.error);
					return;
				}
				document.querySelectorAll("select[data-lookup]").forEach(select =>
					renameOptions(select, (content.lookup || {})[select.dataset.lookup] || {}));
				renameOptions(talentDropdown, content.talents || {});
				updateBar();
			})
			.catch(err => console.error("Error:", err));

		// Each talent card loads its texts again
		talentList.querySelectorAll(".talent_card").forEach(card => {
			fetch(`/ajax/sandbox_endpoint.php?action=getComponent&id=${encodeURIComponent(card.dataset.id)}`)
				.then(r => r.json())
				.then(component => {
					if (component.error) {
						console.error("Error:", component.error);
						return;
					}
					card._component = component;
					refreshTalentCard(card);
				})
				.catch(err => console.error("Error:", err));
		});
	}


// --- Picture of the character ---
// Only PNG, max. 20 MB. The picture stays in the browser (preview). The save function will upload characterPicture later.
const PICTURE_MAX_BYTES = 20 * 1024 * 1024;
const PNG_SIGNATURE = [0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A];
let characterPicture = null;    // the selected File, or null
let characterPictureUrl = null; // object URL of the preview

const pictureButton = document.getElementById("pictureButton");
const pictureFile   = document.getElementById("pictureFile");
const pictureThumb  = document.getElementById("pictureThumb");
const pictureError  = document.getElementById("pictureError");
const pictureDialog = document.getElementById("pictureDialog");
const pictureLarge  = document.getElementById("pictureLarge");

function updatePictureUI() {
	const hasPicture = characterPicture !== null;
	pictureThumb.hidden = !hasPicture;
	document.getElementById("picturePlus").hidden = hasPicture;
	document.getElementById("pictureText").hidden = hasPicture;
	pictureButton.setAttribute("aria-label", tr(hasPicture ? "picture.show" : "picture.add"));
	pictureThumb.alt = hasPicture ? tr("picture.alt") : "";
	pictureLarge.alt = hasPicture ? tr("picture.alt") : "";
}

// true if the file starts with the PNG signature (the file type alone can be wrong)
async function isPngFile(file) {
	const bytes = new Uint8Array(await file.slice(0, PNG_SIGNATURE.length).arrayBuffer());
	return PNG_SIGNATURE.every((b, i) => bytes[i] === b);
}

function setPicture(file) {
	if (characterPictureUrl) URL.revokeObjectURL(characterPictureUrl);
	characterPicture = file;
	characterPictureUrl = file ? URL.createObjectURL(file) : null;
	pictureThumb.src = characterPictureUrl || "";
	pictureLarge.src = characterPictureUrl || "";
	updatePictureUI();
}

pictureButton.addEventListener("click", function () {
	pictureError.textContent = "";
	if (characterPicture) {
		pictureDialog.showModal();
	} else {
		pictureFile.click();
	}
});

pictureFile.addEventListener("change", async function () {
	const file = this.files && this.files[0];
	this.value = ""; // the same file can be selected again
	if (!file) return;
	if (file.size > PICTURE_MAX_BYTES) {
		pictureError.textContent = tr("picture.err_size");
		return;
	}
	if (file.type !== "image/png" || !(await isPngFile(file))) {
		pictureError.textContent = tr("picture.err_type");
		return;
	}
	pictureError.textContent = "";
	setPicture(file);
});

document.getElementById("pictureChange").addEventListener("click", function () {
	pictureDialog.close();
	pictureFile.click();
});
document.getElementById("pictureRemove").addEventListener("click", function () {
	pictureDialog.close();
	setPicture(null);
	pictureButton.focus();
});
document.getElementById("pictureClose").addEventListener("click", () => pictureDialog.close());
// A tap outside the picture box closes the larger view
pictureDialog.addEventListener("click", function (event) {
	if (event.target === pictureDialog) pictureDialog.close();
});

updatePictureUI();
</script>
