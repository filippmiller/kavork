<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1;dbname=timecafe',
            'username' => 'max',
            'password' => '123456',
            'charset' => 'utf8',
            'attributes' => [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));",
            ],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
          'useFileTransport'=>true,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.beget.com',  // e.g. smtp.mandrillapp.com or smtp.gmail.com
                'username' => 'noreply@arturius.ru',
                'password' => 'OrAcY*G4',
                'port' => '465', // Port 25 is a very common port too
                'encryption' => 'ssl', // It is often used, check your provider or mail server specs
            ],
        ],
        'cache' => [
          //'class' => 'yii\caching\FileCache',
            'class' => 'yii\caching\DummyCache',
        ],
    ],
];
