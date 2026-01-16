<?php
return [
    'datetime_option' => [
        'useWithAddon' => true,
        'convertFormat' => true,
        'presetDropdown' => false,
        'initRangeExpr' => true,
        'hideInput' => true,
        'pluginOptions' => [
            'linkedCalendars' => false,
            'minDate' => "01/01/2014",
            'maxDate' => "1/1/" . (date("Y") + 2),
            'timePicker' => true,
            'timePickerIncrement' => 1,
            'separator' => ' - ',
            'format' => 'Y-m-d H:i:s',
            'locale' => [
                'format' => 'Y-m-d g:i A',
            ],
            'showDropdowns' => true,
            'ranges' => [
                Yii::t('app', "Today") => ["moment().startOf('day')", "moment()"],
                Yii::t('app', "Yesterday") => ["moment().startOf('day').subtract(1,'days')", "moment().endOf('day').subtract(1,'days')"],
                Yii::t('app', "Last {n} Days", ['n' => 7]) => ["moment().startOf('day').subtract(6, 'days')", "moment()"],
                Yii::t('app', "Last {n} Days", ['n' => 30]) => ["moment().startOf('day').subtract(29, 'days')", "moment()"],
                Yii::t('app', "This Month") => ["moment().startOf('month')", "moment().endOf('month')"],
                Yii::t('app', "Last Month") => ["moment().subtract(1, 'month').startOf('month')", "moment().subtract(1, 'month').endOf('month')"],
                Yii::t('app', "This Year") => ["moment().startOf('year')", "moment().endOf('year')"],
                Yii::t('app', "Last Year") => ["moment().subtract(1, 'year').startOf('year')", "moment().subtract(1, 'year').endOf('year')"],
            ]
        ]
    ],

];
