-- migration_calculations.sql
-- Adds the field id to the table Characters.
-- Adds the tables character_items, calculation_templates and character_calculations.
-- Run this migration BEFORE you merge the pull request.

-- 1. Characters get a number. The primary key (name, username) stays.
--    MySQL gives each existing character a number.
ALTER TABLE Characters
  ADD COLUMN id int unsigned NOT NULL AUTO_INCREMENT FIRST,
  ADD UNIQUE KEY uq_characters_id (id);

-- 2. Inventory: which character has which item
CREATE TABLE character_items (
  id           int unsigned      NOT NULL AUTO_INCREMENT,
  character_id int unsigned      NOT NULL,
  item_id      int unsigned      NOT NULL,
  quantity     smallint unsigned NOT NULL DEFAULT 1,
  equipped     tinyint(1)        NOT NULL DEFAULT 0,
  created_at   datetime          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_character_items_character (character_id),
  KEY idx_character_items_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Templates: the rules that an item carries, and the base rules for every character
CREATE TABLE calculation_templates (
  id             int unsigned      NOT NULL AUTO_INCREMENT,
  source_type    varchar(20)       NOT NULL,
  item_id        int unsigned      NULL DEFAULT NULL,
  sort_order     smallint unsigned NOT NULL,
  target         varchar(60)       NOT NULL,
  operator       enum('set','add','sub','mul','div','min','max','floor') NOT NULL,
  value_num      decimal(10,2)     NULL DEFAULT NULL,
  value_ref      varchar(60)       NULL DEFAULT NULL,
  condition_json json              NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_calc_templates_source (source_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. The rules of each character (copies of the templates)
CREATE TABLE character_calculations (
  id                int unsigned      NOT NULL AUTO_INCREMENT,
  character_id      int unsigned      NOT NULL,
  source_type       varchar(20)       NOT NULL,
  character_item_id int unsigned      NULL DEFAULT NULL,
  sort_order        smallint unsigned NOT NULL,
  target            varchar(60)       NOT NULL,
  operator          enum('set','add','sub','mul','div','min','max','floor') NOT NULL,
  value_num         decimal(10,2)     NULL DEFAULT NULL,
  value_ref         varchar(60)       NULL DEFAULT NULL,
  condition_json    json              NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_char_calc_character (character_id),
  KEY idx_char_calc_item (character_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
