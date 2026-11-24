<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
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

<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="<?= e(asset_url('/Assets/style.css')) ?>">
    <title data-i18n = "sandbox.title"><?= e(t('sandbox.title')) ?></title>
<link rel="stylesheet" href="<?= e(asset_url('/Assets/sandbox.css')) ?>">
</head>
<body>
<header class = "cs-bar" aria-label = "<?= e(t('character_summary')) ?>" data-i18n-aria = "character_summary">
	<div class = "cs-bar-inner">
		<!-- Picture of the character (only a preview in the browser; the upload comes with the save function) -->
		<button type = "button" class = "cs-picture-frame" id = "pictureButton" aria-label = "<?= e(t('picture.add')) ?>" title = "<?= e(t('picture.add')) ?>">
			<span class = "cs-picture-plus" id = "picturePlus" aria-hidden = "true">+</span>
			<img id = "pictureThumb" alt = "" hidden>
		</button>
		<input type = "file" id = "pictureFile" accept = "image/png" hidden>
		<button type = "button" class = "cs-bar-who" id = "identityButton" aria-expanded = "false" aria-controls = "identityPanel"
			title = "<?= e(t('identity.edit')) ?>" data-i18n-title = "identity.edit">
			<span class = "cs-bar-who-text">
				<span class = "cs-bar-name" id = "bar_name">–</span>
				<span class = "cs-bar-sub" id = "bar_sub"></span>
			</span>
			<svg aria-hidden = "true" width = "18" height = "18" viewBox = "0 0 24 24" fill = "none" stroke = "currentColor" stroke-width = "2" stroke-linecap = "round" stroke-linejoin = "round"><path d = "M12 20h9"></path><path d = "M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
		</button>
		<div class = "cs-bar-hp">
			<button type = "button" class = "cs-hp-minus" id = "hpMinus" aria-label = "<?= e(t('lose_hp')) ?>" data-i18n-aria = "lose_hp">−</button>
			<button type = "button" class = "cs-bar-stat cs-bar-edit" id = "hpButton" aria-expanded = "false" aria-controls = "hpPanel">
				<span data-i18n = "hit_points"><?= e(t('hit_points')) ?></span>
				<span id = "bar_hp" aria-live = "polite"><span id = "bar_hp_current">–</span> / <span id = "bar_hp_max">–</span><span class = "cs-sr-only" id = "bar_hp_note"></span></span>
			</button>
			<button type = "button" class = "cs-hp-plus" id = "hpPlus" aria-label = "<?= e(t('gain_hp')) ?>" data-i18n-aria = "gain_hp">+</button>
		</div>
		<button type = "button" class = "cs-bar-stat cs-bar-edit" id = "armorButton" aria-expanded = "false" aria-controls = "armorPanel"><span data-i18n = "armor_class"><?= e(t('armor_class')) ?></span><span id = "bar_ac">–</span></button>
		<div class = "cs-bar-stat"><span data-i18n = "initiative"><?= e(t('initiative')) ?></span><span id = "bar_initiative">–</span></div>
		<!-- Values that change only here: a tap opens the box "statPanel" with the field -->
		<button type = "button" class = "cs-bar-stat cs-bar-edit cs-bar-optional" data-field = "Walking_Speed" aria-expanded = "false" aria-controls = "statPanel"><span data-i18n = "speed"><?= e(t('speed')) ?></span><span id = "bar_speed">–</span></button>
		<button type = "button" class = "cs-bar-stat cs-bar-edit cs-bar-optional" data-field = "Proficiency_Bonus" aria-expanded = "false" aria-controls = "statPanel"><span data-i18n = "proficiency_bonus"><?= e(t('proficiency_bonus')) ?></span><span id = "bar_proficiency">–</span></button>
		<button type = "button" class = "cs-bar-stat cs-bar-edit cs-bar-optional" data-field = "inspiration" aria-expanded = "false" aria-controls = "statPanel"><span data-i18n = "inspiration"><?= e(t('inspiration')) ?></span><span id = "bar_inspiration">–</span></button>
		<button type = "button" class = "cs-bar-more" id = "barMore" aria-expanded = "false" aria-controls = "statPanel" data-i18n = "more"><?= e(t('more')) ?></button>
	</div>
	<p class = "cs-picture-error" id = "pictureError" role = "alert"></p>
	<!-- Identity panel: name, species, level, class, subclass, background (opens with the name in the bar) -->
	<div class = "cs-identity-panel" id = "identityPanel" role = "dialog" aria-label = "<?= e(t('basics')) ?>" data-i18n-aria = "basics" hidden>
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
		<button type = "button" class = "cs-identity-done" id = "identityDone" data-i18n = "identity.done"><?= e(t('identity.done')) ?></button>
	</div>
	<!-- Box for the values in the bar: speed, proficiency bonus, inspiration (opens with a tap on a value or on "More") -->
	<div class = "cs-stat-panel" id = "statPanel" role = "dialog" aria-label = "<?= e(t('bar_values')) ?>" data-i18n-aria = "bar_values" hidden>
		<div class = "cs-field" data-stat = "Walking_Speed">
			<label for = "Walking_Speed" data-i18n = "walking_speed"><?= e(t('walking_speed')) ?></label>
			<input type = "number" name = "Walking_Speed" id = "Walking_Speed" inputmode = "numeric">
		</div>
		<div class = "cs-field" data-stat = "Proficiency_Bonus">
			<label for = "Proficiency_Bonus" data-i18n = "proficiency_bonus"><?= e(t('proficiency_bonus')) ?></label>
			<input type = "number" name = "Proficiency_Bonus" id = "Proficiency_Bonus" inputmode = "numeric">
		</div>
		<div class = "cs-field" data-stat = "inspiration">
			<label for = "inspiration" data-i18n = "inspiration"><?= e(t('inspiration')) ?></label>
			<select id = "inspiration" name = "inspiration">
				<option id = "empty" class = "Dropdown_Entry" value = "">–</option>
				<option id = "D4" class = "Dropdown_Entry" value = "D4">D4</option>
				<option id = "D6" class = "Dropdown_Entry" value = "D6">D6</option>
				<option id = "D8" class = "Dropdown_Entry" value = "D8">D8</option>
				<option id = "D12" class = "Dropdown_Entry" value = "D12">D12</option>
			</select>
		</div>
		<button type = "button" class = "cs-identity-done" id = "statDone" data-i18n = "identity.done"><?= e(t('identity.done')) ?></button>
	</div>
	<!-- Hit point menu: current, maximum, temporary hit points and hit dice (opens with the middle of the hit point dial) -->
	<div class = "cs-stat-panel cs-hp-panel" id = "hpPanel" role = "dialog" aria-label = "<?= e(t('hit_points')) ?>" data-i18n-aria = "hit_points" hidden>
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
			<span id = "hitDice" style = "flex: 1; font-weight: 700;"></span>
			<label class = "cs-mini" for = "HitDiceUsed" data-i18n = "hit_dice_used"><?= e(t('hit_dice_used')) ?></label>
			<input name = "HitDiceUsed" id = "HitDiceUsed" inputmode = "numeric">
		</div>
		<button type = "button" class = "cs-identity-done" id = "hpDone" data-i18n = "identity.done"><?= e(t('identity.done')) ?></button>
	</div>
	<!-- Armor menu: armor class, armor, shield, armor proficiency (opens with "Armor class" in the bar) -->
	<div class = "cs-stat-panel cs-armor-panel" id = "armorPanel" role = "dialog" aria-label = "<?= e(t('armor')) ?>" data-i18n-aria = "armor" hidden>
		<div class = "cs-field">
			<label for = "armorClass" data-i18n = "armor_class"><?= e(t('armor_class')) ?></label>
			<input name = "armorClass" id = "armorClass" inputmode = "numeric">
		</div>
		<div class = "cs-field">
			<label for = "armor" data-i18n = "armor"><?= e(t('armor')) ?></label>
			<select class = "class_dropdown" name = "armor" id = "armor"></select>
		</div>
		<div class = "cs-field">
			<label for = "shield" data-i18n = "shield"><?= e(t('shield')) ?></label>
			<select class = "class_dropdown" name = "shield" id = "shield"></select>
		</div>
		<span class = "cs-toggles-title" id = "cs-armor-prof-title" data-i18n = "armor_proficiency"><?= e(t('armor_proficiency')) ?></span>
		<div class = "cs-toggles" role = "group" aria-labelledby = "cs-armor-prof-title">
			<label class = "cs-check"><input type = "checkbox" name = "lightArmorProficiency" id = "lightArmorProficiency"><span data-i18n = "light_armor"><?= e(t('light_armor')) ?></span></label>
			<label class = "cs-check"><input type = "checkbox" name = "mediumArmorProficiency" id = "mediumArmorProficiency"><span data-i18n = "medium_armor"><?= e(t('medium_armor')) ?></span></label>
			<label class = "cs-check"><input type = "checkbox" name = "heavyArmorProficiency" id = "heavyArmorProficiency"><span data-i18n = "heavy_armor"><?= e(t('heavy_armor')) ?></span></label>
			<label class = "cs-check"><input type = "checkbox" name = "shieldProficiency" id = "shieldProficiency"><span data-i18n = "shield"><?= e(t('shield')) ?></span></label>
		</div>
		<button type = "button" class = "cs-identity-done" id = "armorDone" data-i18n = "identity.done"><?= e(t('identity.done')) ?></button>
	</div>
</header>
<div class = "cs-page" id = "character_basics">

	<dialog class = "cs-picture-dialog" id = "pictureDialog" aria-label = "<?= e(t('picture.title')) ?>" data-i18n-aria = "picture.title">
		<img id = "pictureLarge" alt = "">
		<div class = "cs-picture-actions">
			<button type = "button" id = "pictureChange" data-i18n = "picture.change"><?= e(t('picture.change')) ?></button>
			<button type = "button" class = "cs-picture-remove" id = "pictureRemove" data-i18n = "picture.remove"><?= e(t('picture.remove')) ?></button>
			<button type = "button" id = "pictureClose" data-i18n = "picture.close"><?= e(t('picture.close')) ?></button>
		</div>
	</dialog>


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

	<!-- Defense and conditions: one strip over the full width -->
	<section class = "cs-card cs-strip" id = "Defense" aria-labelledby = "cs-defense-title">
		<h2 id = "cs-defense-title" data-i18n = "defense_and_conditions"><?= e(t('defense_and_conditions')) ?></h2>
		<ul class = "cs-chips" id = "defenseChips" aria-labelledby = "cs-defense-title"></ul>
		<p class = "cs-chips-empty" id = "defenseEmpty" data-i18n = "defense.none"><?= e(t('defense.none')) ?></p>
		<div class = "cs-chip-buttons">
			<button type = "button" class = "cs-chip-add" id = "addDamageImmunity" aria-expanded = "false" aria-controls = "defenseForm">+ <span data-i18n = "defense"><?= e(t('defense')) ?></span></button>
			<button type = "button" class = "cs-chip-add" id = "addCondition" aria-expanded = "false" aria-controls = "conditionForm">+ <span data-i18n = "condition"><?= e(t('condition')) ?></span></button>
		</div>
		<div class = "cs-chip-form" id = "defenseForm" hidden>
			<select class = "class_dropdown" id = "defenseInfluence" name = "damageInfluence" data-lookup = "damage_influence" aria-label = "<?= e(t('influence')) ?>" data-i18n-aria = "influence">
				<option class = "Dropdown_Entry" value = ""></option>
				<?php foreach ($damageInfluences as $damageInfluence): ?>
					<option class = "Dropdown_Entry" value = "<?= e($damageInfluence['code']) ?>"><?= e($damageInfluence['name']) ?></option>
				<?php endforeach; ?>
			</select>
			<select class = "class_dropdown" id = "defenseDamageType" name = "damageType" data-lookup = "damagetype" aria-label = "<?= e(t('damage_type')) ?>" data-i18n-aria = "damage_type">
				<option class = "Dropdown_Entry" value = ""></option>
				<?php foreach ($damageTypes as $damageType): ?>
					<option class = "Dropdown_Entry" value = "<?= e($damageType['code']) ?>"><?= e($damageType['name']) ?></option>
				<?php endforeach; ?>
			</select>
			<button type = "button" class = "cs-chip-confirm" id = "defenseAdd" data-i18n = "add"><?= e(t('add')) ?></button>
		</div>
		<div class = "cs-chip-form" id = "conditionForm" hidden>
			<select class = "class_dropdown" id = "conditionSelect" name = "conditions" data-lookup = "condition" aria-label = "<?= e(t('condition')) ?>" data-i18n-aria = "condition">
				<option class = "Dropdown_Entry" value = ""></option>
				<?php foreach ($conditions as $condition): ?>
					<option class = "Dropdown_Entry" value = "<?= e($condition['code']) ?>"><?= e($condition['name']) ?></option>
				<?php endforeach; ?>
			</select>
			<button type = "button" class = "cs-chip-confirm" id = "conditionAdd" data-i18n = "add"><?= e(t('add')) ?></button>
		</div>
		<p class = "cs-chip-message" id = "defenseMessage" aria-live = "polite"></p>
	</section>

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
 <script src="<?= e(asset_url('/calculation.js')) ?>"></script>
</body>

<script>
// Data from PHP for Assets/sandbox.js (the file cannot contain PHP)
let I18N = <?= json_encode(i18n_merged(current_lang()), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
const CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
const abilities = <?= json_encode($abilities) ?>;
const classes = <?= json_encode($classes) ?>;
const subclasses = <?= json_encode($subclasses) ?>;
</script>
<script src="<?= e(asset_url('/Assets/sandbox.js')) ?>"></script>
