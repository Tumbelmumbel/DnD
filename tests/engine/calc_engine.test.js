// Tests of Assets/calc_engine.js with the real rules from tests/baseline/baseline.sql.
// Run: node --test tests/engine/
// Uses only the test runner that is built into Node.js (no npm packages).
"use strict";

const test = require("node:test");
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");

const root = path.join(__dirname, "..", "..");
const engine = require(path.join(root, "Assets", "calc_engine.js"));

// ---- Read the rows of calculation_templates from the baseline ----
const sql = fs.readFileSync(path.join(root, "tests", "baseline", "baseline.sql"), "utf8");
const block = sql.split("INSERT INTO `calculation_templates`")[1].split(";\n")[0];
const rowRe = /\((\d+), '(\w+)', (NULL|\d+), (\d+), '([^']+)', '(\w+)', (NULL|'[-\d.]+'), (NULL|'[^']*'), (NULL|'(?:[^'\\]|\\.)*')\)/g;
const allRules = [];
let m;
while ((m = rowRe.exec(block))) {
	allRules.push({
		id: +m[1],
		source_type: m[2],
		item_id: m[3] === "NULL" ? null : +m[3],
		sort_order: +m[4],
		target: m[5],
		operator: m[6],
		value_num: m[7] === "NULL" ? null : +m[7].slice(1, -1),
		value_ref: m[8] === "NULL" ? null : m[8].slice(1, -1),
		condition: m[9] === "NULL" ? null : JSON.parse(m[9].slice(1, -1).replace(/\\"/g, '"'))
	});
}
const baseRules = allRules.filter(r => r.source_type === "base");
// The rules of one item, with the item as source (like inventoryRules() in sandbox.js)
function itemRules(itemId, equipped, tags) {
	return allRules.filter(r => r.item_id === itemId).map(r => Object.assign({}, r, { source: { equipped, tags } }));
}

const abilities = ["strength", "dexterity", "constitution", "intelligence", "wisdom", "charisma"];
function inputs(scores, level) {
	const values = { "char.level": level };
	for (const a of abilities) values[`ability.${a}`] = scores[a] ?? 10;
	return values;
}
function calc(rules, scores, level, context) {
	return engine.calculate(rules, inputs(scores, level), context || {});
}

test("the baseline has 100 rules: 93 base rules and 7 item rules", () => {
	assert.equal(allRules.length, 100);
	assert.equal(baseRules.length, 93);
});

test("ability modifier for all scores 1 to 30", () => {
	for (let score = 1; score <= 30; score++) {
		for (const a of abilities) {
			assert.equal(calc(baseRules, { [a]: score }, 1)[`mod.${a}`], Math.floor((score - 10) / 2), `${a} ${score}`);
		}
	}
});

test("proficiency bonus for levels 1 to 20", () => {
	const table = [2, 2, 2, 2, 3, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 6, 6, 6, 6];
	for (let level = 1; level <= 20; level++) {
		assert.equal(calc(baseRules, {}, level)["char.prof_bonus"], table[level - 1], `level ${level}`);
	}
});

test("default values: all scores 10, level 1", () => {
	const v = calc(baseRules, {}, 1);
	assert.equal(v["combat.initiative"], 0);
	assert.equal(v["combat.ac"], 10);
	assert.equal(v["skill.stealth"], 0);
	assert.equal(v["passive.perception"], 10);
});

test("saves and skills use the right ability and the proficiency", () => {
	let v = calc(baseRules, { dexterity: 14 }, 5, { proficient: new Set(["skill.stealth"]) });
	assert.equal(v["char.prof_bonus"], 3);
	assert.equal(v["skill.stealth"], 5);
	assert.equal(v["skill.acrobatics"], 2);
	assert.equal(v["save.dexterity"], 2);
	assert.equal(v["combat.initiative"], 2);
	assert.equal(v["combat.ac"], 12);
	v = calc(baseRules, { wisdom: 16, charisma: 6 }, 1);
	assert.equal(v["skill.perception"], 3);
	assert.equal(v["skill.persuasion"], -2);
	assert.equal(v["skill.arcana"], 0);
});

test("passive skills are 10 plus the skill", () => {
	const v = calc(baseRules, { wisdom: 16, intelligence: 8 }, 5, { proficient: new Set(["skill.perception"]) });
	assert.equal(v["passive.perception"], 16); // 10 + 3 + 3
	assert.equal(v["passive.insight"], 13);
	assert.equal(v["passive.investigation"], 9);
});

test("armor and shield rules of the items", () => {
	// Item 3 (Scale Mail, medium armor): AC 14 + Dexterity modifier, at most +2
	let v = calc(baseRules.concat(itemRules(3, true, ["armor", "medium_armor"])), { dexterity: 18 }, 1);
	assert.equal(v["combat.ac"], 16);
	// Item 2 (Chain Mail, heavy armor): AC 16, no Dexterity
	v = calc(baseRules.concat(itemRules(2, true, ["armor", "heavy_armor"])), { dexterity: 18 }, 1);
	assert.equal(v["combat.ac"], 16);
	// Item 1 (Shield): +2 only when equipped
	assert.equal(calc(baseRules.concat(itemRules(1, true, ["shield"])), {}, 1)["combat.ac"], 12);
	assert.equal(calc(baseRules.concat(itemRules(1, false, ["shield"])), {}, 1)["combat.ac"], 10);
});

test("ring and necklace add to their value", () => {
	const rules = baseRules.concat(itemRules(4, true, ["magic"]), itemRules(5, true, ["magic"]));
	const v = calc(rules, { wisdom: 12, charisma: 12 }, 1);
	assert.equal(v["mod.wisdom"], 2);
	assert.equal(v["skill.perception"], 2); // uses the new modifier
	assert.equal(v["skill.persuasion"], 2);
});

test("weapon rules: ability, attack roll and damage bonus", () => {
	const str16dex14 = { strength: 16, dexterity: 14 };
	function weapon(tags, proficient) {
		return calc(baseRules, str16dex14, 1, { weapon: { tags, proficient } });
	}
	let v = weapon(["weapon", "martial", "melee", "versatile"], false); // Longsword
	assert.equal(v["weapon.ability"], 3);
	assert.equal(v["weapon.attack"], 3);
	assert.equal(v["weapon.damage_bonus"], 3);
	v = weapon(["weapon", "simple", "ranged"], true); // Light Crossbow
	assert.equal(v["weapon.ability"], 2);
	assert.equal(v["weapon.attack"], 4);
	v = calc(baseRules, { strength: 16, dexterity: 18 }, 1, { weapon: { tags: ["weapon", "finesse"], proficient: true } }); // Dagger
	assert.equal(v["weapon.ability"], 4);
	assert.equal(v["weapon.attack"], 6);
	assert.equal(v["weapon.damage_bonus"], 4);
	// Without a weapon in the context, the weapon checks are false
	v = calc(baseRules, str16dex14, 1);
	assert.equal(v["weapon.attack"], 3);
});

test("engine features: operators and checks", () => {
	const one = (rules, values, context) => engine.calculate(rules, values || {}, context || {});
	assert.equal(one([{ id: 1, sort_order: 1, target: "x", operator: "set", value_num: 7 }, { id: 2, sort_order: 2, target: "x", operator: "div", value_num: 0 }]).x, 7);
	assert.equal(one([{ id: 1, sort_order: 1, target: "x", operator: "min", value_num: 19 }], { x: 12 }).x, 19);
	assert.equal(one([{ id: 1, sort_order: 1, target: "x", operator: "max", value_num: 2 }], { x: 5 }).x, 2);
	assert.equal(one([{ id: 1, sort_order: 1, target: "x", operator: "set", value_num: 3, condition: { not: { PROFICIENT: "skill.stealth" } } }]).x, 3);
	assert.equal(one([{ id: 1, sort_order: 1, target: "x", operator: "set", value_num: 3, condition: { UNKNOWN_CHECK: true } }]).x, undefined);
	assert.equal(one([{ id: 1, sort_order: 1, target: "x", operator: "set", value_num: 1, condition: { COMPARE: ["char.level", ">=", 5] } }], { "char.level": 5 }).x, 1);
	assert.equal(one([{ id: 1, sort_order: 1, target: "x", operator: "set", value_num: 1, condition: { HAS_DEFENSE: ["condition", "poisoned"] } }], {}, { defenses: new Set(["condition:poisoned"]) }).x, 1);
});
