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

<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/Assets/style.css">
    <title>Sandbox</title>
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
<div class = "cs-page" id = "character_basics">

	<!-- Identity -->
	<section class = "cs-card" aria-labelledby = "cs-basics-title">
		<h2 id = "cs-basics-title">Basics</h2>
		<div class = "cs-identity">
			<div class = "cs-field cs-name">
				<label for = "name">Character name</label>
				<input name = "name" id = "name" autocomplete = "off">
			</div>
			<div class = "cs-field">
				<label for = "species">Species</label>
				<select class = "class_dropdown" id = "species" name = "species">
					<?php foreach ($species as $char_species): ?>
						<option id = "<?= e($char_species) ?>" class = "Dropdown_Entry" value = "<?= e($char_species) ?>"><?= e($char_species) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class = "cs-field">
				<label for = "Level">Level</label>
				<input name = "Level" id = "Level" inputmode = "numeric">
			</div>
			<div class = "cs-field">
				<label for = "class_dropdown">Class</label>
				<select class = "class_dropdown" id = "class_dropdown" name = "class_dropdown">
					<?php foreach ($classes as $class): ?>
						<option id = "<?= e($class) ?>" class = "Dropdown_Entry" value = "<?= e($class) ?>"><?= e($class) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class = "cs-field">
				<label for = "subclass_dropdown">Subclass</label>
				<select class = "class_dropdown" id = "subclass_dropdown" name = "subclass_dropdown"></select>
			</div>
			<div class = "cs-field">
				<label for = "backgrounds">Background</label>
				<select class = "class_dropdown" id = "backgrounds" name = "backgrounds">
					<?php foreach ($backgrounds as $background): ?>
						<option id = "<?= e($background) ?>" class = "Dropdown_Entry" value = "<?= e($background) ?>"><?= e($background) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</section>

	<!-- Combat values -->
	<section class = "cs-stats" aria-label = "Combat values">
		<div class = "cs-card cs-stat">
			<label class = "cs-mini" for = "armorClass">Armor class</label>
			<input name = "armorClass" id = "armorClass" inputmode = "numeric">
		</div>
		<div class = "cs-card cs-stat">
			<span class = "cs-mini" id = "cs-initiative-label">Initiative</span>
			<label class = "cs-value" name = "initiative" id = "initiative" aria-labelledby = "cs-initiative-label"></label>
		</div>
		<div class = "cs-card cs-stat">
			<label class = "cs-mini" for = "Walking_Speed">Walking speed</label>
			<input type = "number" name = "Walking_Speed" id = "Walking_Speed" inputmode = "numeric">
		</div>
		<div class = "cs-card cs-stat">
			<label class = "cs-mini" for = "Proficiency_Bonus">Proficiency bonus</label>
			<input type = "number" name = "Proficiency_Bonus" id = "Proficiency_Bonus" inputmode = "numeric">
		</div>
		<div class = "cs-card cs-stat">
			<label class = "cs-mini" for = "inspiration">Inspiration</label>
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
					<h2 id = "cs-hp-title">Hit points</h2>
					<div class = "cs-hp">
						<div class = "cs-field">
							<label for = "currentHitpoints">Current</label>
							<input name = "currentHitpoints" id = "currentHitpoints" inputmode = "numeric">
						</div>
						<div class = "cs-field">
							<label for = "maxHitpoints">Maximum</label>
							<input name = "maxHitpoints" id = "maxHitpoints" inputmode = "numeric">
						</div>
						<div class = "cs-field">
							<label for = "tempHitpoints">Temporary</label>
							<input name = "tempHitpoints" id = "tempHitpoints" inputmode = "numeric">
						</div>
					</div>
					<div class = "cs-row">
						<span class = "cs-mini">Hit dice</span>
						<label name = "hitDice" id = "hitDice" style = "flex: 1; font-weight: 700;"></label>
						<label class = "cs-mini" for = "HitDiceUsed">Used</label>
						<input name = "HitDiceUsed" id = "HitDiceUsed" inputmode = "numeric">
					</div>
				</section>

				<section class = "cs-card" aria-labelledby = "cs-armor-title">
					<h2 id = "cs-armor-title">Armor</h2>
					<div class = "cs-field">
						<label for = "armor">Armor</label>
						<select class = "class_dropdown" name = "armor" id = "armor"></select>
					</div>
					<div class = "cs-field">
						<label for = "shield">Shield</label>
						<select class = "class_dropdown" name = "shield" id = "shield"></select>
					</div>
					<div class = "cs-checks">
						<label class = "cs-check"><input type = "checkbox" name = "lightArmorProficiency" id = "lightArmorProficiency"><span>Light armor</span></label>
						<label class = "cs-check"><input type = "checkbox" name = "mediumArmorProficiency" id = "mediumArmorProficiency"><span>Medium armor</span></label>
						<label class = "cs-check"><input type = "checkbox" name = "heavyArmorProficiency" id = "heavyArmorProficiency"><span>Heavy armor</span></label>
						<label class = "cs-check"><input type = "checkbox" name = "shieldProficiency" id = "shieldProficiency"><span>Shield</span></label>
					</div>
				</section>
			</div>

			<div class = "cs-two">
				<section class = "cs-card" id = "Defense" aria-labelledby = "cs-defense-title">
					<div class = "cs-card-head">
						<h2 id = "cs-defense-title">Defense</h2>
						<button type = "button" class = "button add" id = "addDamageImmunity" aria-label = "Add a defense line">+</button>
					</div>
					<div class = "cs-line" id = "defense_line0">
						<select class = "class_dropdown" name = "damageInfluence" aria-label = "Influence">
							<option class = "Dropdown_Entry"></option>
							<?php foreach ($damageInfluences as $damageInfluence): ?>
								<option class = "Dropdown_Entry" value = "<?= e($damageInfluence) ?>"><?= e($damageInfluence) ?></option>
							<?php endforeach; ?>
						</select>
						<select class = "class_dropdown" name = "damageType" aria-label = "Damage type">
							<option class = "Dropdown_Entry"></option>
							<?php foreach ($damageTypes as $damageType): ?>
								<option class = "Dropdown_Entry" value = "<?= e($damageType) ?>"><?= e($damageType) ?></option>
							<?php endforeach; ?>
						</select>
						<button type = "button" class = "button delete defense" aria-label = "Remove this line">-</button>
					</div>
				</section>

				<section class = "cs-card" id = "Condition" aria-labelledby = "cs-condition-title">
					<div class = "cs-card-head">
						<h2 id = "cs-condition-title">Conditions</h2>
						<button type = "button" class = "button add" id = "addCondition" aria-label = "Add a condition">+</button>
					</div>
					<div class = "cs-line" id = "condition_line0">
						<select class = "class_dropdown" name = "conditions" aria-label = "Condition">
							<option class = "Dropdown_Entry"></option>
							<?php foreach ($conditions as $condition): ?>
								<option class = "Dropdown_Entry"><?= e($condition) ?></option>
							<?php endforeach; ?>
						</select>
						<button type = "button" class = "button delete condition" aria-label = "Remove this condition">-</button>
					</div>
				</section>
			</div>
		</div>

		<!-- Abilities (standard order; the logic does not depend on the order) -->
		<section class = "cs-abilities" aria-label = "Abilities">
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
						<label for = "<?= e($ability) ?>"><?= e($ability) ?></label>
						<input name = "<?= e($ability) ?>" id = "<?= e($ability) ?>" inputmode = "numeric" placeholder = "Score">
					</div>
				</div>
				<label class = "cs-check cs-save">
					<input type = "checkbox" name = "<?= e($ability) ?>_Savingthrow_Proficiency" id = "<?= e($ability) ?>_Savingthrow_Proficiency">
					<span>Saving throw</span>
					<output name = "<?= e($ability) ?>_Savingthrow" id = "<?= e($ability) ?>_Savingthrow">0</output>
				</label>
				<?php foreach ($skills as $skill): ?>
				<label class = "cs-check">
					<input type = "checkbox" name = "<?= e($skill) ?>_Proficiency" id = "<?= e($skill) ?>_Proficiency">
					<span><?= e($skill) ?></span>
					<output name = "<?= e($skill) ?>" id = "<?= e($skill) ?>">0</output>
				</label>
				<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
		</section>
	</div>

	<!-- Talents -->
	<section class = "cs-card" id = "Talents" aria-labelledby = "cs-talents-title">
		<h2 id = "cs-talents-title">Talents</h2>
		<div class = "cs-talent-add">
			<label for = "talent_dropdown" class = "cs-mini" style = "position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0);">Talent</label>
			<select class = "class_dropdown" id = "talent_dropdown" name = "talent_dropdown">
				<option value = "">– select a talent –</option>
				<?php foreach ($talents as $talent): ?>
					<option class = "Dropdown_Entry" value = "<?= (int)$talent['id'] ?>"><?= e($talent['name']) ?></option>
				<?php endforeach; ?>
			</select>
			<button type = "button" id = "addTalent">Add</button>
		</div>
		<div id = "talent_list" class = "talent_list"></div>
	</section>

</div>

 <script src="/calculation.js"></script>
</body>

<script>
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
	const componentLabels = {
		description:  "Description",
		action_type:  "Action type",
		range:        "Range",
		attribute:    "Attribute",
		attack_type:  "Attack type",
		damage_type:  "Damage type",
		magic_effect: "Magic effect",
		projectile:   "Projectile",
		uses:         "Uses",
		uses_spent:   "Uses spent",
		duration:     "Duration",
		precondition: "Precondition",
		components:   "Components",
		material:     "Material"
	};

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

	function buildTalentCard(component) {
		const card = document.createElement("div");
		card.className = "talent_card";
		card.dataset.id = String(component.id);

		// Header: arrow, name, short facts, remove button
		const header = document.createElement("div");
		header.className = "talent_card_header";
		header.setAttribute("role", "button");
		header.setAttribute("tabindex", "0");
		header.setAttribute("aria-expanded", "false");

		const arrow = document.createElement("span");
		arrow.className = "talent_card_arrow";
		arrow.textContent = "▸";

		const title = document.createElement("span");
		title.className = "talent_card_title";
		title.textContent = component.name;

		const facts = [];
		if (isFilled(component.action_type)) {
			facts.push(String(component.action_type));
		}
		if (isFilled(component.uses)) {
			facts.push(`Uses ${isFilled(component.uses_spent) ? component.uses_spent : 0}/${component.uses}`);
		}
		const factsSpan = document.createElement("span");
		factsSpan.className = "talent_card_facts";
		factsSpan.textContent = facts.length ? "· " + facts.join(" · ") : "";

		const remove = document.createElement("button");
		remove.type = "button";
		remove.className = "button delete";
		remove.title = "Remove talent";
		remove.textContent = "-";

		header.append(arrow, title, factsSpan, remove);

		// Body: all filled fields (closed at the start)
		const body = document.createElement("dl");
		body.className = "talent_card_body";
		body.hidden = true;
		for (const [field, label] of Object.entries(componentLabels)) {
			const value = component[field];
			if (!isFilled(value)) {
				continue;
			}
			const dt = document.createElement("dt");
			dt.className = "label";
			dt.textContent = label;
			const dd = document.createElement("dd");
			dd.textContent = String(value);
			body.append(dt, dd);
		}

		card.append(header, body);

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
	
	
</script>
