<?php

use app\helpers\GridHelper;
use frontend\modules\tasks\models\Task;
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
    'id',
    [
        'attribute' => 'type',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', "ALL"),
            ],
            Task::getTypes()
        ),
        'value' => function ($model, $key, $index, $column) {
          return Task::getTypes($model->type);
        },
    ],
    [
        'attribute' => 'active',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', "ALL"),
            ],
            Task::getActive()
        ),
        'value' => function ($model, $key, $index, $column) {
          return Task::getActive($model->active);
        },
    ],
    [
        'attribute' => 'start_date',
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig([], false, false),
        'value' => function ($model, $key, $index, $column) {
          $datetime = strtotime($model->start_date);
          return date(Yii::$app->params['lang']['date'], $datetime);
        },
    ],
    [
        'attribute' => 'start_time',
        'filter' => false,
        'value' => function ($model, $key, $index, $column) {
          return date(Yii::$app->params['lang']['time'], strtotime($model->start_time));
        },
    ],
    [
        'attribute' => 'end_time',
        'filter' => false,
        'value' => function ($model, $key, $index, $column) {
          return date(Yii::$app->params['lang']['time'], strtotime($model->end_time));
        },
    ],
    'periodText',
    'text',

    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'template' => '<div class="button_action">' . $actions . '</div>',
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
          return Url::to([$action, 'id' => $key]);
        },
        'updateOptions' => [
            'role' => 'modal-remote',
            'title' => '',
            'class' => 'btn btn-info btn-xs admin',
            'label' => "<i class=\"fa fa-pencil\"></i> " . Yii::t('app', 'Edit data'),
        ],
        'deleteOptions' => [
            'role' => 'modal-remote',
            'title' => '',
            'class' => 'btn btn-danger btn-xs admin',
            'label' => "<i class=\"glyphicon glyphicon-trash\"></i> " . Yii::t('app', 'Delete'),
            'data-confirm' => false, 'data-method' => false,// for override yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => Yii::t('app', 'Are you sure?'),
            'data-confirm-message' => Yii::t('app', 'Are you sure want to delete this item'),]
    ],

];

