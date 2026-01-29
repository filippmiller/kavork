<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$config = [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        'cafe',
        'selfservice',
    ],
    'controllerNamespace' => 'frontend\controllers',
    'language' => defined('LANGUAGE') ? LANGUAGE : 'en-EN',
    'sourceLanguage' => 'dev',
    'components' => [
        'authManager' => [
          //'class' => 'yii\rbac\DbManager',
            'class' => 'frontend\components\RBACmng',
        ],
        'assetManager' => [
            'appendTimestamp' => true, // добавляет таймстемп даты изменения файла в конец урла - помогает браузеру получать новые версии файла
            'assetMap' => [
                'elfinder_custom_theme.css' => '@web/themes/windows-10/css/theme.css'
            ],
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => [
                    'sourcePath' => null,   // не опубликовывать комплект
                  //'js' => [],
                    'css' => [],
                ],
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,   // не опубликовывать комплект
                    'js' => [
                        '//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js',
                    ]
                ],
                'mihaildev\ckeditor\Assets' => [
                    'sourcePath' => '@common/components/ckeditor/editor'
                ],
                'mihaildev\elfinder\Assets' => [
                    'css' => [
                        'css/elfinder.min.css',
                      //'css/theme.css',
                        'css/elfinder_custom_theme.css',
                    ],
                ],
                'johnitvn\ajaxcrud\CrudAsset' => [
                    'sourcePath' => '@frontend/components/ajaxcrud/assets',
                ],
            ]
        ],
        'formatter' => [
            'nullDisplay' => '-',
            'booleanFormat' => [
                '<span class="text-danger glyphicon glyphicon-remove"></span>',
                '<span class="text-success glyphicon glyphicon-ok"></span>',
            ],
        ],
        'request' => [
            'class' => 'frontend\components\HttpRequest',
            'csrfParam' => '_csrf-frontend',
            'enableCsrfValidation' => true,
            'enableCookieValidation' => true,
            'noCsrfValidationRoutes' => [
                'elfinder/connect',
            ],
        ],
        'user' => [
            'identityClass' => frontend\modules\users\models\Users::className(),
            'enableAutoLogin' => true,
            'on afterLogin' => function ($event) {
              frontend\modules\users\models\Users::afterLogin($event->identity->id);
            },
            'on afterLogout' => function ($event) {
	            frontend\modules\users\models\Users::afterLogout($event->identity->id);
            },
        ],
        'view' => [
            'class' => 'frontend\components\ViewBASE',
            'renderers' => [
                'twig' => [
                    'globals' => [
                        'AppAsset' => 'frontend\assets\AppAsset',
                    ]
                ]
            ]
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
          // Hide index.php
            'showScriptName' => false,
          // Use pretty URLs
            'enablePrettyUrl' => true,
            'rules' => [
                'compile_pay'=>'cafe/terminal',
                'cafe/terminal'=>'404',
                ['class' => 'frontend\components\UrlLocalisation'],
                'site/<action>' => '404',
              //'<models>/default/<action>' => '404',
                [
                    'class' => 'frontend\components\LoginPage',
                ],
                '<module:\w+>/<controller:\w+>/<action:(\w|-)+>' => '<module>/<controller>/<action>',
                '<module:\w+>/<controller:\w+>/<action:(\w|-)+>/<id:\d+>' => '<module>/<controller>/<action>',
              //'<models>/admin' => '/<models>/admin/index',
                '<alias:\w+|change-cafe>' => 'site/<alias>',
                '<models:visits|visitor|users>/<alias:(\w|-)+>' => '/<models>/default/<alias>',
            ],
            'normalizer' => [
                'class' => 'yii\web\UrlNormalizer',
            ],
        ],
        'session' => [
          // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
            'cookieParams' => [
                'httpOnly' => true,
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'sameSite' => PHP_VERSION_ID >= 70300 ? \yii\web\Cookie::SAME_SITE_LAX : null,
            ],
            'timeout' => 1800, // 30 minutes inactivity timeout
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\StreamTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                    'stream' => 'php://stderr',
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'cafe' => [
            'class' => 'frontend\modules\cafe\components\Cafe',
        ],
        'helper' => [
            'class' => 'common\components\Helper',
        ],
    ],
    'modules' => [
        'users' => [
            'class' => 'frontend\modules\users\Module',
        ],
        'gridview' => [
            'class' => '\kartik\grid\Module'
        ],
        'permit' => [
            'class' => 'developeruz\db_rbac\Yii2DbRbac',
            'viewPath' => '@frontend/views',
            'params' => [
                'userClass' => 'frontend\modules\users\models\Users',
                'accessRoles' => ['root']
            ]
        ],
        'cafe' => [
            'class' => 'frontend\modules\cafe\Module',
        ],
        'franchisee' => [
            'class' => 'frontend\modules\franchisee\Module',
        ],
        'tariffs' => [
            'class' => 'frontend\modules\tariffs\Module',
        ],
        'visitor' => [
            'class' => 'frontend\modules\visitor\Module',
        ],
        'visits' => [
            'class' => 'frontend\modules\visits\Module',
        ],
        'language' => [
            'class' => 'frontend\modules\language\Module',
        ],
        'certificate' => [
            'class' => 'frontend\modules\certificate\Module',
        ],
        'templates' => [
            'class' => 'frontend\modules\templates\Module',
        ],
        'shop' => [
            'class' => 'frontend\modules\shop\Module',
        ],
        'selfservice' => [
            'class' => 'frontend\modules\selfservice\Module',
        ],
        'report' => [
            'class' => 'frontend\modules\report\Module',
        ],
        'mails' => [
            'class' => 'frontend\modules\mails\Module',
        ],
        'tasks' => [
            'class' => 'frontend\modules\tasks\Module',
        ],
        'polls' => [
            'class' => 'frontend\modules\polls\Module',
        ],
        'timetable' => [
            'class' => 'frontend\modules\timetable\Module',
        ],
        'paypal' => [
            'class' => 'frontend\modules\paypal\Module',
            'clientId' => $params['paypal_client_id'],
            'clientSecret' => $params['paypal_client_secret'],
          //'baseUrl' => $personal['site_url'].'/payment/finish',
          //'isProduction' => false,
          // This is config file for the PayPal system
            'config' => [
                'currency' => "USD",
                'http.ConnectionTimeOut' => 30,
                'http.Retry' => 1,
                'mode' => 'sandbox', // development (sandbox) or production (live) mode
                'log.LogEnabled' => YII_DEBUG ? 1 : 0,
                'log.FileName' => '@runtime/logs/paypal.log',
                'log.LogLevel' => 'FINE', // 'FINE','INFO','WARN','ERROR';
            ]
        ],
        'i18n' => [
            'class' => 'frontend\modules\i18n\Module',
        ],
    ],
    'container' => [
        'definitions' => [
            'yii\data\Pagination' => [
                'defaultPageSize' => 50,
                'pageSizeLimit' => [1, 5000],
            ],
            'kartik\grid\GridView' => [
                'filterSelector' => 'select[name="per-page"]',
            ],
            'yii\bootstrap\ActiveField' => [
                'class' => 'frontend\helpers\MyActiveField',
            ],
            'yii\helpers\Html' => [
                'class' => 'frontend\helpers\MyHtml',
            ],
        ],
    ],
    'controllerMap' => [
        'elfinder' => [
            'class' => 'mihaildev\elfinder\Controller',
            'access' => ['@'], //глобальный доступ к фаил менеджеру @ - для авторизорованных , ? - для гостей , чтоб открыть всем ['@', '?']
            'disabledCommands' => ['netmount'], //отключение ненужных команд https://github.com/Studio-42/elFinder/wiki/Client-configuration-options#commands
            'roots' => [
                [
                    'baseUrl' => '@web',
                    'basePath' => '@webroot',
                    'path' => 'img/asset',
                    'name' => ['category' => 'app', 'message' => 'System'],
                    'access' => ['read' => '*', 'write' => 'root'],
                ],
                [
                    'class' => 'common\components\elfinder\volume\FranchiseePath',
                    'baseUrl' => '@web',
                    'basePath' => '@webroot',
                    'path' => 'img/global/{id}',
                    'name' => ['category' => 'app', 'message' => 'Global'],
                ],
                [
                    'class' => 'common\components\elfinder\volume\CafePath',
                    'baseUrl' => '@web',
                    'basePath' => '@webroot',
                    'path' => 'img/cafe/{id}',
                    'name' => ['category' => 'app', 'message' => 'Cafe'],
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_DEBUG) {
  if (!isset($config['modules']['gii']['generators'])) {
    $config['modules']['gii']['generators'] = [];
  }

  $config['modules']['gii']['generators']['migration'] = ['class' => \ymaker\gii\migration\Generator::className()];
  $config['modules']['gii']['generators']['ajaxcrud'] = [
      'class' => 'frontend\myTemplates\crud\Generator', // generator class
      'templates' => [ //setting for out templates
          'default' => '@frontend/myTemplates/crud/default', // template name => path to template
      ],
  ];

}

return $config;
