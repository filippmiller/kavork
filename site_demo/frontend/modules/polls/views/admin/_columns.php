<?php

use app\helpers\GridHelper;
use frontend\modules\polls\models\Polls;
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
    'question',
  //'answers',
    [
        'attribute' => 'status',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', "ALL"),
            ],
            Polls::getStatus()
        ),
        'value' => function ($model, $key, $index, $column) {
          return Polls::getStatus($model->status);
        },
    ],
    [
        'attribute' => 'event',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', "ALL"),
            ],
            Polls::getEvents()
        ),
        'value' => function ($model, $key, $index, $column) {
          return Polls::getEvents($model->event);
        },
    ],
    [
        'attribute' => 'user_status',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', "ALL TYPE"),
            ],
            Polls::getUserStatus()
        ),
        'value' => function ($model, $key, $index, $column) {
          return Polls::getUserStatus($model->user_status);
        },
    ],
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
        'attribute' => 'created',
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->created) return '-';
          $datetime = strtotime($model->created);
          return date(Yii::$app->params['lang']['datetime'], $datetime);
        },
    ],
  /*    [
        'attribute' => 'other_ans',
        'filter'=>NumberRangerWidget::widget([
          'model'=>$searchModel,
          'attribute'=>'other_ans',
          'value'=>function ($model, $key, $index, $column) {
            return number_format($model->other_ans,  2,'.',' ');
          }
        ])
      ],*/
  /*[
    'attribute' => 'user_status',
    'filter'=>NumberRangerWidget::widget([
      'model'=>$searchModel,
      'attribute'=>'user_status',
      'value'=>function ($model, $key, $index, $column) {
        return number_format($model->user_status,  2,'.',' ');
      }
    ])
  ],*/
  /*    [
        'attribute' => 'event',
        'filter'=>NumberRangerWidget::widget([
          'model'=>$searchModel,
          'attribute'=>'event',
          'value'=>function ($model, $key, $index, $column) {
            return number_format($model->event,  2,'.',' ');
          }
        ])
      ],*/
  /*    [
        'attribute' => 'is_poll',
        'filter'=>NumberRangerWidget::widget([
          'model'=>$searchModel,
          'attribute'=>'is_poll',
          'value'=>function ($model, $key, $index, $column) {
            return number_format($model->is_poll,  2,'.',' ');
          }
        ])
      ],*/
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'template'=>'<div class="button_action">'.$actions.'</div>',
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
          return Url::to([$action, 'id' => $key]);
        },
        'updateOptions' => [
            'role' => 'modal-remote',
            'title' => '',
            'class' => 'btn btn-science-blue btn-xs admin',
            'label' => "<i class=\"fa fa-pencil\"></i> " . Yii::t('app', 'Edit data'),
        ],
        'viewOptions' => [
            'role' => 'modal-remote',
            'title' => '',
            'class' => 'btn btn-science-blue btn-xs admin',
            'label' => "<i class=\"fa fa-eye\"></i> " . Yii::t('app', 'View'),
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
            'data-confirm-message' => Yii::t('app', 'Are you sure want to delete this item'),
        ],
        'buttons' => [
            'result' => function ($url) {
              return \yii\helpers\Html::a(
                  '<i class="icon-metro-pie"></i>' . Yii::t('app', 'Result'),
                  $url,
                  [
                      'class' => 'btn btn-info btn-xs',
                      'role' => 'modal-remote',
                  ]
              );
            },
        ]

    ],

];

