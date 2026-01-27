<?php
/**
 * Console application configuration for PRODUCTION
 * Removes all development dependencies (kint, debug, gii)
 */

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        'queue',
        // 'kint' removed for production
    ],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
        ],
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => [
                'yii\queue\db\migrations',
            ],
        ],
    ],
    'components' => [
        'helper' => [
            'class' => 'common\components\Helper',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'cafe' => [
            'class' => 'frontend\modules\cafe\components\Cafe',
        ],
    ],
    'modules' => [
        // kint module removed for production
    ],
    'params' => $params,
];
