<?php

use app\helpers\GridHelper;
use common\components\widget\NumberRangerWidget;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

return [
    [
        'class' => 'common\components\CheckboxColumn',
        'width' => '20px',
    ],
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
    //'id',
    'text',
    [
        'attribute' => 'datetime',
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->datetime) return '-';
          $datetime = strtotime($model->datetime);
          return date(Yii::$app->params['lang']['datetime'], $datetime);
        },
    ],
    [
        'attribute' => 'status',
        'filterType' => GridView::FILTER_SELECT2,
        'format'     => 'raw',
        'filter'     => ArrayHelper::merge(
            [
                '' => Yii::t('app',"ALL"),
            ],
            \frontend\modules\tasks\models\DoTask::getStatus()
        ),
        'value'=> function ($model, $key, $index, $column) {
          return \frontend\modules\tasks\models\DoTask::getStatus($model->status);
        },
    ],
    [
        'attribute' => 'closedate',
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->closedate) return '-';
          $datetime = strtotime($model->closedate);
          return date(Yii::$app->params['lang']['datetime'], $datetime);
        },
    ],
    'comment',
    [
        'attribute' => 'user_id',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '0' => Yii::t('app', "ALL"),
                '-1' => Yii::t('app', "nobody"),
            ],
            ArrayHelper::map((array)Yii::$app->cafe->getUsersList(), 'id', 'name')
        ),
        'value' => function ($model, $key, $index, $column) {
          $user = $model->user;
          return $user ? $user->name : '-';
        },
    ]
];

