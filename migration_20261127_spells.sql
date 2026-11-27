-- migration_20261127_spells.sql
-- Spells: tables for the spells, the spell data of the classes, the base rules and the test spells.
-- The deploy runs this file with migrate.php after a backup.
--
-- Spell texts: System Reference Document 5.1 ("SRD 5.1") by Wizards of the Coast LLC,
-- https://dnd.wizards.com/resources/systems-reference-document (English and German version).
-- The SRD 5.1 is licensed under the Creative Commons Attribution 4.0 International License,
-- https://creativecommons.org/licenses/by/4.0/legalcode

-- 1) Tables

CREATE TABLE spell_schools (
  code varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  sort_order smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE spells (
  code varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  level tinyint unsigned NOT NULL,
  school_code varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  comp_v tinyint(1) NOT NULL DEFAULT 0,
  comp_s tinyint(1) NOT NULL DEFAULT 0,
  comp_m tinyint(1) NOT NULL DEFAULT 0,
  ritual tinyint(1) NOT NULL DEFAULT 0,
  concentration tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (code),
  KEY idx_spells_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE spells_translation (
  spell_code varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  lang varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  name varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  casting_time varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  spell_range varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  duration varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  material varchar(400) COLLATE utf8mb4_general_ci DEFAULT NULL,
  description text COLLATE utf8mb4_general_ci NOT NULL,
  higher_levels text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (spell_code, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE spell_classes (
  spell_code varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  list_code varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (spell_code, list_code),
  KEY idx_spell_classes_list (list_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2) Schools of magic and their names

INSERT INTO spell_schools (code, sort_order) VALUES
  ('abjuration', 1), ('conjuration', 2), ('divination', 3), ('enchantment', 4),
  ('evocation', 5), ('illusion', 6), ('necromancy', 7), ('transmutation', 8);

INSERT INTO Lookup_translation (type, code, lang, name) VALUES
  ('spell_school', 'abjuration',    'en', 'Abjuration'),
  ('spell_school', 'conjuration',   'en', 'Conjuration'),
  ('spell_school', 'divination',    'en', 'Divination'),
  ('spell_school', 'enchantment',   'en', 'Enchantment'),
  ('spell_school', 'evocation',     'en', 'Evocation'),
  ('spell_school', 'illusion',      'en', 'Illusion'),
  ('spell_school', 'necromancy',    'en', 'Necromancy'),
  ('spell_school', 'transmutation', 'en', 'Transmutation'),
  ('spell_school', 'abjuration',    'de', 'Bannmagie'),
  ('spell_school', 'conjuration',   'de', 'Beschwörung'),
  ('spell_school', 'divination',    'de', 'Erkenntnismagie'),
  ('spell_school', 'enchantment',   'de', 'Verzauberung'),
  ('spell_school', 'evocation',     'de', 'Hervorrufung'),
  ('spell_school', 'illusion',      'de', 'Illusion'),
  ('spell_school', 'necromancy',    'de', 'Nekromantie'),
  ('spell_school', 'transmutation', 'de', 'Verwandlung');

-- 3) Spell data of the classes
-- Spell_Modifier: the code of the ability (Abilities.code) instead of the English name.
UPDATE Classes SET Spell_Modifier = LOWER(TRIM(Spell_Modifier));
-- Corrections: the Eldritch Knight casts with Intelligence. One Tempest Domain row had no ability.
UPDATE Classes SET Spell_Modifier = 'intelligence' WHERE class_code = 'fighter' AND subclass_code = 'eldritch_knight';
UPDATE Classes SET Spell_Modifier = 'wisdom' WHERE class_code = 'cleric' AND Spell_Modifier = '';

-- Spell_Prep_Method: codes instead of names.
-- known = knows a fixed number of spells, prepared = prepares from the class list, spellbook = wizard.
UPDATE Classes SET Spell_Prep_Method = CASE TRIM(Spell_Prep_Method)
    WHEN 'Knowledge'   THEN 'known'
    WHEN 'Preparation' THEN 'prepared'
    WHEN 'Wizard'      THEN 'spellbook'
    ELSE '' END;

-- Spell_List: the spell list that the class uses (spell_classes.list_code). Empty = no spells.
ALTER TABLE Classes ADD COLUMN Spell_List varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '';
UPDATE Classes SET Spell_List = class_code WHERE Spell_Modifier <> '';
UPDATE Classes SET Spell_List = 'wizard' WHERE class_code = 'fighter' AND subclass_code = 'eldritch_knight';

-- Prepared_Spells: the part of the number of prepared spells that comes from the level.
-- The engine adds the modifier of the spell ability (rules 730-732).
UPDATE Classes SET Prepared_Spells = CASE
    WHEN Spell_Prep_Method NOT IN ('prepared', 'spellbook') THEN 0
    WHEN class_code = 'paladin'   THEN FLOOR(Level / 2)
    WHEN class_code = 'artificer' THEN CEIL(Level / 2)
    ELSE Level END;

-- 4) Base rules of the spell values (sort_order 700-732)
-- The check SPELL_ABILITY is true if the selected class casts spells with this ability.
INSERT INTO calculation_templates (source_type, sort_order, target, operator, value_num, value_ref, condition_json) VALUES
  ('base', 700, 'spell.mod',          'set', 0,    NULL,               NULL),
  ('base', 701, 'spell.mod',          'set', NULL, 'mod.intelligence', '{"SPELL_ABILITY": "intelligence"}'),
  ('base', 702, 'spell.mod',          'set', NULL, 'mod.wisdom',       '{"SPELL_ABILITY": "wisdom"}'),
  ('base', 703, 'spell.mod',          'set', NULL, 'mod.charisma',     '{"SPELL_ABILITY": "charisma"}'),
  ('base', 710, 'spell.save_dc',      'set', 8,    NULL,               NULL),
  ('base', 711, 'spell.save_dc',      'add', NULL, 'char.prof_bonus',  NULL),
  ('base', 712, 'spell.save_dc',      'add', NULL, 'spell.mod',        NULL),
  ('base', 720, 'spell.attack',       'set', NULL, 'char.prof_bonus',  NULL),
  ('base', 721, 'spell.attack',       'add', NULL, 'spell.mod',        NULL),
  ('base', 730, 'spell.prepared_max', 'set', NULL, 'spell.prepared_base', NULL),
  ('base', 731, 'spell.prepared_max', 'add', NULL, 'spell.mod',        '{"COMPARE": ["spell.prepared_base", ">", 0]}'),
  ('base', 732, 'spell.prepared_max', 'min', 1,    NULL,               '{"COMPARE": ["spell.prepared_base", ">", 0]}');

-- 5) Test spells (SRD 5.1)
