<?php
declare(strict_types=1);

/**
 * content_translation.php – names and texts of the game content in the current language
 *
 * - Lookup values (classes, subclasses, species, backgrounds, conditions, damage types,
 *   damage influences): table Lookup_translation (type, code, lang, name).
 * - Game components (talents, ...): table Game_component_library_translation.
 * - A missing text falls back to English, then to the code (lookup values)
 *   or to the text in Game_component_library (components).
 *
 * Needs i18n.php (current_lang(), I18N_DEFAULT). bootstrap.php loads it.
 */

/**
 * Names of all lookup values: [type => [code => name]].
 * English fills the names that are missing in $lang.
 */
function lookup_names(PDO $pdo, ?string $lang = null): array {
    static $cache = [];
    $lang = $lang ?? current_lang();
    if (!array_key_exists($lang, $cache)) {
        // English rows first, then the rows of $lang replace them
        $stmt = $pdo->prepare('SELECT type, code, name FROM Lookup_translation
            WHERE lang IN (:lang, :base) ORDER BY lang = :lang_again');
        $stmt->execute([':lang' => $lang, ':base' => I18N_DEFAULT, ':lang_again' => $lang]);
        $names = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $names[$row['type']][$row['code']] = $row['name'];
        }
        $cache[$lang] = $names;
    }
    return $cache[$lang];
}

/** Sort key for a name: case and German umlauts do not change the order */
function content_sort_key(string $name): string {
    $folded = strtr($name, ['Ä' => 'a', 'Ö' => 'o', 'Ü' => 'u', 'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ß' => 'ss']);
    return strtolower($folded);
}

/**
 * A list for a select: [['code' => ..., 'name' => ...], ...], sorted by the name.
 * $codes are the codes from the game table, $type is the type in Lookup_translation.
 */
function lookup_list(PDO $pdo, array $codes, string $type): array {
    $names = lookup_names($pdo)[$type] ?? [];
    $list = [];
    foreach ($codes as $code) {
        $code = (string)$code;
        $list[] = ['code' => $code, 'name' => $names[$code] ?? $code];
    }
    usort($list, fn(array $a, array $b): int => strcmp(content_sort_key($a['name']), content_sort_key($b['name'])));
    return $list;
}

/** Names of the talents: [['id' => ..., 'name' => ...], ...], sorted by the name */
function talent_list(PDO $pdo, ?string $lang = null): array {
    $stmt = $pdo->prepare("SELECT g.id, COALESCE(t.name, e.name, g.name) AS name
        FROM Game_component_library g
        LEFT JOIN Game_component_library_translation t ON t.component_id = g.id AND t.lang = :lang
        LEFT JOIN Game_component_library_translation e ON e.component_id = g.id AND e.lang = :base
        WHERE FIND_IN_SET('talent', g.type) > 0");
    $stmt->execute([':lang' => $lang ?? current_lang(), ':base' => I18N_DEFAULT]);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    usort($list, fn(array $a, array $b): int => strcmp(content_sort_key((string)$a['name']), content_sort_key((string)$b['name'])));
    return $list;
}

/** One talent with its texts in the current language (field by field: language, English, base), or null */
function talent_component(PDO $pdo, int $id, ?string $lang = null): ?array {
    $text = fn(string $field): string =>
        "COALESCE(t.`$field`, e.`$field`, g.`$field`) AS `$field`";
    $stmt = $pdo->prepare('SELECT g.id, ' . implode(', ', [
            $text('name'), $text('description'), $text('action_type'), $text('range'),
            'g.attribute', $text('attack_type'), 'g.damage_type', $text('magic_effect'),
            $text('projectile'), 'g.uses', 'g.uses_spent', $text('duration'),
            $text('precondition'), $text('components'), $text('material'),
        ]) . "
        FROM Game_component_library g
        LEFT JOIN Game_component_library_translation t ON t.component_id = g.id AND t.lang = :lang
        LEFT JOIN Game_component_library_translation e ON e.component_id = g.id AND e.lang = :base
        WHERE g.id = :id AND FIND_IN_SET('talent', g.type) > 0");
    $stmt->execute([':id' => $id, ':lang' => $lang ?? current_lang(), ':base' => I18N_DEFAULT]);
    $component = $stmt->fetch(PDO::FETCH_ASSOC);
    return $component ?: null;
}

/**
 * One row of calculation_templates as a rule for Assets/calc_engine.js, or null.
 * A rule with a broken condition is null: it does not apply.
 */
function calc_rule_from_row(array $row): ?array {
    $condition = null;
    if ($row['condition_json'] !== null) {
        $condition = json_decode((string)$row['condition_json'], true);
        if (!is_array($condition)) {
            return null;
        }
    }
    return [
        'id'         => (int)$row['id'],
        'sort_order' => (int)$row['sort_order'],
        'target'     => (string)$row['target'],
        'operator'   => (string)$row['operator'],
        'value_num'  => $row['value_num'] === null ? null : (float)$row['value_num'],
        'value_ref'  => $row['value_ref'] === null ? null : (string)$row['value_ref'],
        'condition'  => $condition,
    ];
}

/** Names of the items in $lang: [id => name]. A missing name falls back to the English name (items.name). */
function item_names(PDO $pdo, ?string $lang = null): array {
    $stmt = $pdo->prepare('SELECT i.id, COALESCE(t.name, i.name) AS name
        FROM items i LEFT JOIN items_translation t ON t.item_id = i.id AND t.lang = :lang');
    $stmt->execute([':lang' => $lang ?? current_lang()]);
    $names = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $names[(string)$row['id']] = (string)$row['name'];
    }
    return $names;
}

/**
 * All items for the inventory, sorted by the name:
 * [['id', 'name', 'weight', 'rarity', 'tags' => [tag codes], 'rules' => [rules for calc_engine.js]], ...]
 */
function item_list(PDO $pdo, ?string $lang = null): array {
    $names = item_names($pdo, $lang);

    $tags = [];
    $stmt = $pdo->prepare('SELECT g.item_id, g.tag_code FROM item_tags g
        LEFT JOIN tags s ON s.code = g.tag_code ORDER BY g.item_id, s.sort_order, g.tag_code');
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $tags[(int)$row['item_id']][] = (string)$row['tag_code'];
    }

    $rules = [];
    $stmt = $pdo->prepare("SELECT id, item_id, sort_order, target, operator, value_num, value_ref, condition_json
        FROM calculation_templates WHERE source_type = 'item' AND item_id IS NOT NULL ORDER BY sort_order, id");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $rule = calc_rule_from_row($row);
        if ($rule !== null) {
            $rules[(int)$row['item_id']][] = $rule;
        }
    }

    $list = [];
    $stmt = $pdo->prepare('SELECT id, weight_lb, rarity_code, damage_dice, damage_dice_versatile, damage_type_code,
        range_normal_ft, range_long_ft FROM items');
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $id = (int)$row['id'];
        $list[] = [
            'id'     => $id,
            'name'   => $names[(string)$id] ?? '',
            'weight' => $row['weight_lb'] === null ? null : (float)$row['weight_lb'],
            'rarity' => (string)$row['rarity_code'],
            // Weapon data (NULL for items that are not weapons); dice as stored, for example '1d8'
            'damage'           => $row['damage_dice'] === null ? null : (string)$row['damage_dice'],
            'damage_versatile' => $row['damage_dice_versatile'] === null ? null : (string)$row['damage_dice_versatile'],
            'damage_type'      => $row['damage_type_code'] === null ? null : (string)$row['damage_type_code'],
            'range_normal'     => $row['range_normal_ft'] === null ? null : (int)$row['range_normal_ft'],
            'range_long'       => $row['range_long_ft'] === null ? null : (int)$row['range_long_ft'],
            'tags'   => $tags[$id] ?? [],
            'rules'  => $rules[$id] ?? [],
        ];
    }
    usort($list, fn(array $a, array $b): int => strcmp(content_sort_key($a['name']), content_sort_key($b['name'])));
    return $list;
}

/**
 * Spell data of the classes (table Classes) for the spells card:
 * ["<class>|<subclass>" => [level => ['method', 'ability', 'list', 'cantrips', 'known', 'prepared_base', 'slots' => [9 numbers]]]].
 * Only the rows of classes that use a spell list (Spell_List not empty).
 */
function spellcasting_table(PDO $pdo): array {
    $stmt = $pdo->prepare("SELECT class_code, subclass_code, Level, Spell_Prep_Method, Spell_Modifier, Spell_List,
        Known_Cantrips, Known_Spells, Prepared_Spells, Spells_Level_1, Spells_Level_2, Spells_Level_3, Spells_Level_4,
        Spells_Level_5, Spells_Level_6, Spells_Level_7, Spells_Level_8, Spells_Level_9
        FROM Classes WHERE Spell_List <> '' ORDER BY class_code, subclass_code, Level");
    $stmt->execute();
    $table = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $slots = [];
        for ($i = 1; $i <= 9; $i++) {
            $slots[] = (int)$row['Spells_Level_' . $i];
        }
        $table[$row['class_code'] . '|' . $row['subclass_code']][(int)$row['Level']] = [
            'method'        => (string)$row['Spell_Prep_Method'],
            'ability'       => (string)$row['Spell_Modifier'],
            'list'          => (string)$row['Spell_List'],
            'cantrips'      => (int)$row['Known_Cantrips'],
            'known'         => (int)$row['Known_Spells'],
            'prepared_base' => (int)$row['Prepared_Spells'],
            'slots'         => $slots,
        ];
    }
    return $table;
}

/**
 * All spells in $lang, without the long texts, sorted by level and name:
 * [['code', 'name', 'level', 'school', 'v', 's', 'm', 'ritual', 'concentration',
 *   'casting_time', 'range', 'duration', 'lists' => [list codes]], ...]
 * A missing text falls back to English.
 */
function spell_list(PDO $pdo, ?string $lang = null): array {
    $lists = [];
    $stmt = $pdo->prepare('SELECT spell_code, list_code FROM spell_classes ORDER BY list_code');
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $lists[(string)$row['spell_code']][] = (string)$row['list_code'];
    }

    $stmt = $pdo->prepare('SELECT s.code, s.level, s.school_code, s.comp_v, s.comp_s, s.comp_m, s.ritual, s.concentration,
            COALESCE(t.name, e.name, s.code) AS name,
            COALESCE(t.casting_time, e.casting_time) AS casting_time,
            COALESCE(t.spell_range, e.spell_range) AS spell_range,
            COALESCE(t.duration, e.duration) AS duration
        FROM spells s
        LEFT JOIN spells_translation t ON t.spell_code = s.code AND t.lang = :lang
        LEFT JOIN spells_translation e ON e.spell_code = s.code AND e.lang = :base');
    $stmt->execute([':lang' => $lang ?? current_lang(), ':base' => I18N_DEFAULT]);
    $list = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $code = (string)$row['code'];
        $list[] = [
            'code'          => $code,
            'name'          => (string)$row['name'],
            'level'         => (int)$row['level'],
            'school'        => (string)$row['school_code'],
            'v'             => (bool)$row['comp_v'],
            's'             => (bool)$row['comp_s'],
            'm'             => (bool)$row['comp_m'],
            'ritual'        => (bool)$row['ritual'],
            'concentration' => (bool)$row['concentration'],
            'casting_time'  => (string)($row['casting_time'] ?? ''),
            'range'         => (string)($row['spell_range'] ?? ''),
            'duration'      => (string)($row['duration'] ?? ''),
            'lists'         => $lists[$code] ?? [],
        ];
    }
    usort($list, fn(array $a, array $b): int =>
        ($a['level'] <=> $b['level']) ?: strcmp(content_sort_key($a['name']), content_sort_key($b['name'])));
    return $list;
}

/** The long texts of one spell in $lang (field by field: language, then English), or null */
function spell_details(PDO $pdo, string $code, ?string $lang = null): ?array {
    $stmt = $pdo->prepare('SELECT s.code,
            COALESCE(t.material, e.material) AS material,
            COALESCE(t.description, e.description) AS description,
            COALESCE(t.higher_levels, e.higher_levels) AS higher_levels
        FROM spells s
        LEFT JOIN spells_translation t ON t.spell_code = s.code AND t.lang = :lang
        LEFT JOIN spells_translation e ON e.spell_code = s.code AND e.lang = :base
        WHERE s.code = :code');
    $stmt->execute([':code' => $code, ':lang' => $lang ?? current_lang(), ':base' => I18N_DEFAULT]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return null;
    }
    return [
        'code'          => (string)$row['code'],
        'material'      => (string)($row['material'] ?? ''),
        'description'   => (string)($row['description'] ?? ''),
        'higher_levels' => (string)($row['higher_levels'] ?? ''),
    ];
}
