<?php
header("Content-Type: application/json");

// load your bootstrap (session_start, config, helpers, guard_protect, etc.)
require_once __DIR__ . "/../bootstrap.php";

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

        // Base class (empty code) first, then the subclasses by name
        $stmt = $pdo->prepare("SELECT DISTINCT subclass_code AS code, Subclass AS name FROM Classes WHERE class_code = :class ORDER BY subclass_code <> '', Subclass");
        $stmt->execute([':class' => $char_class]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
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

        $stmt = $pdo->prepare('
            SELECT id, name, description, action_type, `range`, attribute, attack_type, damage_type,
                   magic_effect, projectile, uses, uses_spent, duration, precondition, components, material
            FROM Game_component_library
            WHERE id = :id AND FIND_IN_SET(\'talent\', type) > 0
        ');
        $stmt->execute([':id' => $id]);
        $component = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($component ?: ['error' => 'Talent not found']);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}

?>