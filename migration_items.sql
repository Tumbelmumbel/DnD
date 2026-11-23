-- migration_items.sql
-- Adds the tables items, items_translation, tags and item_tags, and the starting tags with English and German names.
-- Run this migration BEFORE you merge the pull request.

-- 1. Items: the name (English, base language) is unique
CREATE TABLE items (
  id         int unsigned NOT NULL AUTO_INCREMENT,
  name       varchar(150) NOT NULL,
  created_at datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_items_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Names of the items in other languages; a name is unique in its language
CREATE TABLE items_translation (
  item_id int unsigned NOT NULL,
  lang    varchar(5)   NOT NULL,
  name    varchar(150) NOT NULL,
  PRIMARY KEY (item_id, lang),
  UNIQUE KEY uq_items_translation_name (lang, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. The allowed tags (names in Lookup_translation, type item_tag). New tags only with a migration.
CREATE TABLE tags (
  code       varchar(40) NOT NULL,
  sort_order smallint    NOT NULL DEFAULT 0,
  PRIMARY KEY (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. The tags of each item
CREATE TABLE item_tags (
  item_id  int unsigned NOT NULL,
  tag_code varchar(40)  NOT NULL,
  PRIMARY KEY (item_id, tag_code),
  KEY idx_item_tags_tag (tag_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tags (code, sort_order) VALUES
  ('weapon', 1),
  ('armor', 2),
  ('shield', 3),
  ('ammunition', 4),
  ('tool', 5),
  ('potion', 6),
  ('scroll', 7),
  ('container', 8),
  ('gear', 9),
  ('simple', 10),
  ('martial', 11),
  ('melee', 12),
  ('ranged', 13),
  ('finesse', 14),
  ('light', 15),
  ('heavy', 16),
  ('loading', 17),
  ('reach', 18),
  ('thrown', 19),
  ('two_handed', 20),
  ('versatile', 21),
  ('needs_ammunition', 22),
  ('special', 23),
  ('light_armor', 24),
  ('medium_armor', 25),
  ('heavy_armor', 26),
  ('stealth_disadvantage', 27),
  ('magic', 28),
  ('consumable', 29);

INSERT INTO Lookup_translation (type, code, lang, name) VALUES
  ('item_tag', 'weapon', 'en', 'Weapon'),
  ('item_tag', 'weapon', 'de', 'Waffe'),
  ('item_tag', 'armor', 'en', 'Armor'),
  ('item_tag', 'armor', 'de', 'Rüstung'),
  ('item_tag', 'shield', 'en', 'Shield'),
  ('item_tag', 'shield', 'de', 'Schild'),
  ('item_tag', 'ammunition', 'en', 'Ammunition'),
  ('item_tag', 'ammunition', 'de', 'Munition'),
  ('item_tag', 'tool', 'en', 'Tool'),
  ('item_tag', 'tool', 'de', 'Werkzeug'),
  ('item_tag', 'potion', 'en', 'Potion'),
  ('item_tag', 'potion', 'de', 'Trank'),
  ('item_tag', 'scroll', 'en', 'Scroll'),
  ('item_tag', 'scroll', 'de', 'Schriftrolle'),
  ('item_tag', 'container', 'en', 'Container'),
  ('item_tag', 'container', 'de', 'Behälter'),
  ('item_tag', 'gear', 'en', 'Gear'),
  ('item_tag', 'gear', 'de', 'Ausrüstung'),
  ('item_tag', 'simple', 'en', 'Simple'),
  ('item_tag', 'simple', 'de', 'Einfach'),
  ('item_tag', 'martial', 'en', 'Martial'),
  ('item_tag', 'martial', 'de', 'Kriegswaffe'),
  ('item_tag', 'melee', 'en', 'Melee'),
  ('item_tag', 'melee', 'de', 'Nahkampf'),
  ('item_tag', 'ranged', 'en', 'Ranged'),
  ('item_tag', 'ranged', 'de', 'Fernkampf'),
  ('item_tag', 'finesse', 'en', 'Finesse'),
  ('item_tag', 'finesse', 'de', 'Finesse'),
  ('item_tag', 'light', 'en', 'Light'),
  ('item_tag', 'light', 'de', 'Leicht'),
  ('item_tag', 'heavy', 'en', 'Heavy'),
  ('item_tag', 'heavy', 'de', 'Schwer'),
  ('item_tag', 'loading', 'en', 'Loading'),
  ('item_tag', 'loading', 'de', 'Laden'),
  ('item_tag', 'reach', 'en', 'Reach'),
  ('item_tag', 'reach', 'de', 'Reichweite'),
  ('item_tag', 'thrown', 'en', 'Thrown'),
  ('item_tag', 'thrown', 'de', 'Wurfwaffe'),
  ('item_tag', 'two_handed', 'en', 'Two-handed'),
  ('item_tag', 'two_handed', 'de', 'Zweihändig'),
  ('item_tag', 'versatile', 'en', 'Versatile'),
  ('item_tag', 'versatile', 'de', 'Vielseitig'),
  ('item_tag', 'needs_ammunition', 'en', 'Needs ammunition'),
  ('item_tag', 'needs_ammunition', 'de', 'Benötigt Munition'),
  ('item_tag', 'special', 'en', 'Special'),
  ('item_tag', 'special', 'de', 'Speziell'),
  ('item_tag', 'light_armor', 'en', 'Light armor'),
  ('item_tag', 'light_armor', 'de', 'Leichte Rüstung'),
  ('item_tag', 'medium_armor', 'en', 'Medium armor'),
  ('item_tag', 'medium_armor', 'de', 'Mittelschwere Rüstung'),
  ('item_tag', 'heavy_armor', 'en', 'Heavy armor'),
  ('item_tag', 'heavy_armor', 'de', 'Schwere Rüstung'),
  ('item_tag', 'stealth_disadvantage', 'en', 'Disadvantage on Stealth'),
  ('item_tag', 'stealth_disadvantage', 'de', 'Nachteil auf Heimlichkeit'),
  ('item_tag', 'magic', 'en', 'Magic'),
  ('item_tag', 'magic', 'de', 'Magisch'),
  ('item_tag', 'consumable', 'en', 'Consumable'),
  ('item_tag', 'consumable', 'de', 'Verbrauchbar');

-- Rollback:
-- DROP TABLE item_tags; DROP TABLE tags; DROP TABLE items_translation; DROP TABLE items;
-- DELETE FROM Lookup_translation WHERE type = 'item_tag';
