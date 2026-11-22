<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/calculation.php';
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

		$stmt = $pdo->prepare('SELECT DISTINCT Class FROM Classes');
		$stmt->execute();
		$classes = $stmt->fetchall(PDO::FETCH_COLUMN);

		$stmt = $pdo->prepare('SELECT DISTINCT species FROM Species');
		$stmt->execute();
		$species = $stmt->fetchall(PDO::FETCH_COLUMN);

		$stmt = $pdo->prepare('SELECT ability, skills FROM abilities_skills');
		$stmt->execute();
		$abilities_skills = $stmt->fetchall();
		
	Foreach ($abilities_skills as $row) {
			if ($row['skills'] === NULL) {
				$abilities[$row['ability']]= $skills = [];
			}
			else {
				$abilities[$row['ability']]= $skills = explode(',', $row['skills']);

			}
	}		
		$stmt = $pdo->prepare('SELECT background FROM Backgrounds');
		$stmt->execute();
		$backgrounds = $stmt->fetchall(PDO::FETCH_COLUMN);
	
		$stmt = $pdo->prepare('SELECT influence FROM Damage_influence');
		$stmt->execute();
		$damageInfluences = $stmt->fetchall(PDO::FETCH_COLUMN);

		$stmt = $pdo->prepare('SELECT damagetype FROM Damagetypes');
		$stmt->execute();
		$damageTypes = $stmt->fetchall(PDO::FETCH_COLUMN);

		$stmt = $pdo->prepare('SELECT char_condition FROM Conditions');
		$stmt->execute();
		$conditions = $stmt->fetchall(PDO::FETCH_COLUMN);

		// Talents for the talent menu (only id and name; details come from the endpoint)
		$stmt = $pdo->prepare("SELECT id, name FROM Game_component_library WHERE FIND_IN_SET('talent', type) > 0 ORDER BY name");
		$stmt->execute();
		$talents = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
.cs-lang { min-height: 48px; padding: 0 10px; border-radius: 8px; border: 1px solid rgba(255, 253, 246, 0.5);
	background: transparent; color: #FFFDF6; font: inherit; cursor: pointer; }
.cs-lang option { color: #4A4431; }
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
		<select class = "cs-lang" id = "langSelect" aria-label = "<?= e(t('language')) ?>" data-i18n-aria = "language">
			<?php foreach (i18n_languages() as $code => $langName): ?>
				<option value = "<?= e($code) ?>"<?= $code === current_lang() ? ' selected' : '' ?>><?= e($langName) ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</header>
<div class = "cs-page" id = "character_basics">

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
				<select class = "class_dropdown" id = "species" name = "species">
					<?php foreach ($species as $char_species): ?>
						<option id = "<?= e($char_species) ?>" class = "Dropdown_Entry" value = "<?= e($char_species) ?>"><?= e($char_species) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class = "cs-field">
				<label for = "Level" data-i18n = "level"><?= e(t('level')) ?></label>
				<input name = "Level" id = "Level" inputmode = "numeric">
			</div>
			<div class = "cs-field">
				<label for = "class_dropdown" data-i18n = "class"><?= e(t('class')) ?></label>
				<select class = "class_dropdown" id = "class_dropdown" name = "class_dropdown">
					<?php foreach ($classes as $class): ?>
						<option id = "<?= e($class) ?>" class = "Dropdown_Entry" value = "<?= e($class) ?>"><?= e($class) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class = "cs-field">
				<label for = "subclass_dropdown" data-i18n = "subclass"><?= e(t('subclass')) ?></label>
				<select class = "class_dropdown" id = "subclass_dropdown" name = "subclass_dropdown"></select>
			</div>
			<div class = "cs-field">
				<label for = "backgrounds" data-i18n = "background"><?= e(t('background')) ?></label>
				<select class = "class_dropdown" id = "backgrounds" name = "backgrounds">
					<?php foreach ($backgrounds as $background): ?>
						<option id = "<?= e($background) ?>" class = "Dropdown_Entry" value = "<?= e($background) ?>"><?= e($background) ?></option>
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
						<select class = "class_dropdown" name = "damageInfluence" aria-label = "<?= e(t('influence')) ?>" data-i18n-aria = "influence">
							<option class = "Dropdown_Entry"></option>
							<?php foreach ($damageInfluences as $damageInfluence): ?>
								<option class = "Dropdown_Entry" value = "<?= e($damageInfluence) ?>"><?= e($damageInfluence) ?></option>
							<?php endforeach; ?>
						</select>
						<select class = "class_dropdown" name = "damageType" aria-label = "<?= e(t('damage_type')) ?>" data-i18n-aria = "damage_type">
							<option class = "Dropdown_Entry"></option>
							<?php foreach ($damageTypes as $damageType): ?>
								<option class = "Dropdown_Entry" value = "<?= e($damageType) ?>"><?= e($damageType) ?></option>
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
						<select class = "class_dropdown" name = "conditions" aria-label = "<?= e(t('condition')) ?>" data-i18n-aria = "condition">
							<option class = "Dropdown_Entry"></option>
							<?php foreach ($conditions as $condition): ?>
								<option class = "Dropdown_Entry"><?= e($condition) ?></option>
							<?php endforeach; ?>
						</select>
						<button type = "button" class = "button delete condition" aria-label = "<?= e(t('remove_condition')) ?>" data-i18n-aria = "remove_condition">-</button>
					</div>
				</section>
			</div>
		</div>

		<!-- Abilities (standard order; the logic does not depend on the order) -->
		<section class = "cs-abilities" aria-label = "<?= e(t('abilities')) ?>" data-i18n-aria = "abilities">
			<?php
			$abilityOrder = ['Strength', 'Dexterity', 'Constitution', 'Intelligence', 'Wisdom', 'Charisma'];
			$abilitiesDisplay = [];
			foreach ($abilityOrder as $name) {
				if (isset($abilities[$name])) {
					$abilitiesDisplay[$name] = $abilities[$name];
				}
			}
			$abilitiesDisplay += $abilities; // any other ability from the database
			?>
			<?php foreach ($abilitiesDisplay as $ability => $skills): ?>
			<div class = "cs-card cs-ability">
				<div class = "cs-ability-head">
					<output class = "cs-mod" name = "<?= e($ability) ?>_Modifier" id = "<?= e($ability) ?>_Modifier">0</output>
					<div class = "cs-field" style = "flex: 1;">
						<label for = "<?= e($ability) ?>" data-i18n = "ability.<?= e($ability) ?>"><?= e(t('ability.' . $ability, $ability)) ?></label>
						<input name = "<?= e($ability) ?>" id = "<?= e($ability) ?>" inputmode = "numeric" placeholder = "<?= e(t('score')) ?>" data-i18n-placeholder = "score">
					</div>
				</div>
				<label class = "cs-check cs-save">
					<input type = "checkbox" name = "<?= e($ability) ?>_Savingthrow_Proficiency" id = "<?= e($ability) ?>_Savingthrow_Proficiency">
					<span data-i18n = "saving_throw"><?= e(t('saving_throw')) ?></span>
					<output name = "<?= e($ability) ?>_Savingthrow" id = "<?= e($ability) ?>_Savingthrow">0</output>
				</label>
				<?php foreach ($skills as $skill): ?>
				<label class = "cs-check">
					<input type = "checkbox" name = "<?= e($skill) ?>_Proficiency" id = "<?= e($skill) ?>_Proficiency">
					<span data-i18n = "skill.<?= e(trim($skill)) ?>"><?= e(t('skill.' . trim($skill), trim($skill))) ?></span>
					<output name = "<?= e($skill) ?>" id = "<?= e($skill) ?>">0</output>
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
for (const [ability, skills] of Object.entries(abilities)) {
  state[ability] = 0;
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


  document.getElementById(`${abilityName}_Modifier`).textContent =
    ability_modifier(score);

  document.getElementById(`${abilityName}_Savingthrow`).textContent =
    skill_save_modifier(score, state[`${abilityName}_Savingthrow_Proficiency`], state.Proficiency_Bonus);

  for (const skill of abilities[abilityName]) {
    document.getElementById(skill).textContent =
      skill_save_modifier(score, state[`${skill}_Proficiency`], state.Proficiency_Bonus);
  }
}

// --- Event Listeners ---
for (const [ability, skills] of Object.entries(abilities)) {

  // Ability score input
  document.getElementById(ability).addEventListener("input", function () {
    state[ability] = this.value;
    updateAbilityUI(ability);
  });

  // Saving throw proficiency checkbox
  document.getElementById(`${ability}_Savingthrow_Proficiency`).addEventListener("input", function () {
    state[`${ability}_Savingthrow_Proficiency`] = this.checked ? 1 : 0;
    updateAbilityUI(ability);
  });

  // Skill proficiency checkboxes
	for (const skill of skills) {
	document.getElementById(`${skill}_Proficiency`).addEventListener("input", function () {
	  state[`${skill}_Proficiency`] = this.checked ? 1 : 0;
	  updateAbilityUI(ability);
	});
	}
}

document.getElementById("Level").addEventListener("change", function () {
	class_set.class = document.getElementById("class_dropdown").value;
	level = this.value;	
	if (!level || !class_set.class) {
			return;
	}
	else {
	fetch(`/ajax/sandbox_endpoint.php?action=getHitDice&class=${class_set.class}&level=${level}`)
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
		fetch(`/ajax/sandbox_endpoint.php?action=getSubclasses&class=${class_set.class}`)
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
			opt.value = item.Subclass;
			opt.textContent = item.Subclass;
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


//LANGUAGE MENU: change the language without a reload (entered values stay)
	document.getElementById("langSelect").addEventListener("change", function () {
		const select = this;
		const previous = document.documentElement.lang;
		const body = new URLSearchParams({ csrf: CSRF_TOKEN, lang: select.value });
		select.disabled = true;
		fetch("/ajax/i18n.php", { method: "POST", body, credentials: "same-origin" })
			.then(r => r.ok ? r.json() : Promise.reject(new Error("HTTP " + r.status)))
			.then(data => {
				I18N = data.texts;
				document.documentElement.lang = data.lang;
				applyI18n();
			})
			.catch(err => {
				console.error("Error:", err);
				select.value = previous; // keep the old language
			})
			.finally(() => { select.disabled = false; });
	});

</script>
