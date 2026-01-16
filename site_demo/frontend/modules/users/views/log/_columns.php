<?php
  use yii\helpers\Url;
  use kartik\grid\GridView;
  use common\components\widget\NumberRangerWidget;
  use yii\helpers\ArrayHelper;
  use frontend\modules\users\models\Users;
  use frontend\modules\franchisee\models\Franchisee;
  use app\helpers\GridHelper;

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
      'attribute' => 'user_id',
      'filterType' => GridView::FILTER_SELECT2,
      'format' => 'raw',
      'filter'=> ArrayHelper::merge(
          [
              '0'=>Yii::t('app',"ALL"),
              '-1'=>Yii::t('app',"nobody"),
          ],
          ArrayHelper::map((array)Yii::$app->cafe->getUsersList(), 'id', 'name')
      ),
      'value' => function ($model, $key, $index, $column) {
        $user=$model->user;
        return $user?$user->name:'-';
      },
    ],
    [
      'attribute' => 'start',
      'filterType' => GridView::FILTER_DATE_RANGE,
      'filterWidgetOptions' =>GridHelper::getFilterDateRangeConfig(),
      'value'=> function ($model, $key, $index, $column) {
        if(!$model->start)return '-';
        $datetime=strtotime($model->start);
        return date(Yii::$app->params['lang']['datetime'], $datetime);
      },
    ],
    [
      'attribute' => 'finish',
      'filterType' => GridView::FILTER_DATE_RANGE,
      'filterWidgetOptions' =>GridHelper::getFilterDateRangeConfig(),
      'value'=> function ($model, $key, $index, $column) {
        if(!$model->finish)return '-';
        $datetime=strtotime($model->finish);
        return date(Yii::$app->params['lang']['datetime'], $datetime);
      },
    ],
      [
          'attribute' => 'duration',
          'value'=>function ($model, $key, $index, $column) {
            return Yii::$app->helper->echo_time($model->duration);
          }
      ],
  [
  'class' => 'kartik\grid\ActionColumn',
  'dropdown' => false,
  'template'=>'<div class="button_action">'.$actions.'</div>',
  'vAlign'=>'middle',
  'urlCreator' => function($action, $model, $key, $index) {
  return Url::to([$action,'id'=>$key]);
  },
  'updateOptions'=>[
  'role'=>'modal-remote',
  'title'=>'',
  'class' => 'btn btn-science-blue btn-xs admin',
  'label'=>"<i class=\"fa fa-pencil\"></i> ".Yii::t('app', 'Edit data'),
  ],
  'deleteOptions'=>[
  'role'=>'modal-remote',
  'title'=>'',
  'class' => 'btn btn-danger btn-xs admin',
  'label'=>"<i class=\"glyphicon glyphicon-trash\"></i> ".Yii::t('app', 'Delete'),
  'data-confirm'=>false, 'data-method'=>false,// for override yii data api
  'data-request-method'=>'post',
  'data-toggle'=>'tooltip',
  'data-confirm-title'=>Yii::t('app', 'Are you sure?'),
  'data-confirm-message'=>Yii::t('app', 'Are you sure want to delete this item'),]
  ],

  ];

