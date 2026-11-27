-- migration_weapon_rules.sql
-- Adds the base rules for the weapons card (sort_order 600-620).
-- The engine calculates these values one time for each equipped weapon, with the weapon as context.
-- Run this migration BEFORE you merge the pull request.

INSERT INTO calculation_templates (source_type, sort_order, target, operator, value_num, value_ref, condition_json) VALUES
  ('base', 600, 'weapon.ability',      'set', NULL, 'mod.strength',    NULL),
  ('base', 601, 'weapon.ability',      'set', NULL, 'mod.dexterity',   '{"WEAPON_TAG": "ranged"}'),
  ('base', 602, 'weapon.ability',      'min', NULL, 'mod.dexterity',   '{"WEAPON_TAG": "finesse"}'),
  ('base', 610, 'weapon.attack',       'set', NULL, 'weapon.ability',  NULL),
  ('base', 611, 'weapon.attack',       'add', NULL, 'char.prof_bonus', '{"WEAPON_PROFICIENT": true}'),
  ('base', 620, 'weapon.damage_bonus', 'set', NULL, 'weapon.ability',  NULL);
