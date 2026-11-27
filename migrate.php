<?php
declare(strict_types=1);

/**
 * migrate.php – runs the database migrations that did not run yet.
 *
 * Command line only:  php migrate.php            (run the new migrations)
 *                     php migrate.php --dry-run  (only list them)
 *
 * - The table schema_migrations lists the migration files that already ran.
 * - The script runs each new file migration_*.sql in name order and records it.
 * - It stops at the first error. MySQL cannot roll back table changes,
 *   so a stopped migration must be checked by hand.
 * - If the table schema_migrations does not exist, the script stops.
 *   It never runs all old migrations again.
 */

// Never from the web: the page must not exist for a visitor
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/config.php';

$dryRun = in_array('--dry-run', $argv, true);

/** Print a line and stop with an error code */
function migrate_fail(string $message): void {
    fwrite(STDERR, 'ERROR: ' . $message . PHP_EOL);
    exit(1);
}

/**
 * Split an SQL file into single statements.
 * Knows strings ('...', "...", `...`), comments (-- , #, /* * /) and the delimiter ;
 */
function migrate_split_sql(string $sql): array {
    $statements = [];
    $current = '';
    $length = strlen($sql);
    $quote = null;
    for ($i = 0; $i < $length; $i++) {
        $c = $sql[$i];
        $next = $i + 1 < $length ? $sql[$i + 1] : '';
        if ($quote !== null) {
            $current .= $c;
            if ($c === '\\' && $quote !== '`' && $next !== '') {
                $current .= $next;
                $i++;
            } elseif ($c === $quote) {
                $quote = null;
            }
            continue;
        }
        if ($c === "'" || $c === '"' || $c === '`') {
            $quote = $c;
            $current .= $c;
        } elseif (($c === '-' && $next === '-' && ($i + 2 >= $length || ctype_space($sql[$i + 2]))) || $c === '#') {
            $end = strpos($sql, "\n", $i);
            $i = $end === false ? $length : $end;
            $current .= "\n";
        } elseif ($c === '/' && $next === '*') {
            $end = strpos($sql, '*/', $i + 2);
            $i = $end === false ? $length : $end + 1;
            $current .= ' ';
        } elseif ($c === ';') {
            if (trim($current) !== '') $statements[] = trim($current);
            $current = '';
        } else {
            $current .= $c;
        }
    }
    if (trim($current) !== '') $statements[] = trim($current);
    return $statements;
}

$db = $APP_CONFIG['db'];
if ($db['host'] === '' || $db['name'] === '' || $db['user'] === '') {
    migrate_fail('Database settings are missing. Check the .env file.');
}
try {
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}",
        $db['user'],
        $db['pass'],
        $db['options']
    );
} catch (PDOException $e) {
    migrate_fail('No connection to the database: ' . $e->getMessage());
}

$table = $pdo->query("SHOW TABLES LIKE 'schema_migrations'")->fetchColumn();
if ($table === false) {
    migrate_fail('The table schema_migrations does not exist. Nothing was changed.');
}

$done = array_flip($pdo->query('SELECT filename FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN));
$files = glob(__DIR__ . '/migration_*.sql') ?: [];
sort($files, SORT_STRING);

$pending = array_values(array_filter($files, fn(string $f): bool => !isset($done[basename($f)])));
if ($pending === []) {
    echo 'No new migrations.' . PHP_EOL;
    exit(0);
}

foreach ($pending as $file) {
    $name = basename($file);
    if ($dryRun) {
        echo 'Pending: ' . $name . PHP_EOL;
        continue;
    }
    echo 'Run: ' . $name . PHP_EOL;
    $statements = migrate_split_sql((string)file_get_contents($file));
    foreach ($statements as $index => $statement) {
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            migrate_fail($name . ', statement ' . ($index + 1) . ': ' . $e->getMessage());
        }
    }
    $stmt = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (:f)');
    $stmt->execute([':f' => $name]);
    echo 'OK:  ' . $name . ' (' . count($statements) . ' statements)' . PHP_EOL;
}
exit(0);
