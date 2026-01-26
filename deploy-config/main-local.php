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
            'useFileTransport' => false, // Changed from true - now sends real emails
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
    ],
];
