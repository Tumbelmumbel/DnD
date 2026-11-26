-- migration_weapons.sql
-- Adds the weapon fields to items and five test weapons with German names and tags.
-- Run this migration BEFORE you merge the pull request.
-- The weapons have no calculation rules. The tags use the codes of the table tags.

-- 1. Weapon fields (NULL for items that are not weapons)
ALTER TABLE items
  ADD COLUMN damage_dice           varchar(20)       NULL DEFAULT NULL AFTER cost_gp,
  ADD COLUMN damage_dice_versatile varchar(20)       NULL DEFAULT NULL AFTER damage_dice,
  ADD COLUMN damage_type_code      varchar(40)       NULL DEFAULT NULL AFTER damage_dice_versatile,
  ADD COLUMN range_normal_ft       smallint unsigned NULL DEFAULT NULL AFTER damage_type_code,
  ADD COLUMN range_long_ft         smallint unsigned NULL DEFAULT NULL AFTER range_normal_ft;

-- 2. The test weapons (cost in gold pieces, weight in pounds, range in feet)
INSERT INTO items (name, weight_lb, rarity_code, cost_gp, damage_dice, damage_dice_versatile, damage_type_code, range_normal_ft, range_long_ft) VALUES
  ('Dagger',         1.00, 'mundane',  2.00, '1d4',  NULL,   'piercing', 20,   60),
  ('Longsword',      3.00, 'mundane', 15.00, '1d8',  '1d10', 'slashing', NULL, NULL),
  ('Greataxe',       7.00, 'mundane', 30.00, '1d12', NULL,   'slashing', NULL, NULL),
  ('Shortbow',       2.00, 'mundane', 25.00, '1d6',  NULL,   'piercing', 80,   320),
  ('Light Crossbow', 5.00, 'mundane', 25.00, '1d8',  NULL,   'piercing', 80,   320);

-- 3. German names
INSERT INTO items_translation (item_id, lang, name)
  SELECT id, 'de', 'Dolch'            FROM items WHERE name = 'Dagger' UNION ALL
  SELECT id, 'de', 'Langschwert'      FROM items WHERE name = 'Longsword' UNION ALL
  SELECT id, 'de', 'Zweihandaxt'      FROM items WHERE name = 'Greataxe' UNION ALL
  SELECT id, 'de', 'Kurzbogen'        FROM items WHERE name = 'Shortbow' UNION ALL
  SELECT id, 'de', 'Leichte Armbrust' FROM items WHERE name = 'Light Crossbow';

-- 4. Tags
INSERT INTO item_tags (item_id, tag_code)
  SELECT i.id, t.tag FROM items i JOIN (
    SELECT 'Dagger' AS n, 'weapon' AS tag UNION ALL SELECT 'Dagger', 'simple' UNION ALL SELECT 'Dagger', 'melee' UNION ALL
    SELECT 'Dagger', 'finesse' UNION ALL SELECT 'Dagger', 'light' UNION ALL SELECT 'Dagger', 'thrown' UNION ALL
    SELECT 'Longsword', 'weapon' UNION ALL SELECT 'Longsword', 'martial' UNION ALL SELECT 'Longsword', 'melee' UNION ALL
    SELECT 'Longsword', 'versatile' UNION ALL
    SELECT 'Greataxe', 'weapon' UNION ALL SELECT 'Greataxe', 'martial' UNION ALL SELECT 'Greataxe', 'melee' UNION ALL
    SELECT 'Greataxe', 'heavy' UNION ALL SELECT 'Greataxe', 'two_handed' UNION ALL
    SELECT 'Shortbow', 'weapon' UNION ALL SELECT 'Shortbow', 'simple' UNION ALL SELECT 'Shortbow', 'ranged' UNION ALL
    SELECT 'Shortbow', 'needs_ammunition' UNION ALL SELECT 'Shortbow', 'two_handed' UNION ALL
    SELECT 'Light Crossbow', 'weapon' UNION ALL SELECT 'Light Crossbow', 'simple' UNION ALL SELECT 'Light Crossbow', 'ranged' UNION ALL
    SELECT 'Light Crossbow', 'needs_ammunition' UNION ALL SELECT 'Light Crossbow', 'loading' UNION ALL SELECT 'Light Crossbow', 'two_handed'
  ) t ON t.n = i.name;
