<?php
header("Content-Type: application/json");

// load your bootstrap (session_start, config, helpers, guard_protect, etc.)
require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../content_translation.php";

// run your session hardening
guard_protect();

$pdo  = db();

// GET action and class safely
$action = $_GET['action'] ?? null;
$char_class = $_GET['class'] ?? null;
$level = $_GET['level'] ?? null;

// The class is a code from Classes.class_code, for example "bard"
if ($char_class !== null && !preg_match('/^[a-z0-9_]{1,40}$/D', (string)$char_class)) {
    $char_class = null;
}


switch ($action) {

    case 'getSubclasses':

        if (!$char_class) {
            echo json_encode(['error' => 'Missing class parameter']);
            break;
        }

        // Base class (empty code, empty name) first, then the subclasses by the translated name
        $stmt = $pdo->prepare("SELECT DISTINCT subclass_code FROM Classes WHERE class_code = :class AND subclass_code <> ''");
        $stmt->execute([':class' => $char_class]);
        $list = lookup_list($pdo, $stmt->fetchAll(PDO::FETCH_COLUMN), 'subclass');

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Classes WHERE class_code = :class AND subclass_code = ''");
        $stmt->execute([':class' => $char_class]);
        if ((int)$stmt->fetchColumn() > 0) {
            array_unshift($list, ['code' => '', 'name' => '']);
        }

        echo json_encode($list);
        break;

    case 'getHitDice':

        if (!$char_class) {
            echo json_encode(['error' => 'Missing class parameter']);
            break;
        }

        $stmt = $pdo->prepare('SELECT DISTINCT Hit_Dice FROM Classes WHERE class_code = :class');
        $stmt->execute([':class' => $char_class]);

        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));	
        break;


    case 'getComponent':

        // One talent from Game_component_library, by its id
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || $id === null) {
            echo json_encode(['error' => 'Missing or invalid id parameter']);
            break;
        }

        // The texts in the current language (content_translation.php)
        $component = talent_component($pdo, $id);

        echo json_encode($component ?? ['error' => 'Talent not found']);
        break;

    case 'getContentNames':

        // All names of the game content in the current language (after a change of the language)
        $talents = [];
        foreach (talent_list($pdo) as $talent) {
            $talents[(string)$talent['id']] = $talent['name'];
        }
        echo json_encode(['lookup' => lookup_names($pdo), 'talents' => (object)$talents, 'items' => (object)item_names($pdo)]);
        break;

    case 'getSpells':

        // All spells in the current language, without the long texts (after a change of the language)
        echo json_encode(['spells' => spell_list($pdo), 'schools' => (object)(lookup_names($pdo)['spell_school'] ?? [])]);
        break;

    case 'getSpell':

        // The long texts of one spell (description, material, at higher levels), by its code
        $code = $_GET['code'] ?? '';
        if (!is_string($code) || !preg_match('/^[a-z0-9_]{1,60}$/D', $code)) {
            echo json_encode(['error' => 'Missing or invalid code parameter']);
            break;
        }
        echo json_encode(spell_details($pdo, $code) ?? ['error' => 'Spell not found']);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}

?>