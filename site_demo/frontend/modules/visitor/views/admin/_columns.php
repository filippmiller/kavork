<?php
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\helpers\GridHelper;
use yii\helpers\Html;

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
	'code',
    'f_name',
    'l_name',
    'email',
    'phone',
    'visit_cnt',
    [
      'attribute' => 'create',
      'filterType' => GridView::FILTER_DATE_RANGE,
      'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
      'value'=> function ($model, $key, $index, $column) {
        $datetime=strtotime($model->create);
        return date(Yii::$app->params['lang']['datetime'], $datetime);
      },
    ],
    'notice',
    [
      'attribute'=>'lg',
      'filterType' => GridView::FILTER_SELECT2,
      'format' => 'raw',
      'filter'=> ArrayHelper::merge(
          [
              ''=>Yii::t('app',"ALL")
          ],
          Yii::$app->cafe->languageList
      ),
      'value' => function ($model, $key, $index, $column) {
        $lg= Yii::$app->cafe->languageList;
        return $lg[isset($lg[$model->lg])?$model->lg:Yii::$app->params['defaultLang']];
      },
    ],
    [
        'attribute'=>'franchisee_id',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'visible'    => Yii::$app->user->can('AllFranchisee'),
        'filter'=> ArrayHelper::merge(
            [
                ''=>Yii::t('app', 'ALL')
            ],
            \frontend\modules\franchisee\models\Franchisee::getList()
        ),
        'value' => function ($model, $key, $index, $column) {
          return isset($model->franchisee) ? $model->franchisee->name : null;
        },
    ],
    [
      'class' => 'kartik\grid\ActionColumn',
      'dropdown' => false,
      'template'=>'<div class="button_action"> {update} {delete} {print-card}</div>',
      'vAlign'=>'middle',
      'urlCreator' => function($action, $model, $key, $index) {
        return Url::to([$action,'id'=>$key]);
      },
      'updateOptions'=>[
        'role'=>'modal-remote',
        'title'=>'',
        'label'=>"<div class=\"btn btn-info btn-xs admin\"><i class=\"fa fa-pencil\"></i> ".Yii::t('app', 'Edit data')."</div>",
      ],
      'deleteOptions'=>['role'=>'modal-remote','title'=>'',
        'label'=>"<div class=\"btn btn-danger btn-xs admin\"><i class=\"fa fa-stop\"></i> ".Yii::t('app', 'Delete')."</div>",
        'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
        'data-request-method'=>'post',
        'data-toggle'=>'tooltip',
        'data-confirm-title'=>Yii::t('app', 'Are you sure?'),
        'data-confirm-message'=>Yii::t('app', 'Are you sure want to delete this item'),
      ],
      'buttons'=> [
          'print-card' => function ($url, $model) {
              return Html::a(
                  '<div class="btn btn-info btn-xs admin"><i class="fa fa-print"></i> '.Yii::t('app', 'Print&nbsp;card').'</div>',
                  ['/visitor/admin/print-card', 'id' => $model->id],
                  ['role' => 'modal-remote', 'title' => 'Print card']
              );
          },
      ],
    ],

];

