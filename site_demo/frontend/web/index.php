<?php

require(__DIR__ . '/../../common/config/start_param.php');

// Clear Twig cache on deployment - DEPLOY_MARKER_v5
$twigCachePath = __DIR__ . '/../../frontend/runtime/Twig/cache';
if (is_dir($twigCachePath) && function_exists('opcache_reset')) {
    $markerFile = $twigCachePath . '/.cache_cleared_v5';
    if (!file_exists($markerFile)) {
        array_map('unlink', glob($twigCachePath . '/*'));
        opcache_reset();
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
