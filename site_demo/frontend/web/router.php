<?php
/**
 * Router script for PHP built-in development server
 */

// Get the path from the URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

// If file exists and is readable, let PHP serve it directly
if (is_file($file)) {
    return false;
}

// If the request is for a published asset, try to publish on-demand
if (strpos($uri, '/assets/') === 0) {
    $parts = explode('/', ltrim($uri, '/'));
    $hash = $parts[1] ?? null;
    if ($hash && is_dir(__DIR__ . '/assets/' . $hash)) {
        require __DIR__ . '/index.php';
        $am = \Yii::$app->assetManager;
        if (is_dir($am->basePath . DIRECTORY_SEPARATOR . $hash)) {
            return false;
        }
    }
}

// Route everything else through index.php
include __DIR__ . '/index.php';
