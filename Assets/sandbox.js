// sandbox.js – logic of the character sheet (CharacterSandbox.php).
// The page defines the data from PHP before this file: I18N, CSRF_TOKEN, abilities, classes, subclasses.

// --- Languages ---

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


// --- State ---
let class_set = {char_class: "", char_subclass: ""};
let level;
let immunitiesCounter = 0;

// An empty or wrong ability score counts as 10 (modifier 0); an empty or wrong level counts as 1
const DEFAULT_ABILITY_SCORE = 10;
const DEFAULT_LEVEL = 1;

// Inventory: [{ uid, itemId, quantity, equipped, rules }]. "rules" is the copy of the item's rules (itemCatalog from PHP).
// It stays in the browser for now; the save function stores it later in character_items and character_calculations.
let inventory = [];
const itemById = new Map(itemCatalog.map(item => [item.id, item]));

//DEFENSE AND CONDITIONS: each entry is a chip; the chip stores only codes
const defenseChips = document.getElementById("defenseChips");
const defenseMessage = document.getElementById("defenseMessage");
const chipForms = {
	defense: { button: document.getElementById("addDamageImmunity"), form: document.getElementById("defenseForm") },
	condition: { button: document.getElementById("addCondition"), form: document.getElementById("conditionForm") }
};

// Name of a code in a list (the lists get the new names after a change of the language)
function optionName(selectId, code) {
	const option = [...document.getElementById(selectId).options].find(o => o.value === code);
	return option ? option.textContent : code;
}

function chipText(chip) {
	if (chip.dataset.kind === "defense") {
		return optionName("defenseInfluence", chip.dataset.influence) + ": " + optionName("defenseDamageType", chip.dataset.damagetype);
	}
	return optionName("conditionSelect", chip.dataset.condition);
}

// Write the names of all chips again (after an add or a change of the language)
function renderChipNames() {
	defenseChips.querySelectorAll(".cs-chip").forEach(chip => {
		const text = chipText(chip);
		chip.querySelector(".cs-chip-text").textContent = text;
		chip.querySelector(".cs-chip-remove").setAttribute("aria-label", tr("remove_entry").replace("{name}", text));
	});
	document.getElementById("defenseEmpty").hidden = defenseChips.children.length > 0;
}

function openChipForm(kind) {
	for (const [key, part] of Object.entries(chipForms)) {
		const open = key === kind && part.form.hidden;
		part.form.hidden = !open;
		part.button.setAttribute("aria-expanded", open ? "true" : "false");
		if (open) part.form.querySelector("select").focus();
	}
	defenseMessage.textContent = "";
}

function addChip(data, key) {
	if (defenseChips.querySelector(`[data-key="${CSS.escape(key)}"]`)) {
		defenseMessage.textContent = tr("defense.duplicate");
		return false;
	}
	const chip = document.createElement("li");
	chip.className = "cs-chip";
	chip.dataset.key = key;
	Object.assign(chip.dataset, data);
	const text = document.createElement("span");
	text.className = "cs-chip-text";
	const remove = document.createElement("button");
	remove.type = "button";
	remove.className = "cs-chip-remove";
	remove.textContent = "×";
	chip.append(text, remove);
	defenseChips.appendChild(chip);
	defenseMessage.textContent = "";
	renderChipNames();
	return true;
}

chipForms.defense.button.addEventListener("click", () => openChipForm("defense"));
chipForms.condition.button.addEventListener("click", () => openChipForm("condition"));

document.getElementById("defenseAdd").addEventListener("click", () => {
	const influence = document.getElementById("defenseInfluence");
	const damageType = document.getElementById("defenseDamageType");
	if (!influence.value || !damageType.value) {
		defenseMessage.textContent = tr("defense.choose_both");
		return;
	}
	if (addChip({ kind: "defense", influence: influence.value, damagetype: damageType.value },
		"defense:" + influence.value + ":" + damageType.value)) {
		influence.value = "";
		damageType.value = "";
		openChipForm(null);
		chipForms.defense.button.focus();
	}
});

document.getElementById("conditionAdd").addEventListener("click", () => {
	const condition = document.getElementById("conditionSelect");
	if (!condition.value) {
		defenseMessage.textContent = tr("defense.choose_condition");
		return;
	}
	if (addChip({ kind: "condition", condition: condition.value }, "condition:" + condition.value)) {
		condition.value = "";
		openChipForm(null);
		chipForms.condition.button.focus();
	}
});

defenseChips.addEventListener("click", e => {
	const remove = e.target.closest(".cs-chip-remove");
	if (!remove) return;
	const chip = remove.closest(".cs-chip");
	const next = chip.nextElementSibling || chip.previousElementSibling;
	chip.remove();
	renderChipNames();
	(next ? next.querySelector(".cs-chip-remove") : chipForms.defense.button).focus();
});

// --- Calculation (engine: Assets/calc_engine.js, rules: calcRules from CharacterSandbox.php) ---
function numberOr(id, fallback) {
	const v = parseInt(document.getElementById(id).value, 10);
	return Number.isNaN(v) ? fallback : v;
}

// A modifier with its sign: "+2", "+0", "-1"
function signed(n) {
	return (n >= 0 ? "+" : "") + n;
}

// The copied rules of the items in the inventory. The source of each rule is its inventory row (for EQUIPPED and HAS_TAG).
function inventoryRules() {
	const rules = [];
	for (const entry of inventory) {
		const item = itemById.get(entry.itemId);
		const source = { equipped: entry.equipped, tags: item ? item.tags : [] };
		for (const rule of entry.rules) rules.push(Object.assign({}, rule, { source }));
	}
	return rules;
}

// Collect the inputs of the sheet, calculate all values and write them into the fields with data-calc
function recalc() {
	const inputs = { "char.level": numberOr("Level", DEFAULT_LEVEL) };
	const proficient = new Set();
	for (const [ability, skills] of Object.entries(abilities)) {
		inputs[`ability.${ability}`] = numberOr(`ability_${ability}`, DEFAULT_ABILITY_SCORE);
		if (document.getElementById(`save_${ability}_prof`).checked) proficient.add(`save.${ability}`);
		for (const skill of skills) {
			if (document.getElementById(`skill_${skill}_prof`).checked) proficient.add(`skill.${skill}`);
		}
	}
	// Defenses and conditions as "kind:code", for example "resistant:fire" or "condition:poisoned"
	const defenses = new Set();
	defenseChips.querySelectorAll(".cs-chip").forEach(chip => {
		defenses.add(chip.dataset.kind === "defense"
			? `${chip.dataset.influence}:${chip.dataset.damagetype}`
			: `condition:${chip.dataset.condition}`);
	});

	const values = CalcEngine.calculate(calcRules.concat(inventoryRules()), inputs, { proficient, defenses });
	document.querySelectorAll("[data-calc]").forEach(el => {
		const value = values[el.dataset.calc];
		const text = value === undefined ? "–" : (el.dataset.calcFormat === "plain" ? String(value) : signed(value));
		if (el.tagName === "INPUT") el.value = text;
		else el.textContent = text;
	});
}

// Calculate again on each change: ability scores, level, proficiency boxes (input/change) and defense chips (list changes)
document.addEventListener("input", recalc);
document.addEventListener("change", recalc);
new MutationObserver(recalc).observe(defenseChips, { childList: true });
recalc();

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


//INVENTORY: + Item opens a search box; a tap on an item adds it as "Carried". Equipped / Carried and × change the list.
	const inventoryList    = document.getElementById("inventoryList");
	const inventoryAdd     = document.getElementById("inventoryAdd");
	const inventoryPicker  = document.getElementById("inventoryPicker");
	const inventorySearch  = document.getElementById("inventorySearch");
	const inventoryResults = document.getElementById("inventoryResults");
	let inventoryUid = 0;

	// A weight in pounds, for example "45 lb" or "0.25 lb"
	function poundsText(lb) {
		return `${Math.round(lb * 100) / 100} lb`;
	}

	// Name of the rarity, only for magic items (not "mundane")
	function rarityText(item) {
		return item.rarity && item.rarity !== "mundane" ? ((itemLookup.item_rarity || {})[item.rarity] || item.rarity) : "";
	}

	function tagText(code) {
		return (itemLookup.item_tag || {})[code] || code;
	}

	// Number of rows and total weight in the head of the card
	function updateInventorySummary() {
		let weight = 0;
		for (const entry of inventory) weight += (itemById.get(entry.itemId).weight || 0) * entry.quantity;
		document.getElementById("inventorySummary").textContent = inventory.length === 0 ? "" :
			tr("inventory.summary").replace("{n}", inventory.length).replace("{w}", Math.round(weight * 100) / 100);
	}

	function inventoryRow(entry) {
		const item = itemById.get(entry.itemId);
		const row = document.createElement("li");
		row.className = "cs-inventory-row" + (entry.equipped ? " is-equipped" : "");
		row.dataset.uid = entry.uid;

		const equip = document.createElement("button");
		equip.type = "button";
		equip.className = "cs-inventory-equip";
		equip.dataset.action = "equip";
		equip.setAttribute("aria-pressed", entry.equipped ? "true" : "false");
		equip.textContent = tr(entry.equipped ? "inventory.equipped" : "inventory.carried");
		equip.setAttribute("aria-label", equip.textContent + ": " + item.name);

		const info = document.createElement("span");
		info.className = "cs-inventory-name";
		const title = document.createElement("span");
		const name = document.createElement("strong");
		name.textContent = item.name;
		title.append(name);
		if (rarityText(item)) {
			const rarity = document.createElement("span");
			rarity.className = "cs-inventory-rarity";
			rarity.textContent = " · " + rarityText(item);
			title.append(rarity);
		}
		const tags = document.createElement("span");
		tags.className = "cs-inventory-tags";
		for (const code of item.tags) {
			const tag = document.createElement("span");
			tag.className = "cs-inventory-tag";
			tag.textContent = tagText(code);
			tags.append(tag);
		}
		info.append(title, tags);

		const quantity = document.createElement("input");
		quantity.className = "cs-inventory-qty";
		quantity.inputMode = "numeric";
		quantity.value = String(entry.quantity);
		quantity.dataset.action = "quantity";
		quantity.setAttribute("aria-label", tr("inventory.quantity_of").replace("{name}", item.name));

		const weight = document.createElement("span");
		weight.className = "cs-inventory-weight";
		weight.textContent = item.weight === null ? "–" : poundsText(item.weight * entry.quantity);

		const remove = document.createElement("button");
		remove.type = "button";
		remove.className = "cs-chip-remove";
		remove.dataset.action = "remove";
		remove.textContent = "×";
		remove.setAttribute("aria-label", tr("remove_entry").replace("{name}", item.name));

		row.append(equip, info, quantity, weight, remove);
		return row;
	}

	function renderInventory() {
		inventoryList.replaceChildren(...inventory.map(inventoryRow));
		document.getElementById("inventoryEmpty").hidden = inventory.length > 0;
		document.querySelector(".cs-inventory-cols").hidden = inventory.length === 0;
		updateInventorySummary();
	}

	// Add an item: a new inventory row with a copy of the item's rules
	function addItem(itemId) {
		const item = itemById.get(itemId);
		if (!item) return;
		const entry = { uid: ++inventoryUid, itemId, quantity: 1, equipped: false, rules: item.rules.map(rule => Object.assign({}, rule)) };
		inventory.push(entry);
		renderInventory();
		recalc();
		inventoryList.querySelector(`[data-uid="${entry.uid}"] .cs-inventory-equip`).focus();
	}

	function entryOf(element) {
		const row = element.closest(".cs-inventory-row");
		return row ? inventory.find(entry => String(entry.uid) === row.dataset.uid) : null;
	}

	inventoryList.addEventListener("click", function (event) {
		const button = event.target.closest("button[data-action]");
		const entry = button ? entryOf(button) : null;
		if (!entry) return;
		if (button.dataset.action === "equip") {
			entry.equipped = !entry.equipped;
			renderInventory();
			recalc();
			inventoryList.querySelector(`[data-uid="${entry.uid}"] .cs-inventory-equip`).focus();
		} else if (button.dataset.action === "remove") {
			const index = inventory.indexOf(entry);
			inventory.splice(index, 1); // the copied rules go with the row
			renderInventory();
			recalc();
			const next = inventoryList.querySelectorAll(".cs-chip-remove")[Math.min(index, inventory.length - 1)];
			(next || inventoryAdd).focus();
		}
	});

	// Quantity: 1 or more. While typing only valid numbers count; leaving the field shows the stored number again.
	inventoryList.addEventListener("input", function (event) {
		if (event.target.dataset.action !== "quantity") return;
		const entry = entryOf(event.target);
		const value = parseInt(event.target.value, 10);
		if (!entry || Number.isNaN(value) || value < 1) return;
		entry.quantity = value;
		const item = itemById.get(entry.itemId);
		event.target.closest(".cs-inventory-row").querySelector(".cs-inventory-weight").textContent =
			item.weight === null ? "–" : poundsText(item.weight * value);
		updateInventorySummary();
	});
	inventoryList.addEventListener("change", function (event) {
		if (event.target.dataset.action !== "quantity") return;
		const entry = entryOf(event.target);
		if (entry) event.target.value = String(entry.quantity);
	});

	// Search box: all items whose name contains the text
	function renderResults() {
		const query = inventorySearch.value.trim().toLocaleLowerCase();
		const found = itemCatalog.filter(item => item.name.toLocaleLowerCase().includes(query));
		inventoryResults.replaceChildren(...found.map(item => {
			const li = document.createElement("li");
			const button = document.createElement("button");
			button.type = "button";
			button.className = "cs-inventory-result";
			button.dataset.id = item.id;
			const name = document.createElement("strong");
			name.textContent = item.name;
			const info = document.createElement("span");
			info.textContent = [rarityText(item)].concat(item.tags.map(tagText)).filter(text => text !== "").join(" · ");
			button.append(name, info);
			li.append(button);
			return li;
		}));
		if (found.length === 0) {
			const li = document.createElement("li");
			li.className = "cs-chips-empty";
			li.textContent = tr("inventory.no_match");
			inventoryResults.append(li);
		}
	}

	function setPicker(open) {
		inventoryPicker.hidden = !open;
		inventoryAdd.setAttribute("aria-expanded", open ? "true" : "false");
		if (open) {
			inventorySearch.value = "";
			renderResults();
			inventorySearch.focus();
		}
	}
	inventoryAdd.addEventListener("click", () => setPicker(inventoryPicker.hidden));
	inventorySearch.addEventListener("input", renderResults);
	inventoryResults.addEventListener("click", function (event) {
		const button = event.target.closest(".cs-inventory-result");
		if (!button) return;
		setPicker(false);
		addItem(Number(button.dataset.id));
	});
	inventoryPicker.addEventListener("keydown", function (event) {
		if (event.key === "Escape") {
			setPicker(false);
			inventoryAdd.focus();
		}
	});
	document.addEventListener("pointerdown", function (event) {
		if (!inventoryPicker.hidden && !inventoryPicker.contains(event.target) && !inventoryAdd.contains(event.target)) {
			setPicker(false);
		}
	});
	renderInventory();


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
		const sub = [barText("species"), barText("class_dropdown"), barText("subclass_dropdown"),
			barText("Level") ? tr("level") + " " + barText("Level") : "", barText("backgrounds")]
			.filter(t => t !== "").join(" · ");
		document.getElementById("bar_sub").textContent = sub;
		const hpCurrent = hpValue("currentHitpoints");
		const hpTemp = Math.max(0, hpValue("tempHitpoints") ?? 0);
		const hpShown = document.getElementById("bar_hp_current");
		hpShown.textContent = hpCurrent === null ? (hpTemp > 0 ? String(hpTemp) : "–") : String(hpCurrent + hpTemp);
		hpShown.classList.toggle("cs-hp-temp", hpTemp > 0);
		document.getElementById("bar_hp_max").textContent = barText("maxHitpoints") || "–";
		document.getElementById("bar_hp_note").textContent = hpTemp > 0 ? " " + tr("hp.includes_temp").replace("{n}", hpTemp) : "";
		document.getElementById("bar_speed").textContent = barText("Walking_Speed") || "–";
		document.getElementById("bar_inspiration").textContent = barText("inspiration") || "–";
	}

	// Hit point values as numbers (null if the field is empty)
	function hpValue(id) {
		const v = parseInt(document.getElementById(id).value, 10);
		return Number.isNaN(v) ? null : v;
	}
	function setHp(id, value) {
		const field = document.getElementById(id);
		if (field.value !== String(value)) {
			field.value = String(value);
			field.dispatchEvent(new Event("input", { bubbles: true }));
		}
	}

	// Rules: current hit points are 0 to maximum; temporary hit points are 0 or more
	function limitHitpoints() {
		const max = hpValue("maxHitpoints");
		const current = hpValue("currentHitpoints");
		const temp = hpValue("tempHitpoints");
		if (current !== null && current < 0) setHp("currentHitpoints", 0);
		if (current !== null && max !== null && current > max) setHp("currentHitpoints", max);
		if (temp !== null && temp < 0) setHp("tempHitpoints", 0);
	}
	// Current and temporary: check while typing. Maximum: check when the field is left (else "2" of "25" would cut the current value)
	document.getElementById("currentHitpoints").addEventListener("input", limitHitpoints);
	document.getElementById("tempHitpoints").addEventListener("input", limitHitpoints);
	document.getElementById("maxHitpoints").addEventListener("change", limitHitpoints);

	// −: temporary hit points go down first, then current hit points. +: current hit points go up to the maximum
	function changeHitpoints(delta) {
		const max = hpValue("maxHitpoints");
		const temp = hpValue("tempHitpoints") ?? 0;
		if (delta < 0 && temp > 0) {
			setHp("tempHitpoints", temp - 1);
			return;
		}
		let value = hpValue("currentHitpoints");
		if (value === null) value = max ?? 0; // start from the maximum
		value += delta;
		if (value < 0) value = 0;
		if (max !== null && value > max) value = max;
		setHp("currentHitpoints", value);
	}

	document.getElementById("hpMinus").addEventListener("click", () => changeHitpoints(-1));
	document.getElementById("hpPlus").addEventListener("click", () => changeHitpoints(1));

	// Update the bar on every change of a field; the subclass list is filled later by the endpoint
	document.addEventListener("input", updateBar);
	document.addEventListener("change", updateBar);
	new MutationObserver(updateBar).observe(document.getElementById("subclass_dropdown"), { childList: true });
	updateBar();


//IDENTITY PANEL: a tap on the name in the bar opens the fields; Done, Esc or a tap outside closes them
	const identityButton = document.getElementById("identityButton");
	const identityPanel = document.getElementById("identityPanel");

	function setIdentityPanel(open, returnFocus) {
		identityPanel.hidden = !open;
		identityButton.setAttribute("aria-expanded", open ? "true" : "false");
		if (open) {
			document.getElementById("name").focus();
		} else if (returnFocus) {
			identityButton.focus();
		}
	}
	identityButton.addEventListener("click", () => setIdentityPanel(identityPanel.hidden, true));
	document.getElementById("identityDone").addEventListener("click", () => setIdentityPanel(false, true));
	identityPanel.addEventListener("keydown", function (event) {
		if (event.key === "Escape") setIdentityPanel(false, true);
	});
	document.addEventListener("pointerdown", function (event) {
		if (!identityPanel.hidden && !identityPanel.contains(event.target) && !identityButton.contains(event.target)) {
			setIdentityPanel(false, false);
		}
	});


//BAR VALUES: speed, proficiency bonus and inspiration change only in the box "statPanel"
	const statPanel = document.getElementById("statPanel");
	const statOpeners = [...document.querySelectorAll(".cs-bar-edit[data-field]"), document.getElementById("barMore")];
	let statOpener = null;

	// Put a box under its button, inside the window
	function placeUnder(panel, opener) {
		const r = opener.getBoundingClientRect();
		const width = panel.offsetWidth;
		const maxLeft = document.documentElement.clientWidth - width - 8;
		panel.style.left = Math.max(8, Math.min(r.left + r.width / 2 - width / 2, maxLeft)) + "px";
	}

	// opener = the button that opens the box (null closes it); a value button shows one field, "More" shows all fields
	function setStatPanel(opener, returnFocus) {
		const last = statOpener;
		statOpener = opener;
		statPanel.hidden = !opener;
		statOpeners.forEach(b => b.setAttribute("aria-expanded", b === opener ? "true" : "false"));
		if (opener) {
			const field = opener.dataset.field || "";
			statPanel.querySelectorAll("[data-stat]").forEach(f => { f.hidden = field !== "" && f.dataset.stat !== field; });
			placeUnder(statPanel, opener);
			statPanel.querySelector("[data-stat]:not([hidden]) input, [data-stat]:not([hidden]) select").focus();
		} else if (returnFocus && last) {
			last.focus();
		}
	}
	statOpeners.forEach(b => b.addEventListener("click", () => setStatPanel(statOpener === b ? null : b, true)));
	document.getElementById("statDone").addEventListener("click", () => setStatPanel(null, true));
	statPanel.addEventListener("keydown", function (event) {
		if (event.key === "Escape") setStatPanel(null, true);
	});
	document.addEventListener("pointerdown", function (event) {
		if (statOpener && !statPanel.contains(event.target) && !statOpeners.some(b => b.contains(event.target))) {
			setStatPanel(null, false);
		}
	});


//MENUS IN THE BAR: hit points and armor. A tap on the value opens the fields; Done, Esc, a tap outside or a second tap closes them
	function barMenu(button, panel, doneId, firstFieldId, onClose) {
		function setOpen(open, returnFocus) {
			panel.hidden = !open;
			button.setAttribute("aria-expanded", open ? "true" : "false");
			if (open) {
				placeUnder(panel, button);
				document.getElementById(firstFieldId).focus();
			} else {
				if (onClose) onClose();
				if (returnFocus) button.focus();
			}
		}
		button.addEventListener("click", () => setOpen(panel.hidden, true));
		document.getElementById(doneId).addEventListener("click", () => setOpen(false, true));
		panel.addEventListener("keydown", function (event) {
			if (event.key === "Escape") setOpen(false, true);
		});
		document.addEventListener("pointerdown", function (event) {
			if (!panel.hidden && !panel.contains(event.target) && !button.contains(event.target)) {
				setOpen(false, false);
			}
		});
	}
	barMenu(document.getElementById("hpButton"), document.getElementById("hpPanel"), "hpDone", "currentHitpoints", limitHitpoints);
	barMenu(document.getElementById("armorButton"), document.getElementById("armorPanel"), "armorDone", "armorClass", null);

//LANGUAGE MENU (footer): change the texts without a reload; entered values stay
	window.onLanguageChanged = function (data) {
		I18N = data.texts;
		document.documentElement.lang = data.lang;
		applyI18n();
		renderInventory();
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
				renderChipNames();
				// Items: new names, tag and rarity names; the search list is sorted by the new names
				itemLookup = { item_tag: (content.lookup || {}).item_tag || {}, item_rarity: (content.lookup || {}).item_rarity || {} };
				for (const item of itemCatalog) {
					if (content.items && Object.prototype.hasOwnProperty.call(content.items, item.id)) item.name = content.items[item.id];
				}
				itemCatalog.sort((a, b) => a.name.localeCompare(b.name, document.documentElement.lang, { sensitivity: "base" }));
				renderInventory();
				if (!inventoryPicker.hidden) renderResults();
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
	const label = tr(hasPicture ? "picture.show" : "picture.add");
	pictureButton.setAttribute("aria-label", label);
	pictureButton.title = label;
	pictureThumb.alt = hasPicture ? tr("picture.alt") : "";
	pictureLarge.alt = hasPicture ? tr("picture.alt") : "";
}

// Error message under the bar; it disappears after 6 seconds
let pictureErrorTimer = null;
function showPictureError(text) {
	pictureError.textContent = text;
	clearTimeout(pictureErrorTimer);
	if (text) pictureErrorTimer = setTimeout(() => { pictureError.textContent = ""; }, 6000);
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
	showPictureError("");
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
		showPictureError(tr("picture.err_size"));
		return;
	}
	if (file.type !== "image/png" || !(await isPngFile(file))) {
		showPictureError(tr("picture.err_type"));
		return;
	}
	showPictureError("");
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
