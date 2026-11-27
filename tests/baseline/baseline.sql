-- Test baseline for the automatic tests (GitHub Actions).
-- Structure of all tables and the data of the game tables.
-- It contains NO user data: users, rate_limit, Characters, character_items
-- and character_calculations have structure only.
-- Never use it on the live database.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Abilities`
--

CREATE TABLE `Abilities` (
  `code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Abilities`
--

INSERT INTO `Abilities` (`code`, `sort_order`) VALUES
('charisma', 6),
('constitution', 3),
('dexterity', 2),
('intelligence', 4),
('strength', 1),
('wisdom', 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Backgrounds`
--

CREATE TABLE `Backgrounds` (
  `code` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Backgrounds`
--

INSERT INTO `Backgrounds` (`code`) VALUES
('acolyte'),
('charlatan'),
('criminal'),
('entertainer'),
('folk_hero'),
('guild_artisan'),
('hermit'),
('noble'),
('outlander'),
('sage'),
('sailor'),
('soldier'),
('urchin');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `calculation_templates`
--

CREATE TABLE `calculation_templates` (
  `id` int UNSIGNED NOT NULL,
  `source_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `item_id` int UNSIGNED DEFAULT NULL,
  `sort_order` smallint UNSIGNED NOT NULL,
  `target` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `operator` enum('set','add','sub','mul','div','min','max','floor') COLLATE utf8mb4_general_ci NOT NULL,
  `value_num` decimal(10,2) DEFAULT NULL,
  `value_ref` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `condition_json` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `calculation_templates`
--

INSERT INTO `calculation_templates` (`id`, `source_type`, `item_id`, `sort_order`, `target`, `operator`, `value_num`, `value_ref`, `condition_json`) VALUES
(1, 'base', NULL, 200, 'char.prof_bonus', 'set', NULL, 'char.level', NULL),
(2, 'base', NULL, 201, 'char.prof_bonus', 'sub', '1.00', NULL, NULL),
(3, 'base', NULL, 202, 'char.prof_bonus', 'div', '4.00', NULL, NULL),
(4, 'base', NULL, 203, 'char.prof_bonus', 'floor', NULL, NULL, NULL),
(5, 'base', NULL, 204, 'char.prof_bonus', 'add', '2.00', NULL, NULL),
(6, 'base', NULL, 210, 'mod.strength', 'set', NULL, 'ability.strength', NULL),
(7, 'base', NULL, 211, 'mod.strength', 'sub', '10.00', NULL, NULL),
(8, 'base', NULL, 212, 'mod.strength', 'div', '2.00', NULL, NULL),
(9, 'base', NULL, 213, 'mod.strength', 'floor', NULL, NULL, NULL),
(10, 'base', NULL, 210, 'mod.dexterity', 'set', NULL, 'ability.dexterity', NULL),
(11, 'base', NULL, 211, 'mod.dexterity', 'sub', '10.00', NULL, NULL),
(12, 'base', NULL, 212, 'mod.dexterity', 'div', '2.00', NULL, NULL),
(13, 'base', NULL, 213, 'mod.dexterity', 'floor', NULL, NULL, NULL),
(14, 'base', NULL, 210, 'mod.constitution', 'set', NULL, 'ability.constitution', NULL),
(15, 'base', NULL, 211, 'mod.constitution', 'sub', '10.00', NULL, NULL),
(16, 'base', NULL, 212, 'mod.constitution', 'div', '2.00', NULL, NULL),
(17, 'base', NULL, 213, 'mod.constitution', 'floor', NULL, NULL, NULL),
(18, 'base', NULL, 210, 'mod.intelligence', 'set', NULL, 'ability.intelligence', NULL),
(19, 'base', NULL, 211, 'mod.intelligence', 'sub', '10.00', NULL, NULL),
(20, 'base', NULL, 212, 'mod.intelligence', 'div', '2.00', NULL, NULL),
(21, 'base', NULL, 213, 'mod.intelligence', 'floor', NULL, NULL, NULL),
(22, 'base', NULL, 210, 'mod.wisdom', 'set', NULL, 'ability.wisdom', NULL),
(23, 'base', NULL, 211, 'mod.wisdom', 'sub', '10.00', NULL, NULL),
(24, 'base', NULL, 212, 'mod.wisdom', 'div', '2.00', NULL, NULL),
(25, 'base', NULL, 213, 'mod.wisdom', 'floor', NULL, NULL, NULL),
(26, 'base', NULL, 210, 'mod.charisma', 'set', NULL, 'ability.charisma', NULL),
(27, 'base', NULL, 211, 'mod.charisma', 'sub', '10.00', NULL, NULL),
(28, 'base', NULL, 212, 'mod.charisma', 'div', '2.00', NULL, NULL),
(29, 'base', NULL, 213, 'mod.charisma', 'floor', NULL, NULL, NULL),
(30, 'base', NULL, 300, 'save.strength', 'set', NULL, 'mod.strength', NULL),
(31, 'base', NULL, 301, 'save.strength', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"save.strength\"}'),
(32, 'base', NULL, 300, 'save.dexterity', 'set', NULL, 'mod.dexterity', NULL),
(33, 'base', NULL, 301, 'save.dexterity', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"save.dexterity\"}'),
(34, 'base', NULL, 300, 'save.constitution', 'set', NULL, 'mod.constitution', NULL),
(35, 'base', NULL, 301, 'save.constitution', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"save.constitution\"}'),
(36, 'base', NULL, 300, 'save.intelligence', 'set', NULL, 'mod.intelligence', NULL),
(37, 'base', NULL, 301, 'save.intelligence', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"save.intelligence\"}'),
(38, 'base', NULL, 300, 'save.wisdom', 'set', NULL, 'mod.wisdom', NULL),
(39, 'base', NULL, 301, 'save.wisdom', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"save.wisdom\"}'),
(40, 'base', NULL, 300, 'save.charisma', 'set', NULL, 'mod.charisma', NULL),
(41, 'base', NULL, 301, 'save.charisma', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"save.charisma\"}'),
(42, 'base', NULL, 300, 'skill.athletics', 'set', NULL, 'mod.strength', NULL),
(43, 'base', NULL, 301, 'skill.athletics', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.athletics\"}'),
(44, 'base', NULL, 300, 'skill.acrobatics', 'set', NULL, 'mod.dexterity', NULL),
(45, 'base', NULL, 301, 'skill.acrobatics', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.acrobatics\"}'),
(46, 'base', NULL, 300, 'skill.sleight_of_hand', 'set', NULL, 'mod.dexterity', NULL),
(47, 'base', NULL, 301, 'skill.sleight_of_hand', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.sleight_of_hand\"}'),
(48, 'base', NULL, 300, 'skill.stealth', 'set', NULL, 'mod.dexterity', NULL),
(49, 'base', NULL, 301, 'skill.stealth', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.stealth\"}'),
(50, 'base', NULL, 300, 'skill.arcana', 'set', NULL, 'mod.intelligence', NULL),
(51, 'base', NULL, 301, 'skill.arcana', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.arcana\"}'),
(52, 'base', NULL, 300, 'skill.history', 'set', NULL, 'mod.intelligence', NULL),
(53, 'base', NULL, 301, 'skill.history', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.history\"}'),
(54, 'base', NULL, 300, 'skill.investigation', 'set', NULL, 'mod.intelligence', NULL),
(55, 'base', NULL, 301, 'skill.investigation', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.investigation\"}'),
(56, 'base', NULL, 300, 'skill.nature', 'set', NULL, 'mod.intelligence', NULL),
(57, 'base', NULL, 301, 'skill.nature', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.nature\"}'),
(58, 'base', NULL, 300, 'skill.religion', 'set', NULL, 'mod.intelligence', NULL),
(59, 'base', NULL, 301, 'skill.religion', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.religion\"}'),
(60, 'base', NULL, 300, 'skill.animal_handling', 'set', NULL, 'mod.wisdom', NULL),
(61, 'base', NULL, 301, 'skill.animal_handling', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.animal_handling\"}'),
(62, 'base', NULL, 300, 'skill.insight', 'set', NULL, 'mod.wisdom', NULL),
(63, 'base', NULL, 301, 'skill.insight', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.insight\"}'),
(64, 'base', NULL, 300, 'skill.medicine', 'set', NULL, 'mod.wisdom', NULL),
(65, 'base', NULL, 301, 'skill.medicine', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.medicine\"}'),
(66, 'base', NULL, 300, 'skill.perception', 'set', NULL, 'mod.wisdom', NULL),
(67, 'base', NULL, 301, 'skill.perception', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.perception\"}'),
(68, 'base', NULL, 300, 'skill.survival', 'set', NULL, 'mod.wisdom', NULL),
(69, 'base', NULL, 301, 'skill.survival', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.survival\"}'),
(70, 'base', NULL, 300, 'skill.deception', 'set', NULL, 'mod.charisma', NULL),
(71, 'base', NULL, 301, 'skill.deception', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.deception\"}'),
(72, 'base', NULL, 300, 'skill.intimidation', 'set', NULL, 'mod.charisma', NULL),
(73, 'base', NULL, 301, 'skill.intimidation', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.intimidation\"}'),
(74, 'base', NULL, 300, 'skill.performance', 'set', NULL, 'mod.charisma', NULL),
(75, 'base', NULL, 301, 'skill.performance', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.performance\"}'),
(76, 'base', NULL, 300, 'skill.persuasion', 'set', NULL, 'mod.charisma', NULL),
(77, 'base', NULL, 301, 'skill.persuasion', 'add', NULL, 'char.prof_bonus', '{\"PROFICIENT\": \"skill.persuasion\"}'),
(78, 'base', NULL, 300, 'combat.initiative', 'set', NULL, 'mod.dexterity', NULL),
(79, 'base', NULL, 400, 'combat.ac_dex', 'set', NULL, 'mod.dexterity', NULL),
(80, 'base', NULL, 410, 'combat.ac', 'set', '10.00', NULL, NULL),
(81, 'base', NULL, 412, 'combat.ac', 'add', NULL, 'combat.ac_dex', NULL),
(82, 'item', 1, 413, 'combat.ac', 'add', '2.00', NULL, '{\"EQUIPPED\": true}'),
(83, 'item', 2, 402, 'combat.ac_dex', 'set', '0.00', NULL, '{\"EQUIPPED\": true}'),
(84, 'item', 2, 411, 'combat.ac', 'set', '16.00', NULL, '{\"EQUIPPED\": true}'),
(85, 'item', 3, 401, 'combat.ac_dex', 'max', '2.00', NULL, '{\"EQUIPPED\": true}'),
(86, 'item', 3, 411, 'combat.ac', 'set', '14.00', NULL, '{\"EQUIPPED\": true}'),
(87, 'item', 4, 214, 'mod.wisdom', 'add', '1.00', NULL, '{\"EQUIPPED\": true}'),
(88, 'item', 5, 302, 'skill.persuasion', 'add', '1.00', NULL, '{\"EQUIPPED\": true}'),
(89, 'base', NULL, 500, 'passive.perception', 'set', '10.00', NULL, NULL),
(90, 'base', NULL, 501, 'passive.perception', 'add', NULL, 'skill.perception', NULL),
(91, 'base', NULL, 500, 'passive.insight', 'set', '10.00', NULL, NULL),
(92, 'base', NULL, 501, 'passive.insight', 'add', NULL, 'skill.insight', NULL),
(93, 'base', NULL, 500, 'passive.investigation', 'set', '10.00', NULL, NULL),
(94, 'base', NULL, 501, 'passive.investigation', 'add', NULL, 'skill.investigation', NULL),
(95, 'base', NULL, 600, 'weapon.ability', 'set', NULL, 'mod.strength', NULL),
(96, 'base', NULL, 601, 'weapon.ability', 'set', NULL, 'mod.dexterity', '{\"WEAPON_TAG\": \"ranged\"}'),
(97, 'base', NULL, 602, 'weapon.ability', 'min', NULL, 'mod.dexterity', '{\"WEAPON_TAG\": \"finesse\"}'),
(98, 'base', NULL, 610, 'weapon.attack', 'set', NULL, 'weapon.ability', NULL),
(99, 'base', NULL, 611, 'weapon.attack', 'add', NULL, 'char.prof_bonus', '{\"WEAPON_PROFICIENT\": true}'),
(100, 'base', NULL, 620, 'weapon.damage_bonus', 'set', NULL, 'weapon.ability', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Characters`
--

CREATE TABLE `Characters` (
  `id` int UNSIGNED NOT NULL,
  `name` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` char(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(400) COLLATE utf8mb4_general_ci NOT NULL,
  `Strength` tinyint NOT NULL,
  `Dexterity` tinyint NOT NULL,
  `Constitution` tinyint NOT NULL,
  `Intelligence` tinyint NOT NULL,
  `Wisdom` tinyint NOT NULL,
  `Charisma` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `character_calculations`
--

CREATE TABLE `character_calculations` (
  `id` int UNSIGNED NOT NULL,
  `character_id` int UNSIGNED NOT NULL,
  `source_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `character_item_id` int UNSIGNED DEFAULT NULL,
  `sort_order` smallint UNSIGNED NOT NULL,
  `target` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `operator` enum('set','add','sub','mul','div','min','max','floor') COLLATE utf8mb4_general_ci NOT NULL,
  `value_num` decimal(10,2) DEFAULT NULL,
  `value_ref` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `condition_json` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `character_items`
--

CREATE TABLE `character_items` (
  `id` int UNSIGNED NOT NULL,
  `character_id` int UNSIGNED NOT NULL,
  `item_id` int UNSIGNED NOT NULL,
  `quantity` smallint UNSIGNED NOT NULL DEFAULT '1',
  `equipped` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Classes`
--

CREATE TABLE `Classes` (
  `class_code` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `subclass_code` varchar(60) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `Level` tinyint NOT NULL,
  `Proficiency_Bonus` tinyint NOT NULL,
  `Hit_Dice` tinyint NOT NULL,
  `Spell_Prep_Method` text COLLATE utf8mb4_general_ci NOT NULL,
  `Spell_Modifier` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Known_Cantrips` tinyint NOT NULL,
  `Known_Spells` tinyint NOT NULL,
  `Spells_Level_1` tinyint NOT NULL,
  `Spells_Level_2` tinyint NOT NULL,
  `Spells_Level_3` tinyint NOT NULL,
  `Spells_Level_4` tinyint NOT NULL,
  `Spells_Level_5` tinyint NOT NULL,
  `Spells_Level_6` tinyint NOT NULL,
  `Spells_Level_7` tinyint NOT NULL,
  `Spells_Level_8` tinyint NOT NULL,
  `Spells_Level_9` tinyint NOT NULL,
  `Arcane_Focus` text COLLATE utf8mb4_general_ci NOT NULL,
  `Ritual_capable` tinyint(1) NOT NULL,
  `Known_Evocations` tinyint NOT NULL,
  `Rage_Amount` tinyint NOT NULL,
  `Focus_Points` tinyint NOT NULL,
  `Sneekatack_Amount` tinyint NOT NULL,
  `Prepared_Spells` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Classes`
--

INSERT INTO `Classes` (`class_code`, `subclass_code`, `Level`, `Proficiency_Bonus`, `Hit_Dice`, `Spell_Prep_Method`, `Spell_Modifier`, `Known_Cantrips`, `Known_Spells`, `Spells_Level_1`, `Spells_Level_2`, `Spells_Level_3`, `Spells_Level_4`, `Spells_Level_5`, `Spells_Level_6`, `Spells_Level_7`, `Spells_Level_8`, `Spells_Level_9`, `Arcane_Focus`, `Ritual_capable`, `Known_Evocations`, `Rage_Amount`, `Focus_Points`, `Sneekatack_Amount`, `Prepared_Spells`) VALUES
('artificer', '', 1, 2, 8, 'Preparation', 'Intelligence', 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 2, 2, 8, 'Preparation', 'Intelligence', 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 3, 2, 8, 'Preparation', 'Intelligence', 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 4, 2, 8, 'Preparation', 'Intelligence', 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 5, 3, 8, 'Preparation', 'Intelligence', 0, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 6, 3, 8, 'Preparation', 'Intelligence', 0, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 7, 3, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 8, 3, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 9, 4, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 10, 4, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 11, 4, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 12, 4, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 13, 5, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 14, 5, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 15, 5, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 16, 5, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 17, 6, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 18, 6, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 19, 6, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('artificer', '', 20, 6, 8, 'Preparation', 'Intelligence', 0, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Tool / Magic Item', 1, 0, 0, 0, 0, 0),
('barbarian', '', 1, 2, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 2, 2, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 3, 2, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 4, 2, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 5, 3, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 6, 3, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 7, 3, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 8, 3, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 9, 4, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 10, 4, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 11, 4, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 12, 4, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 13, 5, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 14, 5, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 15, 5, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 16, 5, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 17, 6, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 18, 6, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 19, 6, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('barbarian', '', 20, 6, 12, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('bard', '', 1, 2, 8, 'Knowledge', 'Charisma', 2, 4, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 2, 2, 8, 'Knowledge', 'Charisma', 2, 5, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 3, 2, 8, 'Knowledge', 'Charisma', 2, 6, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 4, 2, 8, 'Knowledge', 'Charisma', 3, 7, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 5, 3, 8, 'Knowledge', 'Charisma', 3, 8, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 6, 3, 8, 'Knowledge', 'Charisma', 3, 9, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 7, 3, 8, 'Knowledge', 'Charisma', 3, 10, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 8, 3, 8, 'Knowledge', 'Charisma', 3, 11, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 9, 4, 8, 'Knowledge', 'Charisma', 3, 12, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 10, 4, 8, 'Knowledge', 'Charisma', 4, 14, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 11, 4, 8, 'Knowledge', 'Charisma', 4, 15, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 12, 4, 8, 'Knowledge', 'Charisma', 4, 15, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 13, 5, 8, 'Knowledge', 'Charisma', 4, 16, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 14, 5, 8, 'Knowledge', 'Charisma', 4, 18, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 15, 5, 8, 'Knowledge', 'Charisma', 4, 19, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 16, 5, 8, 'Knowledge', 'Charisma', 4, 19, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 17, 6, 8, 'Knowledge', 'Charisma', 4, 20, 4, 3, 3, 3, 2, 1, 1, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 18, 6, 8, 'Knowledge', 'Charisma', 4, 22, 4, 3, 3, 3, 3, 1, 1, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 19, 6, 8, 'Knowledge', 'Charisma', 4, 22, 4, 3, 3, 3, 3, 2, 1, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', '', 20, 6, 8, 'Knowledge', 'Charisma', 4, 22, 4, 3, 3, 3, 3, 2, 2, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 1, 2, 8, 'Knowledge', 'Charisma', 2, 4, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 2, 2, 8, 'Knowledge', 'Charisma', 2, 5, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 3, 2, 8, 'Knowledge', 'Charisma', 2, 6, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 4, 2, 8, 'Knowledge', 'Charisma', 3, 7, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 5, 3, 8, 'Knowledge', 'Charisma', 3, 8, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 6, 3, 8, 'Knowledge', 'Charisma', 3, 9, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 7, 3, 8, 'Knowledge', 'Charisma', 3, 10, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 8, 3, 8, 'Knowledge', 'Charisma', 3, 11, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 9, 4, 8, 'Knowledge', 'Charisma', 3, 12, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 10, 4, 8, 'Knowledge', 'Charisma', 4, 14, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 11, 4, 8, 'Knowledge', 'Charisma', 4, 15, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 12, 4, 8, 'Knowledge', 'Charisma', 4, 15, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 13, 5, 8, 'Knowledge', 'Charisma', 4, 16, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 14, 5, 8, 'Knowledge', 'Charisma', 4, 18, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 15, 5, 8, 'Knowledge', 'Charisma', 4, 19, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 16, 5, 8, 'Knowledge', 'Charisma', 4, 19, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 17, 6, 8, 'Knowledge', 'Charisma', 4, 20, 4, 3, 3, 3, 2, 1, 1, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 18, 6, 8, 'Knowledge', 'Charisma', 4, 22, 4, 3, 3, 3, 3, 1, 1, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 19, 6, 8, 'Knowledge', 'Charisma', 4, 22, 4, 3, 3, 3, 3, 2, 1, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_lore', 20, 6, 8, 'Knowledge', 'Charisma', 4, 22, 4, 3, 3, 3, 3, 2, 2, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 1, 2, 8, 'Knowledge', 'Charisma', 2, 4, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 2, 2, 8, 'Knowledge', 'Charisma', 2, 5, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 3, 2, 8, 'Knowledge', 'Charisma', 2, 6, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 4, 2, 8, 'Knowledge', 'Charisma', 3, 7, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 5, 3, 8, 'Knowledge', 'Charisma', 3, 8, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 6, 3, 8, 'Knowledge', 'Charisma', 3, 9, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 7, 3, 8, 'Knowledge', 'Charisma', 3, 10, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 8, 3, 8, 'Knowledge', 'Charisma', 3, 11, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 9, 4, 8, 'Knowledge', 'Charisma', 3, 12, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 10, 4, 8, 'Knowledge', 'Charisma', 4, 14, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 11, 4, 8, 'Knowledge', 'Charisma', 4, 15, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 12, 4, 8, 'Knowledge', 'Charisma', 4, 15, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 13, 5, 8, 'Knowledge', 'Charisma', 4, 16, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 14, 5, 8, 'Knowledge', 'Charisma', 4, 18, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 15, 5, 8, 'Knowledge', 'Charisma', 4, 19, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 16, 5, 8, 'Knowledge', 'Charisma', 4, 19, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 17, 6, 8, 'Knowledge', 'Charisma', 4, 20, 4, 3, 3, 3, 2, 1, 1, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 18, 6, 8, 'Knowledge', 'Charisma', 4, 22, 4, 3, 3, 3, 3, 1, 1, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 19, 6, 8, 'Knowledge', 'Charisma', 4, 22, 4, 3, 3, 3, 3, 2, 1, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('bard', 'college_of_valor', 20, 6, 8, 'Knowledge', 'Charisma', 4, 22, 4, 3, 3, 3, 3, 2, 2, 1, 1, 'Musical instrument', 1, 0, 0, 0, 0, 0),
('cleric', '', 1, 2, 8, 'Preparation', 'Wisdom', 3, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 2, 2, 8, 'Preparation', 'Wisdom', 3, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 3, 2, 8, 'Preparation', 'Wisdom', 3, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 4, 2, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 5, 3, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 6, 3, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 7, 3, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 8, 3, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 9, 4, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 10, 4, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 11, 4, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 12, 4, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 13, 5, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 14, 5, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 15, 5, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 16, 5, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 17, 6, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 1, 1, 1, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 18, 6, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 3, 1, 1, 1, 1, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 19, 6, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 3, 2, 1, 1, 1, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', '', 20, 6, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 3, 2, 2, 1, 1, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 1, 2, 8, 'Preparation', 'Wisdom', 3, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 2, 2, 8, 'Preparation', 'Wisdom', 3, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 3, 2, 8, 'Preparation', 'Wisdom', 3, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 4, 2, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 5, 3, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 6, 3, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 7, 3, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 8, 3, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 9, 4, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 10, 4, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 11, 4, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 12, 4, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 13, 5, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 14, 5, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 15, 5, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 16, 5, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 17, 6, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 2, 1, 1, 1, 1, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 18, 6, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 3, 1, 1, 1, 1, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 19, 6, 8, 'Preparation', 'Wisdom', 5, 0, 4, 3, 3, 3, 3, 2, 1, 1, 1, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('cleric', 'tempest_domain', 20, 6, 8, 'Preparation', '', 5, 0, 4, 3, 3, 3, 3, 2, 2, 1, 1, 'Holy symbol', 1, 0, 0, 0, 0, 0),
('druid', '', 1, 2, 8, 'Preparation', 'Wisdom', 2, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 2, 2, 8, 'Preparation', 'Wisdom', 2, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 3, 2, 8, 'Preparation', 'Wisdom', 2, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 4, 2, 8, 'Preparation', 'Wisdom', 3, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 5, 3, 8, 'Preparation', 'Wisdom', 3, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 6, 3, 8, 'Preparation', 'Wisdom', 3, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 7, 3, 8, 'Preparation', 'Wisdom', 3, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 8, 3, 8, 'Preparation', 'Wisdom', 3, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 9, 4, 8, 'Preparation', 'Wisdom', 3, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 10, 4, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 11, 4, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 12, 4, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 13, 5, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 14, 5, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 15, 5, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 16, 5, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 17, 6, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 2, 1, 1, 1, 1, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 18, 6, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 3, 1, 1, 1, 1, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 19, 6, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 3, 2, 1, 1, 1, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('druid', '', 20, 6, 8, 'Preparation', 'Wisdom', 4, 0, 4, 3, 3, 3, 3, 2, 2, 1, 1, 'Druidic focus', 1, 0, 0, 0, 0, 0),
('fighter', '', 1, 2, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 2, 2, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 3, 2, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 4, 2, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 5, 3, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 6, 3, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 7, 3, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 8, 3, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 9, 4, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 10, 4, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 11, 4, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 12, 4, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 13, 5, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 14, 5, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 15, 5, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 16, 5, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 17, 6, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 18, 6, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 19, 6, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', '', 20, 6, 10, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 1, 2, 10, 'Knowledge', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 2, 2, 10, 'Knowledge', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 3, 2, 10, 'Knowledge', '', 2, 3, 2, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 4, 2, 10, 'Knowledge', '', 2, 4, 3, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 5, 3, 10, 'Knowledge', '', 2, 4, 3, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 6, 3, 10, 'Knowledge', '', 2, 4, 3, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 7, 3, 10, 'Knowledge', '', 2, 5, 4, 2, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 8, 3, 10, 'Knowledge', '', 2, 6, 4, 2, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 9, 4, 10, 'Knowledge', '', 2, 6, 4, 2, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 10, 4, 10, 'Knowledge', '', 3, 7, 4, 3, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 11, 4, 10, 'Knowledge', '', 3, 8, 4, 3, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 12, 4, 10, 'Knowledge', '', 3, 8, 4, 3, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 13, 5, 10, 'Knowledge', '', 3, 9, 4, 3, 2, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 14, 5, 10, 'Knowledge', '', 3, 10, 4, 3, 2, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 15, 5, 10, 'Knowledge', '', 3, 10, 4, 3, 2, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 16, 5, 10, 'Knowledge', '', 3, 11, 4, 3, 3, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 17, 6, 10, 'Knowledge', '', 3, 11, 4, 3, 3, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 18, 6, 10, 'Knowledge', '', 3, 11, 4, 3, 3, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 19, 6, 10, 'Knowledge', '', 3, 12, 4, 3, 3, 1, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('fighter', 'eldritch_knight', 20, 6, 10, 'Knowledge', '', 3, 13, 4, 3, 3, 1, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('paladin', '', 1, 2, 10, 'Preparation', 'Charisma', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 2, 2, 10, 'Preparation', 'Charisma', 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 3, 2, 10, 'Preparation', 'Charisma', 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 4, 2, 10, 'Preparation', 'Charisma', 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 5, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 6, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 7, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 8, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 9, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 10, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 11, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 12, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 13, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 14, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 15, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 16, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 17, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 18, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 19, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', '', 20, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 1, 2, 10, 'Preparation', 'Charisma', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 2, 2, 10, 'Preparation', 'Charisma', 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 3, 2, 10, 'Preparation', 'Charisma', 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 4, 2, 10, 'Preparation', 'Charisma', 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 5, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 6, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 7, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 8, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 9, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 10, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 11, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 12, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 13, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 14, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 15, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 16, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 17, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 18, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 19, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_devotion', 20, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 1, 2, 10, 'Preparation', 'Charisma', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 2, 2, 10, 'Preparation', 'Charisma', 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 3, 2, 10, 'Preparation', 'Charisma', 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 4, 2, 10, 'Preparation', 'Charisma', 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 5, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 6, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 7, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 8, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 9, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 10, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 11, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 12, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 13, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 14, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 15, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 16, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 17, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 18, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 19, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_the_ancients', 20, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 1, 2, 10, 'Preparation', 'Charisma', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 2, 2, 10, 'Preparation', 'Charisma', 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 3, 2, 10, 'Preparation', 'Charisma', 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 4, 2, 10, 'Preparation', 'Charisma', 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 5, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 6, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 7, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 8, 3, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 9, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 10, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 11, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 12, 4, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 13, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 14, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 15, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 16, 5, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 17, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 18, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 19, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('paladin', 'oath_of_vengeance', 20, 6, 10, 'Preparation', 'Charisma', 0, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Holy symbol', 0, 0, 0, 0, 0, 0),
('ranger', '', 1, 2, 10, 'Knowledge', 'Wisdom', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 2, 2, 10, 'Knowledge', 'Wisdom', 0, 2, 2, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 3, 2, 10, 'Knowledge', 'Wisdom', 0, 3, 3, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 4, 2, 10, 'Knowledge', 'Wisdom', 0, 3, 3, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 5, 3, 10, 'Knowledge', 'Wisdom', 0, 4, 4, 2, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 6, 3, 10, 'Knowledge', 'Wisdom', 0, 4, 4, 2, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 7, 3, 10, 'Knowledge', 'Wisdom', 0, 5, 4, 3, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 8, 3, 10, 'Knowledge', 'Wisdom', 0, 5, 4, 3, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 9, 4, 10, 'Knowledge', 'Wisdom', 0, 6, 4, 3, 2, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 10, 4, 10, 'Knowledge', 'Wisdom', 0, 6, 4, 3, 2, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 11, 4, 10, 'Knowledge', 'Wisdom', 0, 7, 4, 3, 3, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 12, 4, 10, 'Knowledge', 'Wisdom', 0, 7, 4, 3, 3, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 13, 5, 10, 'Knowledge', 'Wisdom', 0, 8, 4, 3, 3, 1, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 14, 5, 10, 'Knowledge', 'Wisdom', 0, 8, 4, 3, 3, 1, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 15, 5, 10, 'Knowledge', 'Wisdom', 0, 9, 4, 3, 3, 2, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 16, 5, 10, 'Knowledge', 'Wisdom', 0, 9, 4, 3, 3, 2, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 17, 6, 10, 'Knowledge', 'Wisdom', 0, 10, 4, 3, 3, 3, 1, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 18, 6, 10, 'Knowledge', 'Wisdom', 0, 10, 4, 3, 3, 3, 1, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 19, 6, 10, 'Knowledge', 'Wisdom', 0, 11, 4, 3, 3, 3, 2, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', '', 20, 6, 10, 'Knowledge', 'Wisdom', 0, 11, 4, 3, 3, 3, 2, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 1, 2, 10, 'Knowledge', 'Wisdom', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 2, 2, 10, 'Knowledge', 'Wisdom', 0, 2, 2, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 3, 2, 10, 'Knowledge', 'Wisdom', 0, 3, 3, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 4, 2, 10, 'Knowledge', 'Wisdom', 0, 3, 3, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 5, 3, 10, 'Knowledge', 'Wisdom', 0, 4, 4, 2, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 6, 3, 10, 'Knowledge', 'Wisdom', 0, 4, 4, 2, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 7, 3, 10, 'Knowledge', 'Wisdom', 0, 5, 4, 3, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 8, 3, 10, 'Knowledge', 'Wisdom', 0, 5, 4, 3, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 9, 4, 10, 'Knowledge', 'Wisdom', 0, 6, 4, 3, 2, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 10, 4, 10, 'Knowledge', 'Wisdom', 0, 6, 4, 3, 2, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 11, 4, 10, 'Knowledge', 'Wisdom', 0, 7, 4, 3, 3, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 12, 4, 10, 'Knowledge', 'Wisdom', 0, 7, 4, 3, 3, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 13, 5, 10, 'Knowledge', 'Wisdom', 0, 8, 4, 3, 3, 1, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 14, 5, 10, 'Knowledge', 'Wisdom', 0, 8, 4, 3, 3, 1, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 15, 5, 10, 'Knowledge', 'Wisdom', 0, 9, 4, 3, 3, 2, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 16, 5, 10, 'Knowledge', 'Wisdom', 0, 9, 4, 3, 3, 2, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 17, 6, 10, 'Knowledge', 'Wisdom', 0, 10, 4, 3, 3, 3, 1, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 18, 6, 10, 'Knowledge', 'Wisdom', 0, 10, 4, 3, 3, 3, 1, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 19, 6, 10, 'Knowledge', 'Wisdom', 0, 11, 4, 3, 3, 3, 2, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'beast_master', 20, 6, 10, 'Knowledge', 'Wisdom', 0, 11, 4, 3, 3, 3, 2, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 1, 2, 10, 'Knowledge', 'Wisdom', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 2, 2, 10, 'Knowledge', 'Wisdom', 0, 2, 2, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 3, 2, 10, 'Knowledge', 'Wisdom', 0, 3, 3, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 4, 2, 10, 'Knowledge', 'Wisdom', 0, 3, 3, 0, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 5, 3, 10, 'Knowledge', 'Wisdom', 0, 4, 4, 2, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 6, 3, 10, 'Knowledge', 'Wisdom', 0, 4, 4, 2, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 7, 3, 10, 'Knowledge', 'Wisdom', 0, 5, 4, 3, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 8, 3, 10, 'Knowledge', 'Wisdom', 0, 5, 4, 3, 0, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 9, 4, 10, 'Knowledge', 'Wisdom', 0, 6, 4, 3, 2, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 10, 4, 10, 'Knowledge', 'Wisdom', 0, 6, 4, 3, 2, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 11, 4, 10, 'Knowledge', 'Wisdom', 0, 7, 4, 3, 3, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 12, 4, 10, 'Knowledge', 'Wisdom', 0, 7, 4, 3, 3, 0, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 13, 5, 10, 'Knowledge', 'Wisdom', 0, 8, 4, 3, 3, 1, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 14, 5, 10, 'Knowledge', 'Wisdom', 0, 8, 4, 3, 3, 1, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 15, 5, 10, 'Knowledge', 'Wisdom', 0, 9, 4, 3, 3, 2, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 16, 5, 10, 'Knowledge', 'Wisdom', 0, 9, 4, 3, 3, 2, 0, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 17, 6, 10, 'Knowledge', 'Wisdom', 0, 10, 4, 3, 3, 3, 1, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 18, 6, 10, 'Knowledge', 'Wisdom', 0, 10, 4, 3, 3, 3, 1, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 19, 6, 10, 'Knowledge', 'Wisdom', 0, 11, 4, 3, 3, 3, 2, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('ranger', 'hunter', 20, 6, 10, 'Knowledge', 'Wisdom', 0, 11, 4, 3, 3, 3, 2, 0, 0, 0, 0, '–', 0, 0, 0, 0, 0, 0),
('rogue', '', 1, 2, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 2, 2, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 3, 2, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 4, 2, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 5, 3, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 6, 3, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 7, 3, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 8, 3, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 9, 4, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 10, 4, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 11, 4, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 12, 4, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 13, 5, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 14, 5, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 15, 5, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 16, 5, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 17, 6, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 18, 6, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 19, 6, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('rogue', '', 20, 6, 8, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0),
('warlock', '', 1, 2, 8, 'Knowledge', 'Charisma', 2, 2, 1, 0, 0, 0, 0, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 2, 2, 8, 'Knowledge', 'Charisma', 2, 3, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 3, 2, 8, 'Knowledge', 'Charisma', 2, 4, 0, 2, 0, 0, 0, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 4, 2, 8, 'Knowledge', 'Charisma', 3, 5, 0, 2, 0, 0, 0, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 5, 3, 8, 'Knowledge', 'Charisma', 3, 6, 0, 0, 2, 0, 0, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 6, 3, 8, 'Knowledge', 'Charisma', 3, 7, 0, 0, 2, 0, 0, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 7, 3, 8, 'Knowledge', 'Charisma', 3, 8, 0, 0, 0, 2, 0, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 8, 3, 8, 'Knowledge', 'Charisma', 3, 9, 0, 0, 0, 2, 0, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 9, 4, 8, 'Knowledge', 'Charisma', 3, 10, 0, 0, 0, 0, 2, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 10, 4, 8, 'Knowledge', 'Charisma', 4, 10, 0, 0, 0, 0, 2, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 11, 4, 8, 'Knowledge', 'Charisma', 4, 11, 0, 0, 0, 0, 3, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 12, 4, 8, 'Knowledge', 'Charisma', 4, 11, 0, 0, 0, 0, 3, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 13, 5, 8, 'Knowledge', 'Charisma', 4, 12, 0, 0, 0, 0, 3, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 14, 5, 8, 'Knowledge', 'Charisma', 4, 12, 0, 0, 0, 0, 3, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 15, 5, 8, 'Knowledge', 'Charisma', 4, 13, 0, 0, 0, 0, 3, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 16, 5, 8, 'Knowledge', 'Charisma', 4, 13, 0, 0, 0, 0, 3, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 17, 6, 8, 'Knowledge', 'Charisma', 4, 14, 0, 0, 0, 0, 4, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 18, 6, 8, 'Knowledge', 'Charisma', 4, 14, 0, 0, 0, 0, 4, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 19, 6, 8, 'Knowledge', 'Charisma', 4, 15, 0, 0, 0, 0, 4, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('warlock', '', 20, 6, 8, 'Knowledge', 'Charisma', 4, 15, 0, 0, 0, 0, 4, 0, 0, 0, 0, 'Arcane focus', 0, 0, 0, 0, 0, 0),
('wizard', '', 1, 2, 6, 'Wizard', 'Intelligence', 3, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 2, 2, 6, 'Wizard', 'Intelligence', 3, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 3, 2, 6, 'Wizard', 'Intelligence', 3, 0, 4, 2, 0, 0, 0, 0, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 4, 2, 6, 'Wizard', 'Intelligence', 4, 0, 4, 3, 0, 0, 0, 0, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 5, 3, 6, 'Wizard', 'Intelligence', 4, 0, 4, 3, 2, 0, 0, 0, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 6, 3, 6, 'Wizard', 'Intelligence', 4, 0, 4, 3, 3, 0, 0, 0, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 7, 3, 6, 'Wizard', 'Intelligence', 4, 0, 4, 3, 3, 1, 0, 0, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 8, 3, 6, 'Wizard', 'Intelligence', 4, 0, 4, 3, 3, 2, 0, 0, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 9, 4, 6, 'Wizard', 'Intelligence', 4, 0, 4, 3, 3, 3, 1, 0, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 10, 4, 6, 'Wizard', 'Intelligence', 5, 0, 4, 3, 3, 3, 2, 0, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 11, 4, 6, 'Wizard', 'Intelligence', 5, 0, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 12, 4, 6, 'Wizard', 'Intelligence', 5, 0, 4, 3, 3, 3, 2, 1, 0, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 13, 5, 6, 'Wizard', 'Intelligence', 5, 0, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 14, 5, 6, 'Wizard', 'Intelligence', 5, 0, 4, 3, 3, 3, 2, 1, 1, 0, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 15, 5, 6, 'Wizard', 'Intelligence', 5, 0, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 16, 5, 6, 'Wizard', 'Intelligence', 5, 0, 4, 3, 3, 3, 2, 1, 1, 1, 0, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 17, 6, 6, 'Wizard', 'Intelligence', 5, 0, 4, 3, 3, 3, 2, 1, 1, 1, 1, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 18, 6, 6, 'Wizard', 'Intelligence', 5, 0, 4, 3, 3, 3, 3, 1, 1, 1, 1, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 19, 6, 6, 'Wizard', 'Intelligence', 5, 0, 4, 3, 3, 3, 3, 2, 1, 1, 1, 'Arcane focus', 1, 0, 0, 0, 0, 0),
('wizard', '', 20, 6, 6, 'Wizard', 'Intelligence', 5, 0, 4, 3, 3, 3, 3, 2, 2, 1, 1, 'Arcane focus', 1, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Conditions`
--

CREATE TABLE `Conditions` (
  `code` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Conditions`
--

INSERT INTO `Conditions` (`code`) VALUES
('blinded'),
('charmed'),
('deafened'),
('exhaustion'),
('frightened'),
('grappled'),
('incapacitated'),
('invisible'),
('paralyzed'),
('petrified'),
('poisoned'),
('prone'),
('restrained'),
('stunned'),
('unconscious');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Damagetypes`
--

CREATE TABLE `Damagetypes` (
  `code` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Damagetypes`
--

INSERT INTO `Damagetypes` (`code`) VALUES
('acid'),
('bludgeoning'),
('cold'),
('fire'),
('force'),
('lightning'),
('necrotic'),
('piercing'),
('poison'),
('psychic'),
('radiant'),
('slashing'),
('thunder');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Damage_influence`
--

CREATE TABLE `Damage_influence` (
  `code` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Damage_influence`
--

INSERT INTO `Damage_influence` (`code`) VALUES
('immune'),
('resistant'),
('vulnerable');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Game_component_library`
--

CREATE TABLE `Game_component_library` (
  `id` int UNSIGNED NOT NULL,
  `type` set('weapon','spell','class_ability','talent','item','armor','shield') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` char(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `action_type` char(255) COLLATE utf8mb4_general_ci NOT NULL,
  `range` char(255) COLLATE utf8mb4_general_ci NOT NULL,
  `attribute` char(255) COLLATE utf8mb4_general_ci NOT NULL,
  `attack_type` char(255) COLLATE utf8mb4_general_ci NOT NULL,
  `damage_type` char(255) COLLATE utf8mb4_general_ci NOT NULL,
  `magic_effect` char(255) COLLATE utf8mb4_general_ci NOT NULL,
  `projectile` char(255) COLLATE utf8mb4_general_ci NOT NULL,
  `uses` int NOT NULL,
  `uses_spent` int NOT NULL,
  `duration` char(255) COLLATE utf8mb4_general_ci NOT NULL,
  `precondition` char(255) COLLATE utf8mb4_general_ci NOT NULL,
  `components` char(255) COLLATE utf8mb4_general_ci NOT NULL,
  `material` char(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Game_component_library`
--

INSERT INTO `Game_component_library` (`id`, `type`, `name`, `description`, `action_type`, `range`, `attribute`, `attack_type`, `damage_type`, `magic_effect`, `projectile`, `uses`, `uses_spent`, `duration`, `precondition`, `components`, `material`) VALUES
(1, 'talent', 'Stangenwaffenmeister', 'Wenn du die Angriffsaktion wählst und nur mit einer Glefe, Hellebarde oder einem Kampfstab angreifst, kannst du deine Bonusaktion aufwenden, um einen weiteren Angriff mit dem anderen Ende der Waffe auszuführen. Dieser Angriff verwendet denselben Attributsmodifikator wie der erste und verursacht 1W4 Wuchtschaden. \r\nSolange du eine Glefe, Hellebarde, Pike oder einen Kampfstab führst, provozieren andere Kreaturen einen Gelegenheitsangriff durch dich, wenn sie sich in Reichweite der Waffe begeben.												\r\n', 'Bonus', '', '', '', '', '', '', 0, 0, '', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Game_component_library_translation`
--

CREATE TABLE `Game_component_library_translation` (
  `component_id` int UNSIGNED NOT NULL,
  `lang` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `action_type` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `range` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attack_type` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `magic_effect` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `projectile` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `duration` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `precondition` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `components` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `material` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Game_component_library_translation`
--

INSERT INTO `Game_component_library_translation` (`component_id`, `lang`, `name`, `description`, `action_type`, `range`, `attack_type`, `magic_effect`, `projectile`, `duration`, `precondition`, `components`, `material`) VALUES
(1, 'de', 'Stangenwaffenmeister', 'Wenn du die Angriffsaktion wählst und nur mit einer Glefe, Hellebarde oder einem Kampfstab angreifst, kannst du deine Bonusaktion aufwenden, um einen weiteren Angriff mit dem anderen Ende der Waffe auszuführen. Dieser Angriff verwendet denselben Attributsmodifikator wie der erste und verursacht 1W4 Wuchtschaden.\nSolange du eine Glefe, Hellebarde, Pike oder einen Kampfstab führst, provozieren andere Kreaturen einen Gelegenheitsangriff durch dich, wenn sie sich in Reichweite der Waffe begeben.', 'Bonusaktion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1, 'en', 'Polearm Master', 'When you take the Attack action and attack only with a glaive, halberd or quarterstaff, you can use your bonus action to make one more attack with the other end of the weapon. This attack uses the same ability modifier as the first attack and deals 1d4 bludgeoning damage.\nWhile you wield a glaive, halberd, pike or quarterstaff, other creatures provoke an opportunity attack from you when they enter the reach of the weapon.', 'Bonus action', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `items`
--

CREATE TABLE `items` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `weight_lb` decimal(8,2) DEFAULT NULL,
  `rarity_code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'mundane',
  `cost_gp` decimal(10,2) DEFAULT NULL,
  `damage_dice` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `damage_dice_versatile` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `damage_type_code` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `range_normal_ft` smallint UNSIGNED DEFAULT NULL,
  `range_long_ft` smallint UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `items`
--

INSERT INTO `items` (`id`, `name`, `weight_lb`, `rarity_code`, `cost_gp`, `damage_dice`, `damage_dice_versatile`, `damage_type_code`, `range_normal_ft`, `range_long_ft`, `created_at`) VALUES
(1, 'Shield', '6.00', 'mundane', '10.00', NULL, NULL, NULL, NULL, NULL, '2026-11-26 10:45:37'),
(2, 'Chain Mail', '55.00', 'mundane', '75.00', NULL, NULL, NULL, NULL, NULL, '2026-11-26 10:45:37'),
(3, 'Scale Mail', '45.00', 'mundane', '50.00', NULL, NULL, NULL, NULL, NULL, '2026-11-26 10:45:37'),
(4, 'Ring of Wisdom', NULL, 'uncommon', NULL, NULL, NULL, NULL, NULL, NULL, '2026-11-26 10:45:37'),
(5, 'Necklace of Persuasion', NULL, 'uncommon', NULL, NULL, NULL, NULL, NULL, NULL, '2026-11-26 10:45:37'),
(6, 'Dagger', '1.00', 'mundane', '2.00', '1d4', NULL, 'piercing', 20, 60, '2026-11-26 14:45:13'),
(7, 'Longsword', '3.00', 'mundane', '15.00', '1d8', '1d10', 'slashing', NULL, NULL, '2026-11-26 14:45:13'),
(8, 'Greataxe', '7.00', 'mundane', '30.00', '1d12', NULL, 'slashing', NULL, NULL, '2026-11-26 14:45:13'),
(9, 'Shortbow', '2.00', 'mundane', '25.00', '1d6', NULL, 'piercing', 80, 320, '2026-11-26 14:45:13'),
(10, 'Light Crossbow', '5.00', 'mundane', '25.00', '1d8', NULL, 'piercing', 80, 320, '2026-11-26 14:45:13');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `items_translation`
--

CREATE TABLE `items_translation` (
  `item_id` int UNSIGNED NOT NULL,
  `lang` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `items_translation`
--

INSERT INTO `items_translation` (`item_id`, `lang`, `name`) VALUES
(6, 'de', 'Dolch'),
(5, 'de', 'Halskette der Überzeugung'),
(2, 'de', 'Kettenpanzer'),
(9, 'de', 'Kurzbogen'),
(7, 'de', 'Langschwert'),
(10, 'de', 'Leichte Armbrust'),
(4, 'de', 'Ring der Weisheit'),
(1, 'de', 'Schild'),
(3, 'de', 'Schuppenpanzer'),
(8, 'de', 'Zweihandaxt');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `item_rarities`
--

CREATE TABLE `item_rarities` (
  `code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` smallint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `item_rarities`
--

INSERT INTO `item_rarities` (`code`, `sort_order`) VALUES
('artifact', 6),
('common', 1),
('legendary', 5),
('mundane', 0),
('rare', 3),
('uncommon', 2),
('very_rare', 4);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `item_tags`
--

CREATE TABLE `item_tags` (
  `item_id` int UNSIGNED NOT NULL,
  `tag_code` varchar(40) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `item_tags`
--

INSERT INTO `item_tags` (`item_id`, `tag_code`) VALUES
(2, 'armor'),
(3, 'armor'),
(6, 'finesse'),
(8, 'heavy'),
(2, 'heavy_armor'),
(6, 'light'),
(10, 'loading'),
(4, 'magic'),
(5, 'magic'),
(7, 'martial'),
(8, 'martial'),
(3, 'medium_armor'),
(6, 'melee'),
(7, 'melee'),
(8, 'melee'),
(9, 'needs_ammunition'),
(10, 'needs_ammunition'),
(9, 'ranged'),
(10, 'ranged'),
(1, 'shield'),
(6, 'simple'),
(9, 'simple'),
(10, 'simple'),
(2, 'stealth_disadvantage'),
(3, 'stealth_disadvantage'),
(6, 'thrown'),
(8, 'two_handed'),
(9, 'two_handed'),
(10, 'two_handed'),
(7, 'versatile'),
(6, 'weapon'),
(7, 'weapon'),
(8, 'weapon'),
(9, 'weapon'),
(10, 'weapon');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Lookup_translation`
--

CREATE TABLE `Lookup_translation` (
  `type` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `lang` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Lookup_translation`
--

INSERT INTO `Lookup_translation` (`type`, `code`, `lang`, `name`) VALUES
('background', 'acolyte', 'de', 'Akolyth'),
('background', 'acolyte', 'en', 'Acolyte'),
('background', 'charlatan', 'de', 'Scharlatan'),
('background', 'charlatan', 'en', 'Charlatan'),
('background', 'criminal', 'de', 'Krimineller'),
('background', 'criminal', 'en', 'Criminal'),
('background', 'entertainer', 'de', 'Unterhaltungskünstler'),
('background', 'entertainer', 'en', 'Entertainer'),
('background', 'folk_hero', 'de', 'Volksheld'),
('background', 'folk_hero', 'en', 'Folk Hero'),
('background', 'guild_artisan', 'de', 'Gildenhandwerker'),
('background', 'guild_artisan', 'en', 'Guild Artisan'),
('background', 'hermit', 'de', 'Einsiedler'),
('background', 'hermit', 'en', 'Hermit'),
('background', 'noble', 'de', 'Adliger'),
('background', 'noble', 'en', 'Noble'),
('background', 'outlander', 'de', 'Fremdländer'),
('background', 'outlander', 'en', 'Outlander'),
('background', 'sage', 'de', 'Weiser'),
('background', 'sage', 'en', 'Sage'),
('background', 'sailor', 'de', 'Seefahrer'),
('background', 'sailor', 'en', 'Sailor'),
('background', 'soldier', 'de', 'Soldat'),
('background', 'soldier', 'en', 'Soldier'),
('background', 'urchin', 'de', 'Straßenkind'),
('background', 'urchin', 'en', 'Urchin'),
('class', 'artificer', 'de', 'Erfinder'),
('class', 'artificer', 'en', 'Artificer'),
('class', 'barbarian', 'de', 'Barbar'),
('class', 'barbarian', 'en', 'Barbarian'),
('class', 'bard', 'de', 'Barde'),
('class', 'bard', 'en', 'Bard'),
('class', 'cleric', 'de', 'Kleriker'),
('class', 'cleric', 'en', 'Cleric'),
('class', 'druid', 'de', 'Druide'),
('class', 'druid', 'en', 'Druid'),
('class', 'fighter', 'de', 'Kämpfer'),
('class', 'fighter', 'en', 'Fighter'),
('class', 'paladin', 'de', 'Paladin'),
('class', 'paladin', 'en', 'Paladin'),
('class', 'ranger', 'de', 'Waldläufer'),
('class', 'ranger', 'en', 'Ranger'),
('class', 'rogue', 'de', 'Schurke'),
('class', 'rogue', 'en', 'Rogue'),
('class', 'warlock', 'de', 'Hexenmeister'),
('class', 'warlock', 'en', 'Warlock'),
('class', 'wizard', 'de', 'Magier'),
('class', 'wizard', 'en', 'Wizard'),
('condition', 'blinded', 'de', 'Blind'),
('condition', 'blinded', 'en', 'Blinded'),
('condition', 'charmed', 'de', 'Bezaubert'),
('condition', 'charmed', 'en', 'Charmed'),
('condition', 'deafened', 'de', 'Taub'),
('condition', 'deafened', 'en', 'Deafened'),
('condition', 'exhaustion', 'de', 'Erschöpfung'),
('condition', 'exhaustion', 'en', 'Exhaustion'),
('condition', 'frightened', 'de', 'Verängstigt'),
('condition', 'frightened', 'en', 'Frightened'),
('condition', 'grappled', 'de', 'Gepackt'),
('condition', 'grappled', 'en', 'Grappled'),
('condition', 'incapacitated', 'de', 'Kampfunfähig'),
('condition', 'incapacitated', 'en', 'Incapacitated'),
('condition', 'invisible', 'de', 'Unsichtbar'),
('condition', 'invisible', 'en', 'Invisible'),
('condition', 'paralyzed', 'de', 'Gelähmt'),
('condition', 'paralyzed', 'en', 'Paralyzed'),
('condition', 'petrified', 'de', 'Versteinert'),
('condition', 'petrified', 'en', 'Petrified'),
('condition', 'poisoned', 'de', 'Vergiftet'),
('condition', 'poisoned', 'en', 'Poisoned'),
('condition', 'prone', 'de', 'Liegend'),
('condition', 'prone', 'en', 'Prone'),
('condition', 'restrained', 'de', 'Festgesetzt'),
('condition', 'restrained', 'en', 'Restrained'),
('condition', 'stunned', 'de', 'Betäubt'),
('condition', 'stunned', 'en', 'Stunned'),
('condition', 'unconscious', 'de', 'Bewusstlos'),
('condition', 'unconscious', 'en', 'Unconscious'),
('damagetype', 'acid', 'de', 'Säure'),
('damagetype', 'acid', 'en', 'Acid'),
('damagetype', 'bludgeoning', 'de', 'Wucht'),
('damagetype', 'bludgeoning', 'en', 'Bludgeoning'),
('damagetype', 'cold', 'de', 'Kälte'),
('damagetype', 'cold', 'en', 'Cold'),
('damagetype', 'fire', 'de', 'Feuer'),
('damagetype', 'fire', 'en', 'Fire'),
('damagetype', 'force', 'de', 'Energie'),
('damagetype', 'force', 'en', 'Force'),
('damagetype', 'lightning', 'de', 'Blitz'),
('damagetype', 'lightning', 'en', 'Lightning'),
('damagetype', 'necrotic', 'de', 'Nekrotisch'),
('damagetype', 'necrotic', 'en', 'Necrotic'),
('damagetype', 'piercing', 'de', 'Stich'),
('damagetype', 'piercing', 'en', 'Piercing'),
('damagetype', 'poison', 'de', 'Gift'),
('damagetype', 'poison', 'en', 'Poison'),
('damagetype', 'psychic', 'de', 'Psychisch'),
('damagetype', 'psychic', 'en', 'Psychic'),
('damagetype', 'radiant', 'de', 'Gleißend'),
('damagetype', 'radiant', 'en', 'Radiant'),
('damagetype', 'slashing', 'de', 'Hieb'),
('damagetype', 'slashing', 'en', 'Slashing'),
('damagetype', 'thunder', 'de', 'Schall'),
('damagetype', 'thunder', 'en', 'Thunder'),
('damage_influence', 'immune', 'de', 'Immun'),
('damage_influence', 'immune', 'en', 'Immune'),
('damage_influence', 'resistant', 'de', 'Resistent'),
('damage_influence', 'resistant', 'en', 'Resistant'),
('damage_influence', 'vulnerable', 'de', 'Anfällig'),
('damage_influence', 'vulnerable', 'en', 'Vulnerable'),
('item_rarity', 'artifact', 'de', 'Artefakt'),
('item_rarity', 'artifact', 'en', 'Artifact'),
('item_rarity', 'common', 'de', 'Gewöhnlich'),
('item_rarity', 'common', 'en', 'Common'),
('item_rarity', 'legendary', 'de', 'Legendär'),
('item_rarity', 'legendary', 'en', 'Legendary'),
('item_rarity', 'mundane', 'de', 'Nichtmagisch'),
('item_rarity', 'mundane', 'en', 'Mundane'),
('item_rarity', 'rare', 'de', 'Selten'),
('item_rarity', 'rare', 'en', 'Rare'),
('item_rarity', 'uncommon', 'de', 'Ungewöhnlich'),
('item_rarity', 'uncommon', 'en', 'Uncommon'),
('item_rarity', 'very_rare', 'de', 'Sehr selten'),
('item_rarity', 'very_rare', 'en', 'Very rare'),
('item_tag', 'ammunition', 'de', 'Munition'),
('item_tag', 'ammunition', 'en', 'Ammunition'),
('item_tag', 'armor', 'de', 'Rüstung'),
('item_tag', 'armor', 'en', 'Armor'),
('item_tag', 'consumable', 'de', 'Verbrauchbar'),
('item_tag', 'consumable', 'en', 'Consumable'),
('item_tag', 'container', 'de', 'Behälter'),
('item_tag', 'container', 'en', 'Container'),
('item_tag', 'finesse', 'de', 'Finesse'),
('item_tag', 'finesse', 'en', 'Finesse'),
('item_tag', 'gear', 'de', 'Ausrüstung'),
('item_tag', 'gear', 'en', 'Gear'),
('item_tag', 'heavy', 'de', 'Schwer'),
('item_tag', 'heavy', 'en', 'Heavy'),
('item_tag', 'heavy_armor', 'de', 'Schwere Rüstung'),
('item_tag', 'heavy_armor', 'en', 'Heavy armor'),
('item_tag', 'light', 'de', 'Leicht'),
('item_tag', 'light', 'en', 'Light'),
('item_tag', 'light_armor', 'de', 'Leichte Rüstung'),
('item_tag', 'light_armor', 'en', 'Light armor'),
('item_tag', 'loading', 'de', 'Laden'),
('item_tag', 'loading', 'en', 'Loading'),
('item_tag', 'magic', 'de', 'Magisch'),
('item_tag', 'magic', 'en', 'Magic'),
('item_tag', 'martial', 'de', 'Kriegswaffe'),
('item_tag', 'martial', 'en', 'Martial'),
('item_tag', 'medium_armor', 'de', 'Mittelschwere Rüstung'),
('item_tag', 'medium_armor', 'en', 'Medium armor'),
('item_tag', 'melee', 'de', 'Nahkampf'),
('item_tag', 'melee', 'en', 'Melee'),
('item_tag', 'needs_ammunition', 'de', 'Benötigt Munition'),
('item_tag', 'needs_ammunition', 'en', 'Needs ammunition'),
('item_tag', 'potion', 'de', 'Trank'),
('item_tag', 'potion', 'en', 'Potion'),
('item_tag', 'ranged', 'de', 'Fernkampf'),
('item_tag', 'ranged', 'en', 'Ranged'),
('item_tag', 'reach', 'de', 'Reichweite'),
('item_tag', 'reach', 'en', 'Reach'),
('item_tag', 'scroll', 'de', 'Schriftrolle'),
('item_tag', 'scroll', 'en', 'Scroll'),
('item_tag', 'shield', 'de', 'Schild'),
('item_tag', 'shield', 'en', 'Shield'),
('item_tag', 'simple', 'de', 'Einfach'),
('item_tag', 'simple', 'en', 'Simple'),
('item_tag', 'special', 'de', 'Speziell'),
('item_tag', 'special', 'en', 'Special'),
('item_tag', 'stealth_disadvantage', 'de', 'Nachteil auf Heimlichkeit'),
('item_tag', 'stealth_disadvantage', 'en', 'Disadvantage on Stealth'),
('item_tag', 'thrown', 'de', 'Wurfwaffe'),
('item_tag', 'thrown', 'en', 'Thrown'),
('item_tag', 'tool', 'de', 'Werkzeug'),
('item_tag', 'tool', 'en', 'Tool'),
('item_tag', 'two_handed', 'de', 'Zweihändig'),
('item_tag', 'two_handed', 'en', 'Two-handed'),
('item_tag', 'versatile', 'de', 'Vielseitig'),
('item_tag', 'versatile', 'en', 'Versatile'),
('item_tag', 'weapon', 'de', 'Waffe'),
('item_tag', 'weapon', 'en', 'Weapon'),
('species', 'dragonborn', 'de', 'Drachenblütiger'),
('species', 'dragonborn', 'en', 'Dragonborn'),
('species', 'dwarf', 'de', 'Zwerg'),
('species', 'dwarf', 'en', 'Dwarf'),
('species', 'elf', 'de', 'Elf'),
('species', 'elf', 'en', 'Elf'),
('species', 'gnome', 'de', 'Gnom'),
('species', 'gnome', 'en', 'Gnome'),
('species', 'halfling', 'de', 'Halbling'),
('species', 'halfling', 'en', 'Halfling'),
('species', 'half_elf', 'de', 'Halbelf'),
('species', 'half_elf', 'en', 'Half-Elf'),
('species', 'half_orc', 'de', 'Halbork'),
('species', 'half_orc', 'en', 'Half-Orc'),
('species', 'human', 'de', 'Mensch'),
('species', 'human', 'en', 'Human'),
('species', 'tiefling', 'de', 'Tiefling'),
('species', 'tiefling', 'en', 'Tiefling'),
('subclass', 'beast_master', 'de', 'Herr der Tiere'),
('subclass', 'beast_master', 'en', 'Beast Master'),
('subclass', 'college_of_lore', 'de', 'Schule des Wissens'),
('subclass', 'college_of_lore', 'en', 'College of Lore'),
('subclass', 'college_of_valor', 'de', 'Schule des Wagemuts'),
('subclass', 'college_of_valor', 'en', 'College of Valor'),
('subclass', 'eldritch_knight', 'de', 'Mystischer Ritter'),
('subclass', 'eldritch_knight', 'en', 'Eldritch Knight'),
('subclass', 'hunter', 'de', 'Jäger'),
('subclass', 'hunter', 'en', 'Hunter'),
('subclass', 'oath_of_devotion', 'de', 'Schwur der Hingabe'),
('subclass', 'oath_of_devotion', 'en', 'Oath of Devotion'),
('subclass', 'oath_of_the_ancients', 'de', 'Der Schwur der Alten'),
('subclass', 'oath_of_the_ancients', 'en', 'Oath of the Ancients'),
('subclass', 'oath_of_vengeance', 'de', 'Schwur der Rache'),
('subclass', 'oath_of_vengeance', 'en', 'Oath of Vengeance'),
('subclass', 'tempest_domain', 'de', 'Domäne des Sturms'),
('subclass', 'tempest_domain', 'en', 'Tempest Domain');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rate_limit`
--

CREATE TABLE `rate_limit` (
  `action` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `subject_hash` char(64) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Skills`
--

CREATE TABLE `Skills` (
  `code` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `ability_code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Skills`
--

INSERT INTO `Skills` (`code`, `ability_code`, `sort_order`) VALUES
('acrobatics', 'dexterity', 2),
('animal_handling', 'wisdom', 10),
('arcana', 'intelligence', 5),
('athletics', 'strength', 1),
('deception', 'charisma', 15),
('history', 'intelligence', 6),
('insight', 'wisdom', 11),
('intimidation', 'charisma', 16),
('investigation', 'intelligence', 7),
('medicine', 'wisdom', 12),
('nature', 'intelligence', 8),
('perception', 'wisdom', 13),
('performance', 'charisma', 17),
('persuasion', 'charisma', 18),
('religion', 'intelligence', 9),
('sleight_of_hand', 'dexterity', 3),
('stealth', 'dexterity', 4),
('survival', 'wisdom', 14);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Species`
--

CREATE TABLE `Species` (
  `code` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `movement` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `Species`
--

INSERT INTO `Species` (`code`, `movement`) VALUES
('dragonborn', 9),
('dwarf', 7.5),
('elf', 9),
('gnome', 7.5),
('halfling', 7.5),
('half_elf', 9),
('half_orc', 9),
('human', 9),
('tiefling', 9);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tags`
--

CREATE TABLE `tags` (
  `code` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` smallint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `tags`
--

INSERT INTO `tags` (`code`, `sort_order`) VALUES
('ammunition', 4),
('armor', 2),
('consumable', 29),
('container', 8),
('finesse', 14),
('gear', 9),
('heavy', 16),
('heavy_armor', 26),
('light', 15),
('light_armor', 24),
('loading', 17),
('magic', 28),
('martial', 11),
('medium_armor', 25),
('melee', 12),
('needs_ammunition', 22),
('potion', 6),
('ranged', 13),
('reach', 18),
('scroll', 7),
('shield', 3),
('simple', 10),
('special', 23),
('stealth_disadvantage', 27),
('thrown', 19),
('tool', 5),
('two_handed', 20),
('versatile', 21),
('weapon', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` char(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `email_verified_at` datetime DEFAULT NULL,
  `verification_token_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_expires_at` datetime DEFAULT NULL,
  `PWreset_token_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PWreset_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `session_version` int NOT NULL DEFAULT '0',
  `language` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `Abilities`
--
ALTER TABLE `Abilities`
  ADD PRIMARY KEY (`code`);

--
-- Indizes für die Tabelle `Backgrounds`
--
ALTER TABLE `Backgrounds`
  ADD PRIMARY KEY (`code`);

--
-- Indizes für die Tabelle `calculation_templates`
--
ALTER TABLE `calculation_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_calc_templates_source` (`source_type`,`item_id`);

--
-- Indizes für die Tabelle `Characters`
--
ALTER TABLE `Characters`
  ADD PRIMARY KEY (`name`,`username`),
  ADD UNIQUE KEY `uq_characters_id` (`id`);

--
-- Indizes für die Tabelle `character_calculations`
--
ALTER TABLE `character_calculations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_char_calc_character` (`character_id`),
  ADD KEY `idx_char_calc_item` (`character_item_id`);

--
-- Indizes für die Tabelle `character_items`
--
ALTER TABLE `character_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_character_items_character` (`character_id`),
  ADD KEY `idx_character_items_item` (`item_id`);

--
-- Indizes für die Tabelle `Classes`
--
ALTER TABLE `Classes`
  ADD PRIMARY KEY (`class_code`,`subclass_code`,`Level`);

--
-- Indizes für die Tabelle `Conditions`
--
ALTER TABLE `Conditions`
  ADD PRIMARY KEY (`code`);

--
-- Indizes für die Tabelle `Damagetypes`
--
ALTER TABLE `Damagetypes`
  ADD PRIMARY KEY (`code`);

--
-- Indizes für die Tabelle `Damage_influence`
--
ALTER TABLE `Damage_influence`
  ADD PRIMARY KEY (`code`);

--
-- Indizes für die Tabelle `Game_component_library`
--
ALTER TABLE `Game_component_library`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `Game_component_library_translation`
--
ALTER TABLE `Game_component_library_translation`
  ADD PRIMARY KEY (`component_id`,`lang`);

--
-- Indizes für die Tabelle `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_items_name` (`name`),
  ADD KEY `idx_items_rarity` (`rarity_code`);

--
-- Indizes für die Tabelle `items_translation`
--
ALTER TABLE `items_translation`
  ADD PRIMARY KEY (`item_id`,`lang`),
  ADD UNIQUE KEY `uq_items_translation_name` (`lang`,`name`);

--
-- Indizes für die Tabelle `item_rarities`
--
ALTER TABLE `item_rarities`
  ADD PRIMARY KEY (`code`);

--
-- Indizes für die Tabelle `item_tags`
--
ALTER TABLE `item_tags`
  ADD PRIMARY KEY (`item_id`,`tag_code`),
  ADD KEY `idx_item_tags_tag` (`tag_code`);

--
-- Indizes für die Tabelle `Lookup_translation`
--
ALTER TABLE `Lookup_translation`
  ADD PRIMARY KEY (`type`,`code`,`lang`);

--
-- Indizes für die Tabelle `rate_limit`
--
ALTER TABLE `rate_limit`
  ADD KEY `idx_rate_limit` (`action`,`subject_hash`,`created_at`);

--
-- Indizes für die Tabelle `Skills`
--
ALTER TABLE `Skills`
  ADD PRIMARY KEY (`code`),
  ADD KEY `idx_skills_ability` (`ability_code`);

--
-- Indizes für die Tabelle `Species`
--
ALTER TABLE `Species`
  ADD PRIMARY KEY (`code`);

--
-- Indizes für die Tabelle `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`code`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `verification_token_hash` (`verification_token_hash`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `calculation_templates`
--
ALTER TABLE `calculation_templates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT für Tabelle `Characters`
--
ALTER TABLE `Characters`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `character_calculations`
--
ALTER TABLE `character_calculations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `character_items`
--
ALTER TABLE `character_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `Game_component_library`
--
ALTER TABLE `Game_component_library`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `items`
--
ALTER TABLE `items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;
-- --------------------------------------------------------

--
-- Table `schema_migrations`: the migration files that already ran
--

CREATE TABLE schema_migrations (
  filename   VARCHAR(190) NOT NULL,
  applied_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_migrations (filename) VALUES
 ('migration_base_calculations.sql'),
 ('migration_calculations.sql'),
 ('migration_class_data_fixes.sql'),
 ('migration_cleanup_lookup_names.sql'),
 ('migration_content_translation.sql'),
 ('migration_game_component_library_id.sql'),
 ('migration_item_properties.sql'),
 ('migration_items.sql'),
 ('migration_lookup_codes.sql'),
 ('migration_passive_skills.sql'),
 ('migration_pwreset_expires_null.sql'),
 ('migration_rate_limit.sql'),
 ('migration_session_version.sql'),
 ('migration_test_items.sql'),
 ('migration_users_language.sql'),
 ('migration_weapon_rules.sql'),
 ('migration_weapons.sql');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
