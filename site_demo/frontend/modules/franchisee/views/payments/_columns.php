<?php

use app\helpers\GridHelper;
use frontend\modules\franchisee\models\Franchisee;
use frontend\modules\franchisee\models\FranchiseePayments;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;

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
        'attribute' => 'franchisee_id',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'visible' => Yii::$app->user->can('AllFranchisee'),
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', 'ALL')
            ],
            Franchisee::getList()
        ),
        'value' => function ($model, $key, $index, $column) {
          return isset($model->franchisee) ? $model->franchisee->name : null;
        },
    ],
    'code',
    [
        'attribute' => 'status',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', 'ALL')
            ],
            FranchiseePayments::getStatus()
        ),
        'value' => function ($model, $key, $index, $column) {
          return FranchiseePayments::getStatus($model->status);
        },
    ],
    [
        'attribute' => 'tariff_id',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', 'ALL')
            ],
            \frontend\modules\franchisee\models\FranchiseeTariffs::getList()
        ),
        'value' => function ($model, $key, $index, $column) {
          $tarif = \frontend\modules\franchisee\models\FranchiseeTariffs::find()
              ->where(['id' => $model->tariff_id])
              ->one();

          return $tarif ? $tarif->lgName : '-';
        },
    ],
    'count',
    'sum',
  //'comment',
    [
        'attribute' => 'created_at',
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->created_at) return '-';
          $datetime = strtotime($model->created_at);
          if (!$datetime) return '-';
          return date(Yii::$app->params['lang']['datetime'], $datetime);
        },
    ],
  /*[
      'class' => 'kartik\grid\ActionColumn',
      'dropdown' => false,
      'template' => $actions,
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
  ],*/

];

