-- migration_cleanup_lookup_names.sql
-- Deletes the old name fields of the game tables and the table abilities_skills.
-- The application reads the names from Lookup_translation and the skills from Skills.
-- Run this migration BEFORE you merge the pull request.
-- A rollback that restores the old names is at the end of this file (commented out).

-- 1. Classes: the names are in Lookup_translation (types class, subclass)
ALTER TABLE Classes DROP COLUMN Class, DROP COLUMN Subclass;

-- 2. Small lookup tables: the code becomes the primary key, the name field goes away
ALTER TABLE Species DROP INDEX speices, DROP INDEX uq_species_code, DROP COLUMN species, ADD PRIMARY KEY (code);
ALTER TABLE Backgrounds DROP PRIMARY KEY, DROP INDEX uq_backgrounds_code, DROP COLUMN background, ADD PRIMARY KEY (code);
ALTER TABLE Conditions DROP PRIMARY KEY, DROP INDEX uq_conditions_code, DROP COLUMN char_condition, ADD PRIMARY KEY (code);
ALTER TABLE Damagetypes DROP PRIMARY KEY, DROP INDEX damagetype, DROP INDEX uq_damagetypes_code, DROP COLUMN damagetype, ADD PRIMARY KEY (code);
ALTER TABLE Damage_influence DROP PRIMARY KEY, DROP INDEX influence, DROP INDEX uq_damage_influence_code, DROP COLUMN influence, ADD PRIMARY KEY (code);

-- 3. abilities_skills: replaced by Abilities and Skills
DROP TABLE abilities_skills;

-- ---------------------------------------------------------------------------
-- ROLLBACK (only if necessary): remove "-- " at the start of each line below and run it.
-- It restores the fields and the names byte for byte (HEX values, data from 2026-11-23).
-- ---------------------------------------------------------------------------

-- ALTER TABLE Classes ADD COLUMN Class text NOT NULL AFTER subclass_code, ADD COLUMN Subclass text NOT NULL AFTER Class;
-- UPDATE Classes SET Class = CONVERT(X'457266696E646572' USING utf8mb4), Subclass = '' WHERE class_code = 'artificer' AND subclass_code = '';
-- UPDATE Classes SET Class = CONVERT(X'426172626172' USING utf8mb4), Subclass = '' WHERE class_code = 'barbarian' AND subclass_code = '';
-- UPDATE Classes SET Class = CONVERT(X'4261726465' USING utf8mb4), Subclass = '' WHERE class_code = 'bard' AND subclass_code = '';
-- UPDATE Classes SET Class = CONVERT(X'4261726465' USING utf8mb4), Subclass = CONVERT(X'536368756C65206465732057697373656E73' USING utf8mb4) WHERE class_code = 'bard' AND subclass_code = 'college_of_lore';
-- UPDATE Classes SET Class = CONVERT(X'4261726465' USING utf8mb4), Subclass = CONVERT(X'536368756C652064657320576167656D757473' USING utf8mb4) WHERE class_code = 'bard' AND subclass_code = 'college_of_valor';
-- UPDATE Classes SET Class = CONVERT(X'4B6C6572696B6572' USING utf8mb4), Subclass = '' WHERE class_code = 'cleric' AND subclass_code = '';
-- UPDATE Classes SET Class = CONVERT(X'4B6C6572696B6572' USING utf8mb4), Subclass = CONVERT(X'446F6DC3A46E652064657320537475726D73' USING utf8mb4) WHERE class_code = 'cleric' AND subclass_code = 'tempest_domain';
-- UPDATE Classes SET Class = CONVERT(X'447275696465' USING utf8mb4), Subclass = '' WHERE class_code = 'druid' AND subclass_code = '';
-- UPDATE Classes SET Class = CONVERT(X'46696768746572' USING utf8mb4), Subclass = '' WHERE class_code = 'fighter' AND subclass_code = '';
-- UPDATE Classes SET Class = CONVERT(X'46696768746572' USING utf8mb4), Subclass = CONVERT(X'456C647269746368204B6E69676874' USING utf8mb4) WHERE class_code = 'fighter' AND subclass_code = 'eldritch_knight';
-- UPDATE Classes SET Class = CONVERT(X'50616C6164696E' USING utf8mb4), Subclass = '' WHERE class_code = 'paladin' AND subclass_code = '';
-- UPDATE Classes SET Class = CONVERT(X'50616C6164696E' USING utf8mb4), Subclass = CONVERT(X'536368777572206465722048696E67616265' USING utf8mb4) WHERE class_code = 'paladin' AND subclass_code = 'oath_of_devotion';
-- UPDATE Classes SET Class = CONVERT(X'50616C6164696E' USING utf8mb4), Subclass = CONVERT(X'446572205363687775722064657220416C74656E' USING utf8mb4) WHERE class_code = 'paladin' AND subclass_code = 'oath_of_the_ancients';
-- UPDATE Classes SET Class = CONVERT(X'50616C6164696E' USING utf8mb4), Subclass = CONVERT(X'53636877757220646572205261636865' USING utf8mb4) WHERE class_code = 'paladin' AND subclass_code = 'oath_of_vengeance';
-- UPDATE Classes SET Class = CONVERT(X'57616C646CC3A475666572' USING utf8mb4), Subclass = '' WHERE class_code = 'ranger' AND subclass_code = '';
-- UPDATE Classes SET Class = CONVERT(X'57616C646CC3A475666572' USING utf8mb4), Subclass = CONVERT(X'4865727220646572205469657265' USING utf8mb4) WHERE class_code = 'ranger' AND subclass_code = 'beast_master';
-- UPDATE Classes SET Class = CONVERT(X'57616C646CC3A475666572' USING utf8mb4), Subclass = CONVERT(X'4AC3A4676572' USING utf8mb4) WHERE class_code = 'ranger' AND subclass_code = 'hunter';
-- UPDATE Classes SET Class = CONVERT(X'53636875726B65' USING utf8mb4), Subclass = '' WHERE class_code = 'rogue' AND subclass_code = '';
-- UPDATE Classes SET Class = CONVERT(X'486578656E6D656973746572' USING utf8mb4), Subclass = '' WHERE class_code = 'warlock' AND subclass_code = '';
-- UPDATE Classes SET Class = CONVERT(X'4D6167696572' USING utf8mb4), Subclass = '' WHERE class_code = 'wizard' AND subclass_code = '';
-- ALTER TABLE Species ADD COLUMN species char(50) NOT NULL DEFAULT '' AFTER code;
-- UPDATE Species SET species = CONVERT(X'447261676F6E626F726E' USING utf8mb4) WHERE code = 'dragonborn';
-- UPDATE Species SET species = CONVERT(X'4477617266' USING utf8mb4) WHERE code = 'dwarf';
-- UPDATE Species SET species = CONVERT(X'456C66' USING utf8mb4) WHERE code = 'elf';
-- UPDATE Species SET species = CONVERT(X'476E6F6D65' USING utf8mb4) WHERE code = 'gnome';
-- UPDATE Species SET species = CONVERT(X'48616C666C696E67' USING utf8mb4) WHERE code = 'halfling';
-- UPDATE Species SET species = CONVERT(X'48616C662D456C66' USING utf8mb4) WHERE code = 'half_elf';
-- UPDATE Species SET species = CONVERT(X'48616C662D4F7263' USING utf8mb4) WHERE code = 'half_orc';
-- UPDATE Species SET species = CONVERT(X'48756D616E' USING utf8mb4) WHERE code = 'human';
-- UPDATE Species SET species = CONVERT(X'546965666C696E67' USING utf8mb4) WHERE code = 'tiefling';
-- ALTER TABLE Species DROP PRIMARY KEY, ADD UNIQUE KEY uq_species_code (code), ADD UNIQUE KEY speices (species);
-- ALTER TABLE Backgrounds ADD COLUMN background char(100) NOT NULL DEFAULT '' AFTER code;
-- UPDATE Backgrounds SET background = CONVERT(X'41636F6C797465' USING utf8mb4) WHERE code = 'acolyte';
-- UPDATE Backgrounds SET background = CONVERT(X'436861726C6174616E' USING utf8mb4) WHERE code = 'charlatan';
-- UPDATE Backgrounds SET background = CONVERT(X'4372696D696E616C' USING utf8mb4) WHERE code = 'criminal';
-- UPDATE Backgrounds SET background = CONVERT(X'456E7465727461696E6572' USING utf8mb4) WHERE code = 'entertainer';
-- UPDATE Backgrounds SET background = CONVERT(X'466F6C6BC2A04865726F' USING utf8mb4) WHERE code = 'folk_hero';
-- UPDATE Backgrounds SET background = CONVERT(X'4775696C64C2A04172746973616E' USING utf8mb4) WHERE code = 'guild_artisan';
-- UPDATE Backgrounds SET background = CONVERT(X'4865726D6974' USING utf8mb4) WHERE code = 'hermit';
-- UPDATE Backgrounds SET background = CONVERT(X'4E6F626C65' USING utf8mb4) WHERE code = 'noble';
-- UPDATE Backgrounds SET background = CONVERT(X'4F75746C616E646572' USING utf8mb4) WHERE code = 'outlander';
-- UPDATE Backgrounds SET background = CONVERT(X'53616765' USING utf8mb4) WHERE code = 'sage';
-- UPDATE Backgrounds SET background = CONVERT(X'5361696C6F72' USING utf8mb4) WHERE code = 'sailor';
-- UPDATE Backgrounds SET background = CONVERT(X'536F6C64696572' USING utf8mb4) WHERE code = 'soldier';
-- UPDATE Backgrounds SET background = CONVERT(X'55726368696E' USING utf8mb4) WHERE code = 'urchin';
-- ALTER TABLE Backgrounds DROP PRIMARY KEY, ADD UNIQUE KEY uq_backgrounds_code (code), ADD PRIMARY KEY (background);
-- ALTER TABLE Conditions ADD COLUMN char_condition char(30) NOT NULL DEFAULT '' AFTER code;
-- UPDATE Conditions SET char_condition = CONVERT(X'626C696E64' USING utf8mb4) WHERE code = 'blinded';
-- UPDATE Conditions SET char_condition = CONVERT(X'636861726D6564' USING utf8mb4) WHERE code = 'charmed';
-- UPDATE Conditions SET char_condition = CONVERT(X'64656166656E64' USING utf8mb4) WHERE code = 'deafened';
-- UPDATE Conditions SET char_condition = CONVERT(X'657868617573746564' USING utf8mb4) WHERE code = 'exhaustion';
-- UPDATE Conditions SET char_condition = CONVERT(X'667269676874656E6564' USING utf8mb4) WHERE code = 'frightened';
-- UPDATE Conditions SET char_condition = CONVERT(X'67726170706C6564' USING utf8mb4) WHERE code = 'grappled';
-- UPDATE Conditions SET char_condition = CONVERT(X'696E6361706163697461746564' USING utf8mb4) WHERE code = 'incapacitated';
-- UPDATE Conditions SET char_condition = CONVERT(X'696E76697369626C65' USING utf8mb4) WHERE code = 'invisible';
-- UPDATE Conditions SET char_condition = CONVERT(X'706172616C797A6564' USING utf8mb4) WHERE code = 'paralyzed';
-- UPDATE Conditions SET char_condition = CONVERT(X'706574726966696564' USING utf8mb4) WHERE code = 'petrified';
-- UPDATE Conditions SET char_condition = CONVERT(X'706F69736F6E6564' USING utf8mb4) WHERE code = 'poisoned';
-- UPDATE Conditions SET char_condition = CONVERT(X'70726F6E65' USING utf8mb4) WHERE code = 'prone';
-- UPDATE Conditions SET char_condition = CONVERT(X'726573747261696E6564' USING utf8mb4) WHERE code = 'restrained';
-- UPDATE Conditions SET char_condition = CONVERT(X'7374756E6E6564' USING utf8mb4) WHERE code = 'stunned';
-- UPDATE Conditions SET char_condition = CONVERT(X'756E636F6E63696F7573' USING utf8mb4) WHERE code = 'unconscious';
-- ALTER TABLE Conditions DROP PRIMARY KEY, ADD UNIQUE KEY uq_conditions_code (code), ADD PRIMARY KEY (char_condition);
-- ALTER TABLE Damagetypes ADD COLUMN damagetype char(255) NOT NULL DEFAULT '' AFTER code;
-- UPDATE Damagetypes SET damagetype = CONVERT(X'41636964' USING utf8mb4) WHERE code = 'acid';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'426C756467656F6E696E67' USING utf8mb4) WHERE code = 'bludgeoning';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'436F6C64' USING utf8mb4) WHERE code = 'cold';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'46697265' USING utf8mb4) WHERE code = 'fire';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'466F726365' USING utf8mb4) WHERE code = 'force';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'4C696768746E696E67' USING utf8mb4) WHERE code = 'lightning';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'4E6563726F746963' USING utf8mb4) WHERE code = 'necrotic';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'5069657263696E67' USING utf8mb4) WHERE code = 'piercing';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'506F69736F6E' USING utf8mb4) WHERE code = 'poison';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'50737963686963' USING utf8mb4) WHERE code = 'psychic';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'52616469616E74' USING utf8mb4) WHERE code = 'radiant';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'536C617368696E67' USING utf8mb4) WHERE code = 'slashing';
-- UPDATE Damagetypes SET damagetype = CONVERT(X'5468756E646572' USING utf8mb4) WHERE code = 'thunder';
-- ALTER TABLE Damagetypes DROP PRIMARY KEY, ADD UNIQUE KEY uq_damagetypes_code (code), ADD PRIMARY KEY (damagetype), ADD UNIQUE KEY damagetype (damagetype);
-- ALTER TABLE Damage_influence ADD COLUMN influence char(30) NOT NULL DEFAULT '' AFTER code;
-- UPDATE Damage_influence SET influence = CONVERT(X'696D6D756E65' USING utf8mb4) WHERE code = 'immune';
-- UPDATE Damage_influence SET influence = CONVERT(X'726573697374616E74' USING utf8mb4) WHERE code = 'resistant';
-- UPDATE Damage_influence SET influence = CONVERT(X'76756C6E657261626C65' USING utf8mb4) WHERE code = 'vulnerable';
-- ALTER TABLE Damage_influence DROP PRIMARY KEY, ADD UNIQUE KEY uq_damage_influence_code (code), ADD PRIMARY KEY (influence), ADD UNIQUE KEY influence (influence);
-- CREATE TABLE abilities_skills (
--   ability char(30) NOT NULL,
--   skills char(255) DEFAULT NULL,
--   PRIMARY KEY (ability)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- INSERT INTO abilities_skills (ability, skills) VALUES
--   ('Charisma', 'Performance, Intimidation, Deception, Persuasion'),
--   ('Constitution', NULL),
--   ('Dexterity', 'Acrobatics, Slight Of Hand, Stealth'),
--   ('Intelligence', 'Arcana, History, Investigation, Nature, Religion'),
--   ('Strength', 'Athletics'),
--   ('Wisdom', 'Medicine, Animal Handling, Insight, Survival, Perception');
