<?php
declare(strict_types=1);

/**
 * index.php
 * Entry point: send the visitor to the sign-in page.
 * (The old test file with hard-coded database credentials was removed.)
 */

require_once __DIR__ . '/bootstrap.php';

redirect('/login.php');
