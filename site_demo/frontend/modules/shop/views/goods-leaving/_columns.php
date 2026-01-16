<?php

use app\helpers\GridHelper;
use common\components\widget\NumberRangerWidget;
use kartik\grid\GridView;
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
        'attribute' => '_product_title',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
          return $model->product->title;
        },
    ],
    [
        'attribute' => '_product_barcode',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
          return $model->product->barcode;
        },
    ],
    [
        'attribute' => 'quantity',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'quantity',
            'value' => function ($model, $key, $index, $column) {
              return number_format($model->quantity, 2, '.', ' ');
            },
        ]),
    ],
    [
        'attribute' => 'created_at',
        'label' => Yii::t('app', 'Sell Date'),
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->created_at) return '-';
          $datetime = strtotime($model->created_at);
          return date(Yii::$app->params['lang']['datetime'], $datetime);
        },
    ],
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
            'data-confirm-message' => Yii::t('app', 'Are you sure want to delete this item'),],
    ],
];

