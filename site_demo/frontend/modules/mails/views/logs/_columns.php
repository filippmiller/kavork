<?php

use app\helpers\GridHelper;
use common\components\widget\NumberRangerWidget;
use frontend\modules\mails\models\MailsLog;
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
    'name',
    [
        'attribute' => 'cteated_at',
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->cteated_at) return '-';
          $timestamp = strtotime($model->cteated_at);
          return date(Yii::$app->params['lang']['datetime'], $timestamp);
        },
    ],
  /*[
    'attribute' => 'last_visitor_id',
    'filter'=>NumberRangerWidget::widget([
      'model'=>$searchModel,
      'attribute'=>'last_visitor_id',
      'value'=>function ($model, $key, $index, $column) {
        return number_format($model->last_visitor_id,  2,'.',' ');
      }
    ])
  ],*/
  /*   [
       'attribute' => 'mail_id',
       'filter'=>false,
         'value'=>function ($model, $key, $index, $column) {

         }
     ],*/
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
    ],
    [
        'attribute' => 'count',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'count',
            'value' => function ($model, $key, $index, $column) {
              return number_format($model->count, 2, '.', ' ');
            }
        ])
    ],
    [
        'attribute' => 'status',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', "ALL"),
            ],
            MailsLog::getStatus()
        ),
        'value' => function ($model, $key, $index, $column) {
          return MailsLog::getStatus($model->status);
        },
    ],
  //'content',
  //'params',
    [
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
    ],

];

