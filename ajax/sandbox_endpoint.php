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


switch ($action) {

    case 'getSubclasses':

        if (!$char_class) {
            echo json_encode(['error' => 'Missing class parameter']);
            break;
        }

        $stmt = $pdo->prepare('SELECT DISTINCT Subclass FROM Classes WHERE Class = :class');
        $stmt->execute([':class' => $char_class]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'getHitDice':

        if (!$char_class) {
            echo json_encode(['error' => 'Missing class parameter']);
            break;
        }

        $stmt = $pdo->prepare('SELECT DISTINCT Hit_Dice FROM Classes WHERE Class = :class');
        $stmt->execute([':class' => $char_class]);

        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));	
        break;


    default:
        echo json_encode(['error' => 'Unknown action']);
}

?>