-- migration_test_items.sql
-- Adds five test items with tags, German names and calculation rules.
-- Run this migration BEFORE you merge the pull request.
-- The rules use item_id from the name of the item, so the numbers of the items do not matter.

-- 1. The items (cost in gold pieces, weight in pounds)
INSERT INTO items (name, weight_lb, rarity_code, cost_gp) VALUES
  ('Shield',                 6.00,  'mundane',  10.00),
  ('Chain Mail',             55.00, 'mundane',  75.00),
  ('Scale Mail',             45.00, 'mundane',  50.00),
  ('Ring of Wisdom',         NULL,  'uncommon', NULL),
  ('Necklace of Persuasion', NULL,  'uncommon', NULL);

-- 2. German names
INSERT INTO items_translation (item_id, lang, name)
  SELECT id, 'de', 'Schild'                    FROM items WHERE name = 'Shield' UNION ALL
  SELECT id, 'de', 'Kettenpanzer'              FROM items WHERE name = 'Chain Mail' UNION ALL
  SELECT id, 'de', 'Schuppenpanzer'            FROM items WHERE name = 'Scale Mail' UNION ALL
  SELECT id, 'de', 'Ring der Weisheit'         FROM items WHERE name = 'Ring of Wisdom' UNION ALL
  SELECT id, 'de', 'Halskette der Überzeugung' FROM items WHERE name = 'Necklace of Persuasion';

-- 3. Tags
INSERT INTO item_tags (item_id, tag_code)
  SELECT id, 'shield'               FROM items WHERE name = 'Shield' UNION ALL
  SELECT id, 'armor'                FROM items WHERE name = 'Chain Mail' UNION ALL
  SELECT id, 'heavy_armor'          FROM items WHERE name = 'Chain Mail' UNION ALL
  SELECT id, 'stealth_disadvantage' FROM items WHERE name = 'Chain Mail' UNION ALL
  SELECT id, 'armor'                FROM items WHERE name = 'Scale Mail' UNION ALL
  SELECT id, 'medium_armor'         FROM items WHERE name = 'Scale Mail' UNION ALL
  SELECT id, 'stealth_disadvantage' FROM items WHERE name = 'Scale Mail' UNION ALL
  SELECT id, 'magic'                FROM items WHERE name = 'Ring of Wisdom' UNION ALL
  SELECT id, 'magic'                FROM items WHERE name = 'Necklace of Persuasion';

-- 4. Calculation rules of the items (they apply only while the item is equipped)
--    Shield: armor class + 2
--    Chain Mail (heavy): armor class 16, no Dexterity
--    Scale Mail (medium): armor class 14 + Dexterity, at most + 2
--    Ring of Wisdom: Wisdom modifier + 1 (after the modifier is calculated)
--    Necklace of Persuasion: Persuasion + 1
INSERT INTO calculation_templates (source_type, item_id, sort_order, target, operator, value_num, value_ref, condition_json)
  SELECT 'item', id, 413, 'combat.ac',        'add', 2,  NULL, '{"EQUIPPED": true}' FROM items WHERE name = 'Shield' UNION ALL
  SELECT 'item', id, 402, 'combat.ac_dex',    'set', 0,  NULL, '{"EQUIPPED": true}' FROM items WHERE name = 'Chain Mail' UNION ALL
  SELECT 'item', id, 411, 'combat.ac',        'set', 16, NULL, '{"EQUIPPED": true}' FROM items WHERE name = 'Chain Mail' UNION ALL
  SELECT 'item', id, 401, 'combat.ac_dex',    'max', 2,  NULL, '{"EQUIPPED": true}' FROM items WHERE name = 'Scale Mail' UNION ALL
  SELECT 'item', id, 411, 'combat.ac',        'set', 14, NULL, '{"EQUIPPED": true}' FROM items WHERE name = 'Scale Mail' UNION ALL
  SELECT 'item', id, 214, 'mod.wisdom',       'add', 1,  NULL, '{"EQUIPPED": true}' FROM items WHERE name = 'Ring of Wisdom' UNION ALL
  SELECT 'item', id, 302, 'skill.persuasion', 'add', 1,  NULL, '{"EQUIPPED": true}' FROM items WHERE name = 'Necklace of Persuasion';
