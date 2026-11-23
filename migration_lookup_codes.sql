-- migration_lookup_codes.sql
-- Adds a fixed English code to every lookup value (i18n step 2).
-- Run this migration BEFORE you merge the pull request.
-- The migration only adds columns and tables. It deletes nothing.

-- 1. Classes: class_code and subclass_code
ALTER TABLE Classes
  ADD COLUMN class_code varchar(40) NOT NULL DEFAULT '' FIRST,
  ADD COLUMN subclass_code varchar(60) NOT NULL DEFAULT '' AFTER class_code;

UPDATE Classes SET class_code = CASE Class
  WHEN 'Barbar'       THEN 'barbarian'
  WHEN 'Barde'        THEN 'bard'
  WHEN 'Druide'       THEN 'druid'
  WHEN 'Erfinder'     THEN 'artificer'
  WHEN 'Fighter'      THEN 'fighter'
  WHEN 'Hexenmeister' THEN 'warlock'
  WHEN 'Kleriker'     THEN 'cleric'
  WHEN 'Magier'       THEN 'wizard'
  WHEN 'Paladin'      THEN 'paladin'
  WHEN 'Schurke'      THEN 'rogue'
  WHEN 'Waldläufer'   THEN 'ranger'
  ELSE 'UNMAPPED' END;

UPDATE Classes SET subclass_code = CASE Subclass
  WHEN ''                     THEN ''
  WHEN 'Schule des Wagemuts'  THEN 'college_of_valor'
  WHEN 'Schule des Wissens'   THEN 'college_of_lore'
  WHEN 'Eldritch Knight'      THEN 'eldritch_knight'
  WHEN 'Domäne des Sturms'    THEN 'tempest_domain'
  WHEN 'Der Schwur der Alten' THEN 'oath_of_the_ancients'
  WHEN 'Schwur der Hingabe'   THEN 'oath_of_devotion'
  WHEN 'Schwur der Rache'     THEN 'oath_of_vengeance'
  WHEN 'Herr der Tiere'       THEN 'beast_master'
  WHEN 'Jäger'                THEN 'hunter'
  ELSE 'UNMAPPED' END;

-- Each class, subclass and level is one row. This step fails if two rows have the same codes.
ALTER TABLE Classes ADD PRIMARY KEY (class_code, subclass_code, Level);

-- 2. Species, Backgrounds, Damagetypes, Damage_influence: code = name in lowercase, "_" for spaces and hyphens
ALTER TABLE Species ADD COLUMN code varchar(40) NOT NULL DEFAULT '' FIRST;
UPDATE Species SET code = LOWER(REPLACE(REPLACE(TRIM(species), ' ', '_'), '-', '_'));
ALTER TABLE Species ADD UNIQUE KEY uq_species_code (code);

ALTER TABLE Backgrounds ADD COLUMN code varchar(40) NOT NULL DEFAULT '' FIRST;
UPDATE Backgrounds SET code = LOWER(REPLACE(REPLACE(REPLACE(TRIM(background), ' ', '_'), '-', '_'), _utf8mb4 X'C2A0', '_'));
-- Two names (Folk Hero, Guild Artisan) contain a non-breaking space (bytes C2 A0). The last REPLACE changes it to "_".
ALTER TABLE Backgrounds ADD UNIQUE KEY uq_backgrounds_code (code);

ALTER TABLE Damagetypes ADD COLUMN code varchar(40) NOT NULL DEFAULT '' FIRST;
UPDATE Damagetypes SET code = LOWER(REPLACE(REPLACE(TRIM(damagetype), ' ', '_'), '-', '_'));
ALTER TABLE Damagetypes ADD UNIQUE KEY uq_damagetypes_code (code);

ALTER TABLE Damage_influence ADD COLUMN code varchar(40) NOT NULL DEFAULT '' FIRST;
UPDATE Damage_influence SET code = LOWER(REPLACE(REPLACE(TRIM(influence), ' ', '_'), '-', '_'));
ALTER TABLE Damage_influence ADD UNIQUE KEY uq_damage_influence_code (code);

-- 3. Conditions: the codes use the correct words (three names have spelling errors)
ALTER TABLE Conditions ADD COLUMN code varchar(40) NOT NULL DEFAULT '' FIRST;
UPDATE Conditions SET code = CASE char_condition
  WHEN 'blind'      THEN 'blinded'
  WHEN 'deafend'    THEN 'deafened'
  WHEN 'exhausted'  THEN 'exhaustion'
  WHEN 'unconcious' THEN 'unconscious'
  ELSE LOWER(REPLACE(TRIM(char_condition), ' ', '_')) END;
ALTER TABLE Conditions ADD UNIQUE KEY uq_conditions_code (code);

-- 4. New tables Abilities and Skills (display names come from lang/*.php)
CREATE TABLE Abilities (
  code       varchar(20) NOT NULL,
  sort_order tinyint     NOT NULL,
  PRIMARY KEY (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO Abilities (code, sort_order) VALUES
  ('strength', 1), ('dexterity', 2), ('constitution', 3),
  ('intelligence', 4), ('wisdom', 5), ('charisma', 6);

CREATE TABLE Skills (
  code         varchar(40) NOT NULL,
  ability_code varchar(20) NOT NULL,
  sort_order   tinyint     NOT NULL,
  PRIMARY KEY (code),
  KEY idx_skills_ability (ability_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO Skills (code, ability_code, sort_order) VALUES
  ('athletics',       'strength',      1),
  ('acrobatics',      'dexterity',     2),
  ('sleight_of_hand', 'dexterity',     3),
  ('stealth',         'dexterity',     4),
  ('arcana',          'intelligence',  5),
  ('history',         'intelligence',  6),
  ('investigation',   'intelligence',  7),
  ('nature',          'intelligence',  8),
  ('religion',        'intelligence',  9),
  ('animal_handling', 'wisdom',       10),
  ('insight',         'wisdom',       11),
  ('medicine',        'wisdom',       12),
  ('perception',      'wisdom',       13),
  ('survival',        'wisdom',       14),
  ('deception',       'charisma',     15),
  ('intimidation',    'charisma',     16),
  ('performance',     'charisma',     17),
  ('persuasion',      'charisma',     18);
