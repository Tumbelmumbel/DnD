// Calculation engine of the character sheet.
// Rules and value names: see database_description.md, section "Calculations".
// The engine only calculates. It does not read or change the page, so tests can run it without a browser.
(function (global) {
	"use strict";

	// A number, or 0 for a missing or wrong value
	function num(value) {
		const n = Number(value);
		return Number.isFinite(n) ? n : 0;
	}

	// Apply one operator to the current value of the target
	function apply(operator, current, given) {
		switch (operator) {
			case "set":   return given;
			case "add":   return current + given;
			case "sub":   return current - given;
			case "mul":   return current * given;
			case "div":   return given === 0 ? current : current / given; // no division by 0
			case "min":   return Math.max(current, given); // at least the given value
			case "max":   return Math.min(current, given); // at most the given value
			case "floor": return Math.floor(current);
			default:      return current; // unknown operator: no change
		}
	}

	// COMPARE: ["char.level", ">", 5]. The right side is a number or the name of a value.
	function compare(args, values) {
		if (!Array.isArray(args) || args.length !== 3) return false;
		const left = num(values[args[0]]);
		const right = typeof args[2] === "string" ? num(values[args[2]]) : num(args[2]);
		switch (args[1]) {
			case ">":  return left > right;
			case ">=": return left >= right;
			case "<":  return left < right;
			case "<=": return left <= right;
			case "=":  return left === right;
			case "!=": return left !== right;
			default:   return false;
		}
	}

	// One check of a condition. An unknown check is false, so a wrong rule does not apply.
	function check(key, arg, rule, values, context) {
		const source = rule.source || null; // the item of the rule (inventory row), if the rule comes from an item
		switch (key) {
			case "all":         return Array.isArray(arg) && arg.every(c => test(c, rule, values, context));
			case "any":         return Array.isArray(arg) && arg.some(c => test(c, rule, values, context));
			case "not":         return !test(arg, rule, values, context);
			case "EQUIPPED":    return Boolean(source && source.equipped) === Boolean(arg);
			case "HAS_TAG":     return Boolean(source && Array.isArray(source.tags) && source.tags.includes(arg));
			case "COMPARE":     return compare(arg, values);
			case "HAS_DEFENSE": return Array.isArray(arg) && arg.length === 2 && context.defenses.has(arg[0] + ":" + arg[1]);
			case "PROFICIENT":  return context.proficient.has(arg);
			default:            return false;
		}
	}

	// A condition object: all its checks must be true. No condition: the rule always applies.
	function test(condition, rule, values, context) {
		if (condition === null || condition === undefined) return true;
		if (typeof condition !== "object" || Array.isArray(condition)) return false;
		return Object.keys(condition).every(key => check(key, condition[key], rule, values, context));
	}

	// rules:   [{ id, sort_order, target, operator, value_num, value_ref, condition, source }]
	// inputs:  values from the sheet, for example { "ability.dexterity": 14, "char.level": 5 }
	// context: { proficient: Set of value names, defenses: Set of "kind:code" }
	// Result:  an object with all values (inputs and calculated values)
	function calculate(rules, inputs, context) {
		const ctx = {
			proficient: (context && context.proficient) || new Set(),
			defenses: (context && context.defenses) || new Set()
		};
		const values = Object.assign({}, inputs);
		const sorted = rules
			.map((rule, index) => ({ rule, index }))
			.sort((a, b) => (a.rule.sort_order - b.rule.sort_order) || ((a.rule.id || 0) - (b.rule.id || 0)) || (a.index - b.index))
			.map(entry => entry.rule);

		for (const rule of sorted) {
			if (!test(rule.condition, rule, values, ctx)) continue;
			const given = rule.value_ref ? num(values[rule.value_ref]) : num(rule.value_num);
			values[rule.target] = apply(rule.operator, num(values[rule.target]), given);
		}
		return values;
	}

	const engine = { calculate };
	if (typeof module !== "undefined" && module.exports) module.exports = engine; // Node (tests)
	global.CalcEngine = engine;
})(typeof window !== "undefined" ? window : globalThis);
