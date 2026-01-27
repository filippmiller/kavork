<?php
// Production console configuration for Railway deployment
// This file is specifically for the console application (yii queue worker, migrations, etc.)

$dbHost = getenv('MYSQLHOST') ?: 'localhost';
$dbPort = getenv('MYSQLPORT') ?: '3306';
$dbName = getenv('MYSQLDATABASE') ?: 'railway';
$dbUser = getenv('MYSQLUSER') ?: 'root';
$dbPass = getenv('MYSQLPASSWORD') ?: '';

return [
    'bootstrap' => [
        'log',
        'queue', // Enable queue component for background email jobs
    ],
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
            'useFileTransport' => false, // Send real emails via SMTP
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
                'username' => getenv('SMTP_USERNAME') ?: 'anticafe294@gmail.com',
                'password' => getenv('SMTP_PASSWORD') ?: '',
                'port' => (int)(getenv('SMTP_PORT') ?: '587'),
                'encryption' => 'tls',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'traceLevel' => 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/console.log',
                ],
            ],
        ],
    ],
    'modules' => [
        // Disable all development modules in production
    ],
];
