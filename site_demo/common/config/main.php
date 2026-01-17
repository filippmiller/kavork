<?php

use kartik\mpdf\Pdf;

$twigFunction = require(dirname(dirname(__DIR__)) . '/common/components/twigFunctionList.php');
$twigFunction['translate'] = '\Yii::t';

$config = [
    'charset' => 'utf-8',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'bootstrap' => [
        'securityHeaders',
    ],
    'components' => [
        // Security components
        'securityHeaders' => [
            'class' => 'common\components\SecurityHeaders',
            'enableCSP' => false, // Enable after testing
        ],
        'loginRateLimiter' => [
            'class' => 'common\components\LoginRateLimiter',
            'maxAttempts' => 10,
            'lockoutDuration' => 1800, // 30 minutes
            'attemptWindow' => 900,    // 15 minutes
        ],
        'apiRateLimiter' => [
            'class' => 'common\components\ApiRateLimiter',
        ],
        'queue' => [
            'class' => \yii\queue\db\Queue::class,
            'as log' => \yii\queue\LogBehavior::class,
            'db' => 'db', // DB connection component or its config
            'tableName' => '{{%queue}}', // Table name
            'channel' => 'default', // Queue channel key
            'mutex' => \yii\mutex\MysqlMutex::class, // Mutex used to sync eries
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mailer' => [
            'viewPath' => '@common/mail',
            'htmlLayout' => 'layouts/html',
            'textLayout' => 'layouts/text',
            'messageConfig' => [
                'charset' => 'UTF-8',
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'TwigString' => [
            'class' => 'common\components\TwigString',
            'params' => [
                'cachePath' => '@runtime/Twig/cache',
                'functions' => $twigFunction,
            ],
        ],
        'view' => [
            'class' => 'yii\web\View',
            'defaultExtension' => 'twig',
            'renderers' => [
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    'cachePath' => '@runtime/Twig/cache',
                  // Array of twig options:
                    'options' => YII_DEBUG ? [
                        'debug' => true,
                        'auto_reload' => true,
                    ] : [
                        'auto_reload' => true,
                    ],
                    'globals' => [
                        'html' => '\yii\helpers\Html',
                        'url' => 'yii\helpers\Url',
                        'ActiveForm' => 'yii\bootstrap\ActiveForm',
                        'MultipleInput' => 'unclead\multipleinput\MultipleInput',
                        'MaskedInput' => 'yii\widgets\MaskedInput',
                    ],
                    'functions' => $twigFunction,
                    'uses' => ['yii\bootstrap'],
                    'extensions' => YII_DEBUG ? [
                        '\Twig_Extension_Debug',
                    ] : [
                    ]
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/language',
                    'on missingTranslation' => ['common\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'app' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/language',
                    'on missingTranslation' => ['common\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'main' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/language',
                    'on missingTranslation' => ['common\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'editor' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/language',
                    'on missingTranslation' => ['common\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'editor_render' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/language',
                    'on missingTranslation' => ['common\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'kvbase' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/language',
                    'on missingTranslation' => ['common\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
                'selfservice' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/language',
                    'on missingTranslation' => ['common\components\TranslationEventHandler', 'handleMissingTranslation']
                ],
            ],
        ],
      // setup Krajee Pdf component
        'pdf' => [
            'class' => Pdf::classname(),
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '.kv-heading-1{font-size:18px}',
        ]
    ],
];

if (YII_DEBUG) {
  // configuration adjustments for 'dev' environment
  $config['bootstrap'][] = 'debug';
  $config['modules']['debug'] = [
      'class' => 'yii\debug\Module',
      'allowedIPs' => ['*']
  ];
  $config['bootstrap'][] = 'gii';
  $config['modules']['gii'] = [
      'class' => 'yii\gii\Module',
  ];

  //Add kint
  $config['bootstrap'][] = 'kint';
  $config['modules']['kint'] = [
      'class' => 'digitv\kint\Module',
  ];
}

return $config;