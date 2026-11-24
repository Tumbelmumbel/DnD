-- migration_item_properties.sql
-- Adds the fields weight_lb, rarity_code and cost_gp to the table items.
-- Adds the table item_rarities with the rarities and their English and German names.
-- Run this migration BEFORE you merge the pull request.

-- 1. The allowed rarities (names in Lookup_translation, type item_rarity). New rarities only with a migration.
CREATE TABLE item_rarities (
  code       varchar(20) NOT NULL,
  sort_order smallint    NOT NULL DEFAULT 0,
  PRIMARY KEY (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO item_rarities (code, sort_order) VALUES
  ('mundane', 0), ('common', 1), ('uncommon', 2), ('rare', 3),
  ('very_rare', 4), ('legendary', 5), ('artifact', 6);

INSERT INTO Lookup_translation (type, code, lang, name) VALUES
  ('item_rarity', 'mundane',   'en', 'Mundane'),   ('item_rarity', 'mundane',   'de', 'Nichtmagisch'),
  ('item_rarity', 'common',    'en', 'Common'),    ('item_rarity', 'common',    'de', 'Gewöhnlich'),
  ('item_rarity', 'uncommon',  'en', 'Uncommon'),  ('item_rarity', 'uncommon',  'de', 'Ungewöhnlich'),
  ('item_rarity', 'rare',      'en', 'Rare'),      ('item_rarity', 'rare',      'de', 'Selten'),
  ('item_rarity', 'very_rare', 'en', 'Very rare'), ('item_rarity', 'very_rare', 'de', 'Sehr selten'),
  ('item_rarity', 'legendary', 'en', 'Legendary'), ('item_rarity', 'legendary', 'de', 'Legendär'),
  ('item_rarity', 'artifact',  'en', 'Artifact'),  ('item_rarity', 'artifact',  'de', 'Artefakt');

-- 2. The new fields of the items
--    weight_lb: weight in pounds (lb), empty = no weight
--    rarity_code: code from item_rarities, new items get 'mundane'
--    cost_gp: cost in gold pieces with 2 decimals (1 sp = 0.10, 1 cp = 0.01), empty = no fixed cost
ALTER TABLE items
  ADD COLUMN weight_lb   decimal(8,2)  NULL     DEFAULT NULL      AFTER name,
  ADD COLUMN rarity_code varchar(20)   NOT NULL DEFAULT 'mundane' AFTER weight_lb,
  ADD COLUMN cost_gp     decimal(10,2) NULL     DEFAULT NULL      AFTER rarity_code,
  ADD KEY idx_items_rarity (rarity_code);
