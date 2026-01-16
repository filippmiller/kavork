<?php
$dbHost = getenv('MYSQLHOST') ?: 'localhost';
$dbPort = getenv('MYSQLPORT') ?: '3306';
$dbName = getenv('MYSQLDATABASE') ?: 'railway';
$dbUser = getenv('MYSQLUSER') ?: 'root';
$dbPass = getenv('MYSQLPASSWORD') ?: '';
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}",
            'username' => $dbUser,
            'password' => $dbPass,
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];
