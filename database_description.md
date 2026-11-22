# Database description

This file describes the database of the DnD character manager.
It shows all tables and all fields, the data type of each field, and the data that each field stores.

- **Database:** `dbs15432866` (MySQL on IONOS)
- **Last update:** 2026-11-22
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

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `id` | int unsigned, auto_increment | PK | No | | Unique number of the user. MySQL sets this number automatically. |
| `email` | varchar(255) | UNI | No | | Email address of the user. The application stores it in lowercase. The user signs in with this address. |
| `username` | char(40) | UNI | No | | Name of the user. The `Characters` table uses this name to identify the owner of a character. |
| `password_hash` | varchar(255) | | No | | Hash of the password (PHP `password_hash()`). The table does not store the password itself. |
| `role` | enum('user','admin') | | No | `user` | Role of the user. `admin` gives access to the admin area. |
| `email_verified_at` | datetime | | Yes | NULL | Date and time when the user verified the email address. NULL = not verified. A user who is not verified cannot sign in. |
| `verification_token_hash` | char(64) | IDX | Yes | NULL | SHA-256 hash of the token in the verification link. NULL after verification. |
| `verification_expires_at` | datetime | | Yes | NULL | Date and time when the verification link expires (24 hours after it is sent). |
| `PWreset_token_hash` | char(64) | | Yes | NULL | SHA-256 hash of the token in the password reset link. NULL when no reset is open. |
| `PWreset_expires_at` | datetime | | **No** | | Date and time when the password reset link expires (24 hours after it is sent). **Known problem:** the field does not accept NULL, but `reset.php` sets it to NULL. MySQL then stores `0000-00-00 00:00:00`. |
| `created_at` | timestamp | | Yes | CURRENT_TIMESTAMP | Date and time when the account was made. |
| `session_version` | int | | No | `0` | Counter for the sessions of the user. A password reset increases the value by 1. Then all sessions with the old value end. |

### `Characters`

This table stores the characters of the users. Each row is one character.
The combination of `name` and `username` identifies a character.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
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

## Game data (lookup tables)

The application reads these tables to fill the lists in the character sheet. Users do not change these tables.

### `Classes`

This table stores the values of each class and subclass for each level.
Each row is one combination of class, subclass and level (levels 1 to 20).
The table has 380 rows: 19 class/subclass combinations × 20 levels.
The table does not have a primary key.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `Class` | text | | No | | Name of the class, for example `Barde` or `Fighter`. The names are in German and English. |
| `Subclass` | text | | No | | Name of the subclass, for example `Schule des Wissens`. Empty = the base class without a subclass. |
| `Level` | tinyint | | No | | Character level (1 to 20). |
| `Proficiency_Bonus` | tinyint | | No | | Proficiency bonus at this level. |
| `Hit_Dice` | tinyint | | No | | Size of the hit die of the class, for example 8 for a d8. **(check)** |
| `Spell_Prep_Method` | text | | No | | How the class gets its spells: `Preparation` (prepares spells) or `Knowledge` (knows a fixed list). |
| `Spell_Modifier` | text | | No | | Ability for spellcasting, for example `Charisma`. Empty = the class does not cast spells. |
| `Known_Cantrips` | tinyint | | No | | Number of cantrips that the character knows at this level. |
| `Known_Spells` | tinyint | | No | | Number of spells that the character knows at this level. |
| `Spells_Level_1` … `Spells_Level_9` | tinyint | | No | | Number of spell slots for spell levels 1 to 9 at this level (9 fields). |
| `Arcane_Focus` | text | | No | | Item that the class can use as a spellcasting focus, for example `Musical instrument`. |
| `Ritual_capable` | tinyint(1) | | No | | 1 = the class can cast spells as rituals. 0 = it cannot. |
| `Known_Evocations` | tinyint | | No | | Number of known evocations at this level (for example eldritch invocations of the warlock). **(check)** |
| `Rage_Amount` | tinyint | | No | | Number of rages per long rest (barbarian). |
| `Focus_Points` | tinyint | | No | | Number of focus points at this level (for example monk). **(check)** |
| `Sneekatack_Amount` | tinyint | | No | | Number of sneak attack dice (rogue). |
| `Prepared_Spells` | tinyint | | No | | Number of spells that the character can prepare at this level. |

### `Species`

This table stores the species that a character can have. Each row is one species (9 rows).

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `species` | char(50) | PK | No | | Name of the species. |
| `movement` | float | | No | | Walking speed of the species. **(check: unit feet or meters)** |

### `Backgrounds`

This table stores the backgrounds that a character can have. Each row is one background (13 rows).

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `background` | char(100) | PK | No | | Name of the background. |

### `abilities_skills`

This table stores the six abilities and the skills of each ability. Each row is one ability.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `ability` | char(30) | PK | No | | Name of the ability, for example `Dexterity`. |
| `skills` | char(255) | | Yes | NULL | Skills of this ability, separated by commas, for example `Acrobatics, Slight Of Hand, Stealth`. NULL = the ability has no skills (`Constitution`). |

### `Conditions`

This table stores the conditions that can have an effect on a character, for example "poisoned". Each row is one condition.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `char_condition` | char(30) | PK | No | | Name of the condition. |

### `Damagetypes`

This table stores the types of damage, for example "fire". Each row is one damage type.

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `damagetype` | char(255) | PK | No | | Name of the damage type. |

### `Damage_influence`

This table stores how a damage type can have an effect on a character. Each row is one influence (3 rows: `immune`, `resistant`, `vulnerable`).

| Field | Data type | Key | Null | Default | Description |
|---|---|---|---|---|---|
| `influence` | char(30) | PK | No | | Name of the influence. |

### `Game_component_library`

This table stores game components: weapons, spells, class abilities, talents, items, armor and shields. Each row is one component.
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
