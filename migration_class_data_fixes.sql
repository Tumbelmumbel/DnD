-- migration_class_data_fixes.sql
-- Corrects two errors in the table Classes.
-- Needs migration_lookup_codes.sql (the fields class_code and subclass_code).
-- Run this migration BEFORE you merge the pull request.

-- 1. Artificer: the hit die is d8 (before: 0). Changes 20 rows.
UPDATE Classes SET Hit_Dice = 8 WHERE class_code = 'artificer';

-- 2. Fighter: add the base class (levels 1 to 20). Adds 20 rows.
--    The values come from the Eldritch Knight rows (same proficiency bonus and hit die d10), without spells.
INSERT INTO Classes (class_code, subclass_code, Class, Subclass, Level, Proficiency_Bonus, Hit_Dice,
  Spell_Prep_Method, Spell_Modifier, Known_Cantrips, Known_Spells,
  Spells_Level_1, Spells_Level_2, Spells_Level_3, Spells_Level_4, Spells_Level_5,
  Spells_Level_6, Spells_Level_7, Spells_Level_8, Spells_Level_9,
  Arcane_Focus, Ritual_capable, Known_Evocations, Rage_Amount, Focus_Points,
  Sneekatack_Amount, Prepared_Spells)
SELECT 'fighter', '', 'Fighter', '', Level, Proficiency_Bonus, Hit_Dice,
  '', '', 0, 0,  0, 0, 0, 0, 0,  0, 0, 0, 0,  '', 0, 0, 0, 0, 0, 0
FROM Classes WHERE class_code = 'fighter' AND subclass_code = 'eldritch_knight';
