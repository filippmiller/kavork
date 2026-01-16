<?php
return [
  //'site_url'=>(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]",
    'adminEmail' => 'noreply@docowork.com',
    'supportEmail' => 'noreply@docowork.com',
    'robotEmail' => 'noreply@docowork.com',
    'adminName' => 'kavork.com',
    'mainLanding' => true,
    'checkMailSendDelay' => 0, // Время ожидания задания "Отправка чека на Email" в очереди перед выполнением, секунд
    'alert_timetable' => 60 * 60 * 24,//За сколько присылать оповещение о начале работы
    'period_timetable' => 60 * 60,//период для оповещения(период крона) для админа о его сеансе

    'user.passwordResetTokenExpire' => 3600,
    'defaultLang' => 'en-EN',
    "lg_list" => [
        'en-EN' => "English",
        'fr-FR' => "Français",
        'ru-RU' => "Русский",
    ],
    "lg_fullcalendar" => [
        'en-EN' => "en-gb",
        'fr-FR' => "fr",
        'ru-RU' => "ru",
    ],
    "franchisee" => [
        1 => "Our cafe",
    ],
    "currency" => [
        "USD" => "USD ($)",
        "CAN" => "CAN ($)",
        "RUB" => "RUB (ք)",
    ],
    "timeZone" => [
        "Europe/Moscow" => "Europe/Moscow",
        "America/New_York" => "America/New York",
        "America/Chicago" => "America/Chicago",
        "America/Denver" => "America/Denver",
        "America/Phoenix" => "America/Phoenix",
        "America/Los_Angeles" => "America/Los_Angeles",
        "America/Anchorage" => "America/Anchorage",
        "America/Adak" => "America/Adak",
        "Pacific/Honolulu" => "Pacific/Honolulu",
    ],
    'reportHour' => 2, //В какой час идет отправка почты с учетом часового пояса
    'monthsReportDate' => 1, //Дата в которую улетают месячные отчеты
    'weakReportDate' => 0, //порядковый № недели с учетом регионом
    'lang' => [
        "datetime" => 'Y-m-d H:i:s',
        "datetime_js" => 'Y-m-d H:i:s',
        "date" => 'Y-m-d',
        "time" => 'H:i',
    ],
    'site_url' => 'https://kavork.com',
    'defaultAccess' => [
        "VisitorView",
        "ChooseCafe",
        "MainAdmin",
        "VisitorCreate",
        "VisitorLogCreate",
        "VisitorLogView",
        "VisitorLogUpdate",
        "VisitorUpdate",
        "VisitorView",
        "SelfServiceModeEnter",
        "ShopInventoryCreate",
        "ShopInventoryUpdate",
        "ShopInventoryView",
        "ShopCatalogCreate",
        "ShopCatalogView",
        "ShopCatalogUpdate",
        //"Announcement",
        "TransactionView",
        //"ShopView",
    ],
    'managerAccess' => [
        "TaskMain",
        "TaskOnStart",
        "TaskReminder",
    ]
];
