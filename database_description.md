# Database description

This file describes the database of the DnD character manager.
It shows all tables and all fields, the data type of each field, and the data that each field stores.

- **Database:** `dbs15432866` (MySQL on IONOS)
- **Last update:** 2026-11-27
- **Foreign keys:** None. The tables do not have links in the database.
- **SQL mode:** Empty (lenient). MySQL does not stop an incorrect value. It changes the value and gives a warning.

## Rules for this file

1. When you change the database structure in phpMyAdmin, update this file in the same branch.
2. Write the SQL of the change in a file `migration_<short_name>.sql`.
3. Write the descriptions in ASD-STE100.
4. Change the date in "Last update".

## Legend

| Column | Meaning |
|---|---|
| Key | `PK` = primary key (unique identifier of a row). `UNI` = the value must be unique. `IDX` = index (faster search). |
| Null | `Yes` = the field can be empty (NULL). `No` = the field must have a value. |
| Default | The value that MySQL uses when the application does not give a value. |

Descriptions with **(check)** are assumptions. The owner must examine and correct them.

---

## User data

### `users`

This table stores the user accounts. Each row is one user.
The application uses this table for registration, login, email verification and password reset.

**Unverified accounts:** `register.php` and `resend.php` delete each account that is not verified when its link has expired
(function `delete_expired_unverified_users()` in `helper_functions.php`). Then the email and the username are free again.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `id` | int unsigned, auto_increment | PK | No | | Unique number of the user. MySQL sets this number automatically. |
| `email` | varchar(255) | UNI | No | | Email address of the user. The application stores it in lowercase. The user signs in with this address. |
| `username` | char(40) | UNI | No | | Name of the user. The `Characters` table uses this name to identify the owner of a character. |
| `password_hash` | varchar(255) | | No | | Hash of the password (PHP `password_hash()`). The table does not store the password itself. A new account gets the hash of a random value. The user sets the real password with the link in the verification email (`verify.php`). |
| `role` | enum('user','admin') | | No | `user` | Role of the user. `admin` gives access to the admin area. |
| `email_verified_at` | datetime | | Yes | NULL | Date and time when the user verified the email address. NULL = not verified. A user who is not verified cannot sign in. |
| `verification_token_hash` | char(64) | IDX | Yes | NULL | SHA-256 hash of the token in the verification link. NULL after verification. |
| `verification_expires_at` | datetime | | Yes | NULL | Date and time when the verification link expires (24 hours after it is sent). PHP writes this value with the PHP time. The PHP time and the MySQL time (`NOW()`) can be different, so compare it with the PHP time. |
| `PWreset_token_hash` | char(64) | | Yes | NULL | SHA-256 hash of the token in the password reset link. NULL when no reset is open. |
| `PWreset_expires_at` | datetime | | Yes | NULL | Date and time when the password reset link expires (24 hours after it is sent). NULL when no reset is open. |
| `created_at` | timestamp | | Yes | CURRENT_TIMESTAMP | Date and time when the account was made. |
| `session_version` | int | | No | `0` | Counter for the sessions of the user. A password reset and the verification (`verify.php`) increase the value by 1. Then all sessions with the old value end. |
| `language` | char(2) | | No | `en` | Language of the interface for this user, for example `en` or `de`. The application sets it when the user selects a language. `login.php` uses it at sign-in. |

### `Characters`

This table stores the characters of the users. Each row is one character.
The combination of `name` and `username` identifies a character.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `id` | int unsigned | UNI | No | AUTO_INCREMENT | Number of the character. Other tables refer to the character with this number. |
| `name` | char(100) | PK | No | | Name of the character. |
| `username` | char(40) | PK | No | | Username of the owner (`users.username`). |
| `image_url` | varchar(400) | | No | | URL of the image of the character. The character overview shows this image. |
| `Strength` | tinyint | | No | | Strength score of the character. |
| `Dexterity` | tinyint | | No | | Dexterity score of the character. |
| `Constitution` | tinyint | | No | | Constitution score of the character. |
| `Intelligence` | tinyint | | No | | Intelligence score of the character. |
| `Wisdom` | tinyint | | No | | Wisdom score of the character. |
| `Charisma` | tinyint | | No | | Charisma score of the character. |

**Note:** The character sheet (`CharacterSandbox.php`) has more fields than this table. For example: species, class, level, hit points and armor. The table cannot store these values yet.

---

### `rate_limit`

This table stores the attempts for the rate limit (`rate_limit.php`). Each row is one attempt of one subject.
The table does not store IP addresses or email addresses. It stores only a keyed hash (HMAC-SHA-256 with `RATE_LIMIT_KEY` from `.env`).
The application deletes the rows that are older than 24 hours.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `action` | varchar(30) | IDX | No | | Action of the attempt: `login_failed` (failed sign-in), `register`, `resend` (new verification link) or `forgot` (password reset). |
| `subject_hash` | char(64) | IDX | No | | Keyed hash of the IP address or of the email address of the attempt. |
| `created_at` | datetime | IDX | No | | Date and time of the attempt (PHP time). |

Limits: sign-in 10 failed attempts per IP address and 5 per email address in 15 minutes.
Registration, new verification link and password reset: 5 per IP address and 3 per email address in one hour.

## Technical tables

### `schema_migrations`

This table lists the migration files that already ran on the database. `migrate.php` reads it and runs only the new files.
The table exists in the test baseline (`tests/baseline/baseline.sql`) and in the live database. The deploy creates it one time with `php8.4-cli migrate.php --init` (file `schema_migrations_init.sql`). After that, the deploy runs each new migration with `migrate.php`, after an automatic backup.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `filename` | varchar(190) | PK | No | | Name of the migration file, for example `migration_weapons.sql`. |
| `applied_at` | datetime | | No | CURRENT_TIMESTAMP | Date and time when the migration ran. |

Names of new migration files: `migration_YYYYMMDD_<short_name>.sql`. Then the name order is also the time order.

## Game data (lookup tables)

The application reads these tables to fill the lists in the character sheet. Users do not change these tables.

**Codes:** each lookup value has a fixed English code, for example `bard`, `folk_hero` or `fire`.
The code has only lowercase letters, digits and `_`. Do not change a code after it is in use.
The code identifies the value. The application uses the code in field values, ids and requests.
The name is only for display.

**Names:** these tables do not store names. The names are in `Lookup_translation` (see "Translations").
`migration_cleanup_lookup_names.sql` deleted the old name fields on 2026-11-23. The end of that file has a rollback.

### `Classes`

This table stores the values of each class and subclass for each level.
Each row is one combination of class, subclass and level (levels 1 to 20).
The table has 400 rows: 20 class/subclass combinations × 20 levels.
The primary key is the combination of `class_code`, `subclass_code` and `Level`.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `class_code` | varchar(40) | PK | No | `''` | Code of the class, for example `bard`. |
| `subclass_code` | varchar(60) | PK | No | `''` | Code of the subclass, for example `college_of_lore`. Empty = the base class without a subclass. |
| `Level` | tinyint | | No | | Character level (1 to 20). |
| `Proficiency_Bonus` | tinyint | | No | | Proficiency bonus at this level. |
| `Hit_Dice` | tinyint | | No | | Size of the hit die of the class, for example 8 for a d8. |
| `Spell_Prep_Method` | text | | No | | How the class gets its spells (code): `known` (knows a fixed number of spells, all are prepared), `prepared` (prepares spells from the full class list) or `spellbook` (wizard: any number of spells in the spellbook, prepares some of them). Empty = no spells. |
| `Spell_Modifier` | text | | No | | Code of the ability for spellcasting (`Abilities.code`), for example `charisma`. Empty = the class does not cast spells. |
| `Spell_List` | varchar(40) | | No | `''` | Code of the spell list of the class (`spell_classes.list_code`), for example `wizard`. Normally the class code. The Eldritch Knight uses `wizard`. Empty = the class does not cast spells. `migration_20261127_spells.sql`. |
| `Known_Cantrips` | tinyint | | No | | Number of cantrips that the character knows at this level. |
| `Known_Spells` | tinyint | | No | | Number of spells that the character knows at this level. Only for the method `known`. |
| `Spells_Level_1` … `Spells_Level_9` | tinyint | | No | | Number of spell slots for spell levels 1 to 9 at this level (9 fields). |
| `Arcane_Focus` | text | | No | | Item that the class can use as a spellcasting focus, for example `Musical instrument`. |
| `Ritual_capable` | tinyint(1) | | No | | 1 = the class can cast spells as rituals. 0 = it cannot. |
| `Known_Evocations` | tinyint | | No | | Number of known evocations at this level (for example eldritch invocations of the warlock). **(check)** |
| `Rage_Amount` | tinyint | | No | | Number of rages per long rest (barbarian). |
| `Focus_Points` | tinyint | | No | | Number of focus points at this level (for example monk). **(check)** |
| `Sneekatack_Amount` | tinyint | | No | | Number of sneak attack dice (rogue). |
| `Prepared_Spells` | tinyint | | No | | The part of the number of prepared spells that comes from the level: the level (cleric, druid, wizard), half the level rounded down (paladin) or rounded up (artificer). 0 for the method `known`. The engine adds the modifier of the spell ability (rules 730–732). |

### `Species`

This table stores the species that a character can have. Each row is one species (9 rows).

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `code` | varchar(40) | PK | No | `''` | Code of the species, for example `half_elf`. |
| `movement` | float | | No | | Walking speed of the species. **(check: unit feet or meters)** |

### `Backgrounds`

This table stores the backgrounds that a character can have. Each row is one background (13 rows).

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `code` | varchar(40) | PK | No | `''` | Code of the background, for example `folk_hero`. |

### `Abilities`

This table stores the six abilities. Each row is one ability (6 rows).
The names come from the language files (`lang/*.php`, key `ability.<code>`).

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `code` | varchar(20) | PK | No | | Code of the ability, for example `dexterity`. |
| `sort_order` | tinyint | | No | | Position of the ability in the character sheet (1 = first). |

### `Skills`

This table stores the skills and the ability of each skill. Each row is one skill (18 rows).
The names come from the language files (`lang/*.php`, key `skill.<code>`).

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `code` | varchar(40) | PK | No | | Code of the skill, for example `sleight_of_hand`. |
| `ability_code` | varchar(20) | IDX | No | | Code of the ability of the skill (`Abilities.code`). |
| `sort_order` | tinyint | | No | | Position of the skill in the character sheet. The skills of an ability are in alphabetical order. |

### `Conditions`

This table stores the conditions that can have an effect on a character, for example "poisoned". Each row is one condition.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `code` | varchar(40) | PK | No | `''` | Code of the condition, for example `unconscious`. |

### `Damagetypes`

This table stores the types of damage, for example "fire". Each row is one damage type.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `code` | varchar(40) | PK | No | `''` | Code of the damage type, for example `fire`. |

### `Damage_influence`

This table stores how a damage type can have an effect on a character. Each row is one influence (3 rows: `immune`, `resistant`, `vulnerable`).

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `code` | varchar(40) | PK | No | `''` | Code of the influence, for example `resistant`. |

### `Game_component_library`

This table stores game components: weapons, spells, class abilities, talents, items, armor and shields. Each row is one component.
Now the application uses only the talents of this table. The spells are in the table `spells` (see "Spells").
The field `id` identifies each row.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `id` | int unsigned, auto_increment | PK | No | | Unique number of the component. MySQL sets this number automatically. |
| `type` | set('weapon','spell','class_ability','talent','item','armor','shield') | | No | | Type of the component. |
| `name` | char(255) | | No | | Name of the component. |
| `description` | text | | No | | Full rules text of the component. |
| `action_type` | char(255) | | No | | Action that the component needs, for example `Bonus` (bonus action). **(check)** |
| `range` | char(255) | | No | | Range of the component. **(check)** |
| `attribute` | char(255) | | No | | Ability that the component uses. **(check)** |
| `attack_type` | char(255) | | No | | Type of attack, for example melee or ranged. **(check)** |
| `damage_type` | char(255) | | No | | Damage type of the component (see `Damagetypes`). **(check)** |
| `magic_effect` | char(255) | | No | | Magic effect of the component. **(check)** |
| `projectile` | char(255) | | No | | Projectile that the component needs, for example arrows. **(check)** |
| `uses` | int | | No | | Number of uses (for example per rest). 0 = no limit. **(check)** |
| `uses_spent` | int | | No | | Number of uses already spent. **(check)** |
| `duration` | char(255) | | No | | Duration of the effect. **(check)** |
| `precondition` | char(255) | | No | | Condition that the character must meet to use the component. **(check)** |
| `components` | char(255) | | No | | Spell components (verbal, somatic, material). **(check)** |
| `material` | char(255) | | No | | Material that a spell needs. **(check)** |

## Items

An item has a name, a weight, a rarity, a cost and tags. The name is unique. The rarities and the tags come from fixed lists.
Now there is no page to add items. Items and tags are added with SQL (a migration).

### `items`

This table stores the items. Each row is one item.

**Test data:** `migration_test_items.sql` adds five test items with tags, German names and calculation rules: `Shield`, `Chain Mail`, `Scale Mail`, `Ring of Wisdom` and `Necklace of Persuasion`.
`migration_weapons.sql` adds five test weapons with weapon data, tags and German names, but without calculation rules: `Dagger`, `Longsword`, `Greataxe`, `Shortbow` and `Light Crossbow`.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `id` | int unsigned | PK | No | AUTO_INCREMENT | Number of the item. Other tables refer to the item with this number, so the name can change. |
| `name` | varchar(150) | UNI | No | | English name of the item (base language). Two items cannot have the same name. Upper and lower case count as the same name. |
| `weight_lb` | decimal(8,2) | | Yes | NULL | Weight of the item in pounds (lb), for example `55.00` or `0.25`. NULL: the item has no weight. |
| `rarity_code` | varchar(20) | IDX | No | `mundane` | Code of the rarity (`item_rarities.code`). `mundane` is an item that is not magical. |
| `cost_gp` | decimal(10,2) | | Yes | NULL | Cost of the item in gold pieces (gp), with 2 decimals. 1 sp = `0.10`, 1 cp = `0.01`. NULL: the item has no fixed cost. |
| `damage_dice` | varchar(20) | | Yes | NULL | Damage dice of a weapon, for example `1d8`. NULL: the item is not a weapon. The page shows the dice in the notation of the language (German: `1W8`). |
| `damage_dice_versatile` | varchar(20) | | Yes | NULL | Damage dice of a versatile weapon when you use it with two hands, for example `1d10`. NULL: the weapon is not versatile. |
| `damage_type_code` | varchar(40) | | Yes | NULL | Code of the damage type of a weapon (`Damagetypes.code`), for example `slashing`. NULL: the item is not a weapon. |
| `range_normal_ft` | smallint unsigned | | Yes | NULL | Normal range of a ranged or thrown weapon in feet, for example `80`. NULL: the weapon has no range (melee only). |
| `range_long_ft` | smallint unsigned | | Yes | NULL | Long range of a ranged or thrown weapon in feet, for example `320`. NULL: the weapon has no range. |
| `created_at` | datetime | | No | CURRENT_TIMESTAMP | Date and time when the item was added. |
| `updated_at` | datetime | | No | CURRENT_TIMESTAMP | Date and time of the last change (MySQL sets it). |

### `items_translation`

This table stores the names of the items in other languages. Each row is the name of one item in one language.
If a language has no row, the application shows the English name from `items`.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `item_id` | int unsigned | PK | No | | Number of the item (`items.id`). |
| `lang` | varchar(5) | PK, UNI | No | | Language code, for example `de`. |
| `name` | varchar(150) | UNI | No | | Name of the item in this language. Two items cannot have the same name in the same language. |

### `item_rarities`

This table stores the rarities that an item can have. Each row is one rarity.
Only a migration adds or changes rarities. The names are in `Lookup_translation` (type `item_rarity`).
Code uses a rarity always by its `code`.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `code` | varchar(20) | PK | No | | Code of the rarity: `mundane`, `common`, `uncommon`, `rare`, `very_rare`, `legendary` or `artifact`. |
| `sort_order` | smallint | | No | 0 | Position of the rarity in lists, from `mundane` (0) to `artifact` (6). |

### `tags`

This table stores the tags that an item can have. Each row is one tag.
Only a migration adds or changes tags. The names are in `Lookup_translation` (type `item_tag`).
A tag is one piece of information without a value. Code can use a tag for logic, always by its `code`.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `code` | varchar(40) | PK | No | | Code of the tag, for example `finesse` or `heavy_armor`. |
| `sort_order` | smallint | | No | 0 | Position of the tag in lists. |

### `item_tags`

This table stores the tags of each item. Each row is one tag of one item. An item can have any number of tags.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `item_id` | int unsigned | PK | No | | Number of the item (`items.id`). |
| `tag_code` | varchar(40) | PK, IDX | No | | Code of the tag (`tags.code`). |

### `character_items`

This table stores the inventory of the characters. Each row is one item of one character.
Two items of the same type (for example two daggers) are two rows. A stack (for example 20 arrows) is one row with a `quantity`.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `id` | int unsigned | PK | No | AUTO_INCREMENT | Number of the inventory row. `character_calculations.character_item_id` refers to this number. |
| `character_id` | int unsigned | IDX | No | | Number of the character (`Characters.id`). |
| `item_id` | int unsigned | IDX | No | | Number of the item (`items.id`). |
| `quantity` | smallint unsigned | | No | `1` | Quantity of the item in this row. |
| `equipped` | tinyint(1) | | No | `0` | `1` = the character uses the item now (for example wears the armor). `0` = the item is only in the inventory. |
| `created_at` | datetime | | No | CURRENT_TIMESTAMP | Date and time when the character got the item. |

## Spells

The spells come from the System Reference Document 5.1 (SRD 5.1) of Wizards of the Coast, in English and in the official German version.
The SRD 5.1 has the license CC-BY-4.0. The character sheet shows the attribution under the spells card.
The spells of a character stay in the browser for now (no save function yet).

### `spell_schools`

This table stores the schools of magic. Each row is one school (8 rows). The names are in `Lookup_translation` (type `spell_school`).

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `code` | varchar(20) | PK | No | | Code of the school, for example `evocation`. |
| `sort_order` | smallint | | No | 0 | Position of the school in lists. |

### `spells`

This table stores the spells. Each row is one spell. The texts are in `spells_translation`.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `code` | varchar(60) | PK | No | | Code of the spell, for example `cure_wounds`. |
| `level` | tinyint unsigned | IDX | No | | Level of the spell, 0 to 9. 0 = cantrip. |
| `school_code` | varchar(20) | | No | | Code of the school (`spell_schools.code`). |
| `comp_v` | tinyint(1) | | No | 0 | 1 = the spell has a verbal component (V). |
| `comp_s` | tinyint(1) | | No | 0 | 1 = the spell has a somatic component (S; German: G). |
| `comp_m` | tinyint(1) | | No | 0 | 1 = the spell has a material component (M). The material is in `spells_translation.material`. |
| `ritual` | tinyint(1) | | No | 0 | 1 = the spell can be cast as a ritual. |
| `concentration` | tinyint(1) | | No | 0 | 1 = the spell needs concentration. |

### `spells_translation`

This table stores the texts of the spells. Each row is the texts of one spell in one language.
Each spell has an English row. A field with NULL, or a missing language, uses the English row.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `spell_code` | varchar(60) | PK | No | | Code of the spell (`spells.code`). |
| `lang` | varchar(5) | PK | No | | Language code, for example `en` or `de`. |
| `name` | varchar(150) | | No | | Name of the spell in this language. |
| `casting_time` | varchar(150) | | Yes | NULL | Casting time, for example `1 action`. |
| `spell_range` | varchar(150) | | Yes | NULL | Range, for example `60 feet` or `Touch`. |
| `duration` | varchar(150) | | Yes | NULL | Duration, for example `Concentration, up to 1 minute`. |
| `material` | varchar(400) | | Yes | NULL | Material component, for example `a pinch of sulfur`. NULL: no material. |
| `description` | text | | No | | Rules text of the spell. Paragraphs are separated by a line break. |
| `higher_levels` | text | | Yes | NULL | Text "At Higher Levels". NULL: the spell has no such text. |

### `spell_classes`

This table stores the spell lists. Each row is one spell on one spell list. A spell can be on many lists.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `spell_code` | varchar(60) | PK | No | | Code of the spell (`spells.code`). |
| `list_code` | varchar(40) | PK, IDX | No | | Code of the spell list. The class uses the list in `Classes.Spell_List`, for example `cleric`. |

**Rules of the spells card:**

- The card uses the row of `Classes` for the class, the subclass and the level. If the subclass has no rows, it uses the base class.
- The character can add only spells of the spell list (`Classes.Spell_List`) up to the highest level with spell slots. Cantrips only if `Known_Cantrips` > 0.
- Cantrips: up to `Known_Cantrips`. Method `known`: spells up to `Known_Spells`, all are prepared.
- Methods `prepared` and `spellbook`: any number of spells. The character can prepare not more than `spell.prepared_max`.
- Spell slots: the total comes from `Spells_Level_1` … `Spells_Level_9`. The used slots are 0 to the total.

## Calculations

These tables store the rules that calculate the values of a character (for example armor class or a skill).
The JavaScript engine of the character sheet does the calculation. The database stores only the rules and the inputs (ability scores, level, inventory), not the results.

**Rule:** each row changes one value (`target`) with one operator. The engine uses the rows in the sequence of `sort_order`.

**Value names (`target`, `value_ref`):** the pattern is `<group>.<code>`. Use lowercase letters, digits and `_`, and exactly one dot. Where a database code exists, the part after the dot is this code.

| Group | Example | Code from |
|---|---|---|
| `char.` | `char.level`, `char.prof_bonus`, `char.speed` | Fixed list |
| `ability.` | `ability.dexterity` (score) | `Abilities.code` |
| `mod.` | `mod.dexterity` (modifier) | `Abilities.code` |
| `save.` | `save.dexterity` | `Abilities.code` |
| `skill.` | `skill.sleight_of_hand` | `Skills.code` |
| `combat.` | `combat.ac`, `combat.initiative` | Fixed list |
| `hp.` | `hp.max` | Fixed list |
| `passive.` | `passive.perception` | `Skills.code` |
| `weapon.` | `weapon.attack`, `weapon.damage_bonus`, `weapon.ability` | Fixed list. The engine calculates these values one time for each equipped weapon (weapons card). |
| `spell.` | `spell.mod`, `spell.save_dc`, `spell.attack`, `spell.prepared_max`, `spell.prepared_base` | Fixed list (spells card). |

**Operators (`operator`):**

| Operator | Result |
|---|---|
| `set` | The value becomes the given value. |
| `add` | The given value is added. |
| `sub` | The given value is subtracted. |
| `mul` | The value is multiplied by the given value. |
| `div` | The value is divided by the given value. |
| `min` | The value is at least the given value. |
| `max` | The value is at most the given value. |
| `floor` | The value is rounded down. This operator has no given value. |

**Given value:** a row has a number in `value_num` or the name of another value in `value_ref`. The operator `floor` has neither.

**Condition (`condition_json`):** NULL = the rule always applies. Otherwise a JSON object with these checks:

| Check | Example | Result |
|---|---|---|
| `EQUIPPED` | `{"EQUIPPED": true}` | True if the source item is equipped. |
| `HAS_TAG` | `{"HAS_TAG": "heavy_armor"}` | True if the source item has this tag (`tags.code`). |
| `COMPARE` | `{"COMPARE": ["char.level", ">", 5]}` | True if the comparison is true. |
| `HAS_DEFENSE` | `{"HAS_DEFENSE": ["resistant", "fire"]}` | True if the character has this defense. The first entry is a code of `Damage_influence` or `condition`. The second entry is a code of `Damagetypes` or `Conditions`. |
| `PROFICIENT` | `{"PROFICIENT": "skill.stealth"}` | True if the character is proficient in this saving throw or skill (the box on the sheet is ticked). |
| `WEAPON_TAG` | `{"WEAPON_TAG": "finesse"}` | True only in the calculation of one weapon, if this weapon has this tag (`tags.code`). |
| `SPELL_ABILITY` | `{"SPELL_ABILITY": "wisdom"}` | True if the selected class casts spells with this ability (`Classes.Spell_Modifier`, a code of `Abilities`). |
| `WEAPON_PROFICIENT` | `{"WEAPON_PROFICIENT": true}` | True only in the calculation of one weapon, if the character is proficient with it: "Simple weapons" or "Martial weapons" is on and the weapon has the tag `simple` or `martial`, or the weapon has a chip in the card "Weapon proficiency". |
| `all`, `any`, `not` | `{"all": [{...}, {...}]}` | Combine checks: all are true, one is true, the check is false. |

An object with more than one check is true only if all checks are true. An unknown check is false. Then the rule does not apply.
`COMPARE` uses these comparisons: `>`, `>=`, `<`, `<=`, `=`, `!=`. The right side is a number or the name of a value.

**Calculation:** `Assets/calc_engine.js` calculates the values. A value that does not exist counts as 0. `div` by 0 does not change the value.

**Inputs (no rules):** `ability.<code>` (ability score from the sheet, empty = 10), `char.level` (level from the sheet, empty = 1) and `spell.prepared_base` (`Classes.Prepared_Spells` of the selected class and level, else 0).

**Base rules (`source_type` `base`, `migration_base_calculations.sql`):**

| `sort_order` | Value | Rule |
|---|---|---|
| 200–204 | `char.prof_bonus` | 2 + floor((level − 1) / 4) |
| 210–213 | `mod.<ability>` | floor((score − 10) / 2) |
| 300–301 | `save.<ability>` | Modifier of the ability. Plus `char.prof_bonus` if `PROFICIENT`. |
| 300–301 | `skill.<skill>` | Modifier of the ability of the skill (`Skills.ability_code`). Plus `char.prof_bonus` if `PROFICIENT`. |
| 300 | `combat.initiative` | `mod.dexterity` |
| 400 | `combat.ac_dex` | `mod.dexterity` (the part of Dexterity in the armor class; armor can change it) |
| 410–412 | `combat.ac` | 10 + `combat.ac_dex` |
| 500–501 | `passive.perception`, `passive.insight`, `passive.investigation` | 10 + the skill (`skill.perception`, `skill.insight`, `skill.investigation`). `migration_passive_skills.sql`. |
| 600–602 | `weapon.ability` | `mod.strength`. A weapon with the tag `ranged` uses `mod.dexterity`. A weapon with the tag `finesse` uses at least `mod.dexterity` (the higher modifier). `migration_weapon_rules.sql`. |
| 610–611 | `weapon.attack` | `weapon.ability`. Plus `char.prof_bonus` if `WEAPON_PROFICIENT`. |
| 620 | `weapon.damage_bonus` | `weapon.ability` (added to the damage dice). |
| 700–703 | `spell.mod` | 0. The modifier of the spell ability if `SPELL_ABILITY` is `intelligence`, `wisdom` or `charisma`. `migration_20261127_spells.sql`. |
| 710–712 | `spell.save_dc` | 8 + `char.prof_bonus` + `spell.mod` |
| 720–721 | `spell.attack` | `char.prof_bonus` + `spell.mod` |
| 730–732 | `spell.prepared_max` | `spell.prepared_base`. If it is more than 0: plus `spell.mod`, and at least 1. |

### `calculation_templates`

This table stores the rules as templates. Each row is one rule.
Rules with `source_type` `base` apply to each character. The engine reads them directly from this table.
Rules with `source_type` `item` belong to an item. When a character gets the item, the application copies these rules to `character_calculations`.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `id` | int unsigned | PK | No | AUTO_INCREMENT | Number of the rule. |
| `source_type` | varchar(20) | IDX | No | | Source of the rule: `base` (each character) or `item`. Later also `species`, `class`. |
| `item_id` | int unsigned | IDX | Yes | NULL | Number of the item (`items.id`) if `source_type` is `item`. Else NULL. |
| `sort_order` | smallint unsigned | | No | | Sequence of the rule. Use steps of 100 per stage (100, 200, ...), so that new rules can go between. |
| `target` | varchar(60) | | No | | Name of the value that the rule changes, for example `combat.ac`. |
| `operator` | enum | | No | | Operator: `set`, `add`, `sub`, `mul`, `div`, `min`, `max` or `floor`. |
| `value_num` | decimal(10,2) | | Yes | NULL | Given value as a number, for example `2` or `0.5`. |
| `value_ref` | varchar(60) | | Yes | NULL | Given value as the name of another value, for example `mod.dexterity`. |
| `condition_json` | json | | Yes | NULL | Condition of the rule. NULL = the rule always applies. |

### `character_calculations`

This table stores the rules of each character. Each row is one rule of one character.
The application adds the rules of an item when the character gets the item. It deletes them when the item goes out of the inventory.
If a template changes later, the copies do not change. A migration must update them.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `id` | int unsigned | PK | No | AUTO_INCREMENT | Number of the rule. |
| `character_id` | int unsigned | IDX | No | | Number of the character (`Characters.id`). |
| `source_type` | varchar(20) | | No | | Source of the rule: `item`. Later also `species`, `class`, `manual` (a special rule for this character only). |
| `character_item_id` | int unsigned | IDX | Yes | NULL | Number of the inventory row (`character_items.id`) if `source_type` is `item`. Else NULL. `EQUIPPED` and `HAS_TAG` check this inventory row. |
| `sort_order` | smallint unsigned | | No | | Sequence of the rule (see `calculation_templates`). |
| `target` | varchar(60) | | No | | Name of the value that the rule changes. |
| `operator` | enum | | No | | Operator (see `calculation_templates`). |
| `value_num` | decimal(10,2) | | Yes | NULL | Given value as a number. |
| `value_ref` | varchar(60) | | Yes | NULL | Given value as the name of another value. |
| `condition_json` | json | | Yes | NULL | Condition of the rule. NULL = the rule always applies. |

## Translations

The application reads the names and texts of the game content from these tables, in the language of the user.
The game tables do not store names (except `Game_component_library`, see below).
If a text is missing in this language, the application uses the English text (`lang = 'en'`).
Each new lookup value and each new game component needs an English row. A German row is recommended.

### `Lookup_translation`

This table stores the names of the lookup values. Each row is the name of one value in one language.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `type` | varchar(30) | PK | No | | Type of the value: `class`, `subclass`, `species`, `background`, `condition`, `damagetype`, `damage_influence`, `item_tag`, `item_rarity` or `spell_school`. |
| `code` | varchar(60) | PK | No | | Code of the value, for example `bard` (see the field `code` or `class_code` / `subclass_code` of the game table). |
| `lang` | varchar(5) | PK | No | | Language code, for example `en` or `de`. |
| `name` | varchar(150) | | No | | Name of the value in this language, for example `Barde`. |

### `Game_component_library_translation`

This table stores the texts of the game components (now only talents). Each row is the texts of one component in one language.
A field with NULL uses the English row. If the English row has NULL too, the application uses the field of `Game_component_library`. That table keeps its texts as the last fallback.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `component_id` | int unsigned | PK | No | | Number of the component (`Game_component_library.id`). |
| `lang` | varchar(5) | PK | No | | Language code, for example `en` or `de`. |
| `name` | varchar(255) | | No | | Name of the component in this language. |
| `description` | text | | No | | Rules text of the component in this language. |
| `action_type` | varchar(255) | | Yes | NULL | Action that the component needs, for example `Bonus action`. |
| `range` | varchar(255) | | Yes | NULL | Range of the component. |
| `attack_type` | varchar(255) | | Yes | NULL | Type of attack. |
| `magic_effect` | varchar(255) | | Yes | NULL | Magic effect of the component. |
| `projectile` | varchar(255) | | Yes | NULL | Projectile that the component needs. |
| `duration` | varchar(255) | | Yes | NULL | Duration of the effect. |
| `precondition` | varchar(255) | | Yes | NULL | Condition that the character must meet to use the component. |
| `components` | varchar(255) | | Yes | NULL | Spell components. |
| `material` | varchar(255) | | Yes | NULL | Material that a spell needs. |
