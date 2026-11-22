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
?>

<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/Assets/style.css">
    <title>Sandbox</title>
</head>
<body>
<div class = "container_vertical" id = "character_basics">
	<div class = "container_horizontal" style = "align-items: flex-start; gap: 10px; flex-wrap: wrap">
		<div class = "container_vertical">
			<label class= "label section_header" for="class_dropdown">Basics</label>
			<div class = "container_horizontal">
				<label class= "label" for="class_dropdown">Character Name</label>
				<input class= "input" name="name" id = "name"></input>
			</div>
			<div class = "container_horizontal">
				<label class= "label" for="species">Species</label>
				<select class = "class_dropdown" id = "species" name = "species">
					<?php foreach ($species as $char_species): ?>
						<option id="<?= $char_species ?>" class = "Dropdown_Entry" value = "<?= $char_species ?>"> <?= $char_species ?> </option>
					<?php endforeach; ?>
				</select>
			</div>			
			<div class = "container_horizontal">
				<label class= "label" for="Level">Level</label>
				<input class= "input" name="Level" id = "Level"></input>			
			</div>
			<div class = "container_horizontal">
				<label class= "label" for="class_dropdown">Class</label>
				<select class = "class_dropdown" id = "class_dropdown" name = "class_dropdown">
					<?php foreach ($classes as $class): ?>
						<option id="<?= $class ?>" class = "Dropdown_Entry" value = "<?= $class ?>"> <?= $class ?> </option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class = "container_horizontal">
				<label class= "label" for="subclass_dropdown">Subclass</label>
				<select class = "class_dropdown" id = "subclass_dropdown" name = "subclass_dropdown"> </select>
			</div>

			<div class = "container_horizontal">
				<label class= "label" for="backgrounds">Backgrounds</label>
				<select class = "class_dropdown" id = "backgrounds" name = "backgrounds">
					<?php foreach ($backgrounds as $background): ?>
						<option id="<?= $background ?>" class = "Dropdown_Entry" value = "<?= $background ?>"> <?= $background ?> </option>
					<?php endforeach; ?>
				</select>	
			</div>
		</div>

		<div class = "container_vertical" id = "Defense">
			<label class= "label section_header">Defense</label>
			<button class = "button add" style = "align-items: flex-start;" id = "addDamageImmunity"> + </button>
			<div class = "container_horizontal" id = "defense_line0">
					<select class = "class_dropdown" name="damageInfluence">
						<option class = "Dropdown_Entry"></option>
						<?php foreach ($damageInfluences as $damageInfluence): ?>
							<option class = "Dropdown_Entry" value = "<?= $damageInfluence ?>"> <?= $damageInfluence ?> </option>
						<?php endforeach; ?>
					</select>
					<select class = "class_dropdown" name="damageType">
						<option class = "Dropdown_Entry"></option>
						<?php foreach ($damageTypes as $damageType): ?>
							<option class = "Dropdown_Entry" value = "<?= $damageType ?>"> <?= $damageType ?> </option>
						<?php endforeach; ?>
					</select>
				<button class = "button delete defense"> - </button>
			</div>	
		</div>
		<div class = "container_vertical" id = "Condition">
			<label class= "label section_header">Conditions</label>
			<button class = "button add" style = "align-items: flex-start;" id = "addCondition"> + </button>
			<div class = "container_horizontal" Style = "align-items: flex-start;" id = "condition_line0">
				<select class = "class_dropdown" name="conditions">
					<option class = "Dropdown_Entry"></option>
					<?php foreach ($conditions as $condition): ?>
						<option class = "Dropdown_Entry" name="<?= $condition ?>" style = "align-self: flex-end"><?= $condition ?></option>
					<?php endforeach; ?>	
				</select>
				<button class = "button delete condition"> - </button>
			</div>
		</div>

	</div>
	<div class = "container_horizontal" style = "align-items: flex-start; gap: 10px; flex-wrap: wrap">
		<div class = "container_vertical">
			<div class = "container_horizontal" >
				<label class= "label section_header" for="currentHitpoints">Hitpoints</label>
				<input class= "input" name="currentHitpoints" id = "currentHitpoints"></input>
			</div>
			<div class = "container_horizontal" >
				<label class= "label" for="maxHitpoints">Maximum Hitpoints</label>
				<input class= "input" name="maxHitpoints" id = "maxHitpoints"></input>	
			</div>
			<div class = "container_horizontal" >
				<label class= "label" for="tempHitpoints">Temporary Hitpoints</label>
				<input class= "input" name="tempHitpoints" id = "tempHitpoints"></input>
			</div>
			<div class = "container_horizontal" >
				<label class= "label" for="hitDice">Hit dice</label>
				<label class= "skill_modifier" name="hitDice" id = "hitDice"></label>		
			</div>
			<div class = "container_horizontal" >
				<label class= "label" for="HitDiceUsed">Used</label>
				<input class= "input" name="HitDiceUsed" id = "HitDiceUsed"></input>	
			</div>
		</div>
		<div class = "container_vertical" style = "align-items: flex-start; gap: 10px; flex-wrap: wrap">
			<div class = "container_horizontal" >
				<label class= "label section_header" for="initiative">Initiative</label>
				<label class= "ability_modifier" name="initiative" id = "initiative"></label>		
			</div>
			<div class = "container_horizontal" >
				<label class= "label section_header" for= "Walking_Speed"  >Walking Speed</label>
				<input class="ability_modifier" type = "number" name="Walking_Speed" id = "Walking_Speed"></input>
			</div>
			<div class = "container_horizontal" >
				<label class= "label section_header" for= "Proficiency_Bonus"  >Proficiency Bonus</label>
				<input class="ability_modifier" type = "number" name="Proficiency_Bonus" id = "Proficiency_Bonus"></input>
			</div>
			<div class = "container_horizontal" >
				<label class= "label section_header" for="inspiration"  >Inspiration</label>
				<select class = "ability_modifier" id = "inspiration" name = "inspiration">
					<option id="empty" class = "Dropdown_Entry" value = ""> </option>
					<option id="D4" class = "Dropdown_Entry" value = "D4"> D4 </option>
					<option id="D6" class = "Dropdown_Entry" value = "D6"> D6 </option>
					<option id="D8" class = "Dropdown_Entry" value = "D8"> D8 </option>
					<option id="D12" class = "Dropdown_Entry" value = "D12"> D12 </option>					
				</select>			
			</div>
		</div>
		<div class = "container_vertical" >
				<div class = "container_horizontal" >
					<label class= "label section_header" for="armorClass">Armor Class</label>
					<input class= "input ability" name="armorClass" id = "armorClass"></input>	
				</div>
				<div class = "container_horizontal" >
					<label class= "label" for="armor">Armor</label>
					<select class = "class_dropdown" name="armor" id = "armor"></select>	
				</div>
				<div class = "container_horizontal" >
					<label class= "label" for="lightArmorProficiency">Light Armor Proficiency</label>
					<input type = "checkbox" name="lightArmorProficiency" id = "lightArmorProficiency"></input>	
				</div>
				<div class = "container_horizontal" >
					<label class= "label" for="mediumArmorProficiency">Medium Armor Proficiency</label>
					<input type = "checkbox" name="mediumArmorProficiency" id = "mediumArmorProficiency"></input>	
				</div>
				<div class = "container_horizontal" >
					<label class= "label" for="heavyArmorProficiency">Heavy Armor Proficiency</label>
					<input type = "checkbox" name="heavyArmorProficiency" id = "heavyArmorProficiency"></input>	
				</div>
				<div class = "container_horizontal" >
					<label class= "label" for="shield">Shield</label>
					<select class = "class_dropdown" name="shield" id = "shield"></select>	
				</div>
				<div class = "container_horizontal" >
					<label class= "label" for="shieldProficiency">Shield Proficiency</label>
					<input type = "checkbox" name="shieldProficiency" id = "shieldProficiency"></input>	
				</div>
		</div>
	</div>
		




					
	<div class = "container_horizontal" style = "align-items: flex-start; gap: 10px; flex-wrap: wrap">

			<?php foreach ($abilities as $ability => $skills): ?>
			<div class = "container_vertical" style = "max-width: 230px;">
				<label class= "label section_header" for="<?= $ability ?>"><?= $ability ?></label>
				<output class= "ability_modifier" type = "number" name="<?= $ability ?>_Modifier" id = "<?= $ability ?>_Modifier">0</output>
				<input class= "input ability" name="<?= $ability ?>" id = "<?= $ability ?>"></input>
				<div class = "container_horizontal skill">
						<label class="label" style = "min-width: 115px;">Savingthrow</label>
						<output class="skill_modifier" name="<?= $ability ?>_Savingthrow" id = "<?= $ability ?>_Savingthrow">0</output>
						<input type = "checkbox" name="<?= $ability ?>_Savingthrow_Proficiency" id = "<?= $ability ?>_Savingthrow_Proficiency" style = "align-self: flex-end"> </input>
				</div>
				<?php
				foreach ($skills as $skill): ?>
					<div class = "container_horizontal skill">
						<label class="label" style = "min-width: 115px;"><?= $skill ?></label>
						<output class="skill_modifier" type = "number" name="<?= $skill ?>" id = "<?= $skill ?>">0</output>
						<input type = "checkbox" name="<?= $skill ?>_Proficiency" id = "<?= $skill ?>_Proficiency" style = "align-self: flex-end"> </input>
					</div>			
				<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
	</div>

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
	
	
</script>
