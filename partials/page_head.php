<?php
/**
 * Shared <head> content for the account pages (sign-in, registration, verification, password reset).
 * Set $pageTitle before you include this file.
 * The stylesheet link has a version number (the file time), so browsers load a new style.css after a deploy.
 */
$styleVersion = (string) (@filemtime(__DIR__ . '/../Assets/style.css') ?: '1');
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle ?? '') ?></title>
<link rel="stylesheet" href="/Assets/style.css?v=<?= e($styleVersion) ?>">
