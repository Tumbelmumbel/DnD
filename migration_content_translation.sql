-- migration_content_translation.sql
-- Adds translation tables for the content from the database (language plan, step 3).
-- Run this migration BEFORE you merge the pull request.
-- The migration only adds tables and rows. It deletes nothing.

-- 1. Names of the lookup values, one row per value and language
CREATE TABLE Lookup_translation (
  type varchar(30)  NOT NULL,
  code varchar(60)  NOT NULL,
  lang varchar(5)   NOT NULL,
  name varchar(150) NOT NULL,
  PRIMARY KEY (type, code, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO Lookup_translation (type, code, lang, name) VALUES
  ('class', 'artificer', 'en', 'Artificer'),
  ('class', 'artificer', 'de', 'Erfinder'),
  ('class', 'barbarian', 'en', 'Barbarian'),
  ('class', 'barbarian', 'de', 'Barbar'),
  ('class', 'bard', 'en', 'Bard'),
  ('class', 'bard', 'de', 'Barde'),
  ('class', 'cleric', 'en', 'Cleric'),
  ('class', 'cleric', 'de', 'Kleriker'),
  ('class', 'druid', 'en', 'Druid'),
  ('class', 'druid', 'de', 'Druide'),
  ('class', 'fighter', 'en', 'Fighter'),
  ('class', 'fighter', 'de', 'Kämpfer'),
  ('class', 'paladin', 'en', 'Paladin'),
  ('class', 'paladin', 'de', 'Paladin'),
  ('class', 'ranger', 'en', 'Ranger'),
  ('class', 'ranger', 'de', 'Waldläufer'),
  ('class', 'rogue', 'en', 'Rogue'),
  ('class', 'rogue', 'de', 'Schurke'),
  ('class', 'warlock', 'en', 'Warlock'),
  ('class', 'warlock', 'de', 'Hexenmeister'),
  ('class', 'wizard', 'en', 'Wizard'),
  ('class', 'wizard', 'de', 'Magier');

INSERT INTO Lookup_translation (type, code, lang, name) VALUES
  ('subclass', 'beast_master', 'en', 'Beast Master'),
  ('subclass', 'beast_master', 'de', 'Herr der Tiere'),
  ('subclass', 'college_of_lore', 'en', 'College of Lore'),
  ('subclass', 'college_of_lore', 'de', 'Schule des Wissens'),
  ('subclass', 'college_of_valor', 'en', 'College of Valor'),
  ('subclass', 'college_of_valor', 'de', 'Schule des Wagemuts'),
  ('subclass', 'eldritch_knight', 'en', 'Eldritch Knight'),
  ('subclass', 'eldritch_knight', 'de', 'Mystischer Ritter'),
  ('subclass', 'hunter', 'en', 'Hunter'),
  ('subclass', 'hunter', 'de', 'Jäger'),
  ('subclass', 'oath_of_devotion', 'en', 'Oath of Devotion'),
  ('subclass', 'oath_of_devotion', 'de', 'Schwur der Hingabe'),
  ('subclass', 'oath_of_the_ancients', 'en', 'Oath of the Ancients'),
  ('subclass', 'oath_of_the_ancients', 'de', 'Der Schwur der Alten'),
  ('subclass', 'oath_of_vengeance', 'en', 'Oath of Vengeance'),
  ('subclass', 'oath_of_vengeance', 'de', 'Schwur der Rache'),
  ('subclass', 'tempest_domain', 'en', 'Tempest Domain'),
  ('subclass', 'tempest_domain', 'de', 'Domäne des Sturms');

INSERT INTO Lookup_translation (type, code, lang, name) VALUES
  ('species', 'dragonborn', 'en', 'Dragonborn'),
  ('species', 'dragonborn', 'de', 'Drachenblütiger'),
  ('species', 'dwarf', 'en', 'Dwarf'),
  ('species', 'dwarf', 'de', 'Zwerg'),
  ('species', 'elf', 'en', 'Elf'),
  ('species', 'elf', 'de', 'Elf'),
  ('species', 'gnome', 'en', 'Gnome'),
  ('species', 'gnome', 'de', 'Gnom'),
  ('species', 'half_elf', 'en', 'Half-Elf'),
  ('species', 'half_elf', 'de', 'Halbelf'),
  ('species', 'half_orc', 'en', 'Half-Orc'),
  ('species', 'half_orc', 'de', 'Halbork'),
  ('species', 'halfling', 'en', 'Halfling'),
  ('species', 'halfling', 'de', 'Halbling'),
  ('species', 'human', 'en', 'Human'),
  ('species', 'human', 'de', 'Mensch'),
  ('species', 'tiefling', 'en', 'Tiefling'),
  ('species', 'tiefling', 'de', 'Tiefling');

INSERT INTO Lookup_translation (type, code, lang, name) VALUES
  ('background', 'acolyte', 'en', 'Acolyte'),
  ('background', 'acolyte', 'de', 'Akolyth'),
  ('background', 'charlatan', 'en', 'Charlatan'),
  ('background', 'charlatan', 'de', 'Scharlatan'),
  ('background', 'criminal', 'en', 'Criminal'),
  ('background', 'criminal', 'de', 'Krimineller'),
  ('background', 'entertainer', 'en', 'Entertainer'),
  ('background', 'entertainer', 'de', 'Unterhaltungskünstler'),
  ('background', 'folk_hero', 'en', 'Folk Hero'),
  ('background', 'folk_hero', 'de', 'Volksheld'),
  ('background', 'guild_artisan', 'en', 'Guild Artisan'),
  ('background', 'guild_artisan', 'de', 'Gildenhandwerker'),
  ('background', 'hermit', 'en', 'Hermit'),
  ('background', 'hermit', 'de', 'Einsiedler'),
  ('background', 'noble', 'en', 'Noble'),
  ('background', 'noble', 'de', 'Adliger'),
  ('background', 'outlander', 'en', 'Outlander'),
  ('background', 'outlander', 'de', 'Fremdländer'),
  ('background', 'sage', 'en', 'Sage'),
  ('background', 'sage', 'de', 'Weiser'),
  ('background', 'sailor', 'en', 'Sailor'),
  ('background', 'sailor', 'de', 'Seefahrer'),
  ('background', 'soldier', 'en', 'Soldier'),
  ('background', 'soldier', 'de', 'Soldat'),
  ('background', 'urchin', 'en', 'Urchin'),
  ('background', 'urchin', 'de', 'Straßenkind');

INSERT INTO Lookup_translation (type, code, lang, name) VALUES
  ('condition', 'blinded', 'en', 'Blinded'),
  ('condition', 'blinded', 'de', 'Blind'),
  ('condition', 'charmed', 'en', 'Charmed'),
  ('condition', 'charmed', 'de', 'Bezaubert'),
  ('condition', 'deafened', 'en', 'Deafened'),
  ('condition', 'deafened', 'de', 'Taub'),
  ('condition', 'exhaustion', 'en', 'Exhaustion'),
  ('condition', 'exhaustion', 'de', 'Erschöpfung'),
  ('condition', 'frightened', 'en', 'Frightened'),
  ('condition', 'frightened', 'de', 'Verängstigt'),
  ('condition', 'grappled', 'en', 'Grappled'),
  ('condition', 'grappled', 'de', 'Gepackt'),
  ('condition', 'incapacitated', 'en', 'Incapacitated'),
  ('condition', 'incapacitated', 'de', 'Kampfunfähig'),
  ('condition', 'invisible', 'en', 'Invisible'),
  ('condition', 'invisible', 'de', 'Unsichtbar'),
  ('condition', 'paralyzed', 'en', 'Paralyzed'),
  ('condition', 'paralyzed', 'de', 'Gelähmt'),
  ('condition', 'petrified', 'en', 'Petrified'),
  ('condition', 'petrified', 'de', 'Versteinert'),
  ('condition', 'poisoned', 'en', 'Poisoned'),
  ('condition', 'poisoned', 'de', 'Vergiftet'),
  ('condition', 'prone', 'en', 'Prone'),
  ('condition', 'prone', 'de', 'Liegend'),
  ('condition', 'restrained', 'en', 'Restrained'),
  ('condition', 'restrained', 'de', 'Festgesetzt'),
  ('condition', 'stunned', 'en', 'Stunned'),
  ('condition', 'stunned', 'de', 'Betäubt'),
  ('condition', 'unconscious', 'en', 'Unconscious'),
  ('condition', 'unconscious', 'de', 'Bewusstlos');

INSERT INTO Lookup_translation (type, code, lang, name) VALUES
  ('damagetype', 'acid', 'en', 'Acid'),
  ('damagetype', 'acid', 'de', 'Säure'),
  ('damagetype', 'bludgeoning', 'en', 'Bludgeoning'),
  ('damagetype', 'bludgeoning', 'de', 'Wucht'),
  ('damagetype', 'cold', 'en', 'Cold'),
  ('damagetype', 'cold', 'de', 'Kälte'),
  ('damagetype', 'fire', 'en', 'Fire'),
  ('damagetype', 'fire', 'de', 'Feuer'),
  ('damagetype', 'force', 'en', 'Force'),
  ('damagetype', 'force', 'de', 'Energie'),
  ('damagetype', 'lightning', 'en', 'Lightning'),
  ('damagetype', 'lightning', 'de', 'Blitz'),
  ('damagetype', 'necrotic', 'en', 'Necrotic'),
  ('damagetype', 'necrotic', 'de', 'Nekrotisch'),
  ('damagetype', 'piercing', 'en', 'Piercing'),
  ('damagetype', 'piercing', 'de', 'Stich'),
  ('damagetype', 'poison', 'en', 'Poison'),
  ('damagetype', 'poison', 'de', 'Gift'),
  ('damagetype', 'psychic', 'en', 'Psychic'),
  ('damagetype', 'psychic', 'de', 'Psychisch'),
  ('damagetype', 'radiant', 'en', 'Radiant'),
  ('damagetype', 'radiant', 'de', 'Gleißend'),
  ('damagetype', 'slashing', 'en', 'Slashing'),
  ('damagetype', 'slashing', 'de', 'Hieb'),
  ('damagetype', 'thunder', 'en', 'Thunder'),
  ('damagetype', 'thunder', 'de', 'Schall');

INSERT INTO Lookup_translation (type, code, lang, name) VALUES
  ('damage_influence', 'immune', 'en', 'Immune'),
  ('damage_influence', 'immune', 'de', 'Immun'),
  ('damage_influence', 'resistant', 'en', 'Resistant'),
  ('damage_influence', 'resistant', 'de', 'Resistent'),
  ('damage_influence', 'vulnerable', 'en', 'Vulnerable'),
  ('damage_influence', 'vulnerable', 'de', 'Anfällig');

-- 2. Texts of the game components (talents and later others), one row per component and language
--    A NULL field shows the value of the English row (or of Game_component_library).
CREATE TABLE Game_component_library_translation (
  component_id int unsigned NOT NULL,
  lang         varchar(5)   NOT NULL,
  name         varchar(255) NOT NULL,
  description  text         NOT NULL,
  action_type  varchar(255) NULL,
  `range`      varchar(255) NULL,
  attack_type  varchar(255) NULL,
  magic_effect varchar(255) NULL,
  projectile   varchar(255) NULL,
  duration     varchar(255) NULL,
  precondition varchar(255) NULL,
  components   varchar(255) NULL,
  material     varchar(255) NULL,
  PRIMARY KEY (component_id, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. The talent (id 1): English and German texts
INSERT INTO Game_component_library_translation (component_id, lang, name, description, action_type) VALUES
  (1, 'en', 'Polearm Master',
   'When you take the Attack action and attack only with a glaive, halberd or quarterstaff, you can use your bonus action to make one more attack with the other end of the weapon. This attack uses the same ability modifier as the first attack and deals 1d4 bludgeoning damage.\nWhile you wield a glaive, halberd, pike or quarterstaff, other creatures provoke an opportunity attack from you when they enter the reach of the weapon.',
   'Bonus action'),
  (1, 'de', 'Stangenwaffenmeister',
   'Wenn du die Angriffsaktion wählst und nur mit einer Glefe, Hellebarde oder einem Kampfstab angreifst, kannst du deine Bonusaktion aufwenden, um einen weiteren Angriff mit dem anderen Ende der Waffe auszuführen. Dieser Angriff verwendet denselben Attributsmodifikator wie der erste und verursacht 1W4 Wuchtschaden.\nSolange du eine Glefe, Hellebarde, Pike oder einen Kampfstab führst, provozieren andere Kreaturen einen Gelegenheitsangriff durch dich, wenn sie sich in Reichweite der Waffe begeben.',
   'Bonusaktion');
