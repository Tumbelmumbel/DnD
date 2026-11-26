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
    $stmt = $pdo->prepare('SELECT id, weight_lb, rarity_code FROM items');
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $id = (int)$row['id'];
        $list[] = [
            'id'     => $id,
            'name'   => $names[(string)$id] ?? '',
            'weight' => $row['weight_lb'] === null ? null : (float)$row['weight_lb'],
            'rarity' => (string)$row['rarity_code'],
            'tags'   => $tags[$id] ?? [],
            'rules'  => $rules[$id] ?? [],
        ];
    }
    usort($list, fn(array $a, array $b): int => strcmp(content_sort_key($a['name']), content_sort_key($b['name'])));
    return $list;
}
