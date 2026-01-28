<?php

require(__DIR__ . '/../../common/config/start_param.php');

// Clear Twig cache on deployment - DEPLOY_MARKER_v6
$twigCachePath = __DIR__ . '/../../frontend/runtime/Twig/cache';
if (is_dir($twigCachePath)) {
    $markerFile = $twigCachePath . '/.cache_cleared_v6';
    if (!file_exists($markerFile)) {
        // Recursively delete all cache files and subdirectories
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($twigCachePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }
        // Clear opcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        touch($markerFile);
    }
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);

(new yii\web\Application($config))->run();
