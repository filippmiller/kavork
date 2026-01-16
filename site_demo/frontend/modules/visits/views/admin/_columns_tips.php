<?php
use common\components\widget\NumberRangerWidget;
use frontend\modules\certificate\models\Certificate;
use frontend\modules\visits\models\VisitorLog;
use frontend\components\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use app\helpers\GridHelper;

//d($_GET);
//ddd($searchModel);
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
    [
        'attribute' => 'user_id',
        'filterType' => GridView::FILTER_SELECT2,
        'headerOptions'=>['style' => 'min-width:200px;background-color:rgba(3, 85, 168, 0.05);'],
        'contentOptions'=>['style' => 'min-width:200px;background-color:rgba(3, 85, 168, 0.05);'],
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
        'attribute' => 'tip',
        'headerOptions' => ['style' => 'background-color:rgba(3, 85, 168, 0.05);'],
        'contentOptions' => ['style' => 'background-color:rgba(3, 85, 168, 0.05);'],
        'filter'=>false,
        'value'=>function ($model, $key, $index, $column) {
          if($model->tip===false)return Yii::t('app','error');
          return number_format($model->tip,  2,'.',' ').' '.Yii::$app->cafe->getCurrency();
        }
    ],
    [
      'attribute' => 'visitor_id',
      'value' => function ($model, $key, $index, $column) {
        if(!$model->visitor_id)return Yii::t('app', 'Anonymous');
        $visitor = $model->visitor;
        return $visitor->f_name.' '.$visitor->l_name;
      },
    ],
    [
        'attribute' => 'add_time',
        'filter'=>false,
        //'filterType' => GridView::FILTER_DATE_RANGE,
        //'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value'=> function ($model, $key, $index, $column) {
          if(!$model->add_time)return '-';
          $datetime=strtotime($model->add_time);
          return date(Yii::$app->params['lang']['datetime'], $datetime);
        },
    ],
    [
        'attribute' => 'finish_time',
        'filter'=>false,
        //'filterType' => GridView::FILTER_DATE_RANGE,
        //'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value'=> function ($model, $key, $index, $column) {
          if(!$model->finish_time || strtotime($model->finish_time)<100000)return '-';
          $datetime=strtotime($model->finish_time);
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
      'attribute' => 'cost',
      'filter'=>NumberRangerWidget::widget([
        'model'=>$searchModel,
        'attribute'=>'cost',
      ]),
      'value'=>function ($model, $key, $index, $column) {
        if($model->cost===false)return Yii::t('app','error');
        return number_format($model->cost,  2,'.',' ').' '.Yii::$app->cafe->getCurrency();
      }
    ],
    [
        'attribute' => 'pay_state',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter'=> ArrayHelper::merge(
            [
                '-2'=>Yii::t('app',"ALL"),
            ],
            \frontend\modules\visits\models\VisitorLog::payStatusList()
        ),
        'value' => function ($model, $key, $index, $column) {
          return '<div class="center-color '.VisitorLog::$colors_payment[$model->pay_state].'">'.VisitorLog::payStatusList($model->pay_state).'</div>';
        }
    ],

];

