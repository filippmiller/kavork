<?php

use common\components\widget\NumberRangerWidget;
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
        'attribute' => 'name',
        'value' => function ($model, $key, $index, $column) {
          return $model->lgName;
        }
    ],
    [
        'attribute' => 'cafe_count',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'cafe_count',
            'value' => function ($model, $key, $index, $column) {
              return number_format($model->day_price, 2, '.', ' ');
            }
        ])
    ],
    [
        'attribute' => 'role_ids',
        'format' => 'raw',
        'visible' => Yii::$app->user->can('CafeRulesView'),
        'value' => function ($model, $key, $index, $column) {
          $content = [];
          foreach ($model->role_ids as $role) {
            $content[] = \frontend\modules\cafe\models\CafeAuthItem::getList($role);
          }
          return implode('<br>', $content);

        },
    ],
    [
        'attribute' => 'label',
        'filterType' => \frontend\components\GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => \yii\helpers\ArrayHelper::merge(
            [
                '' => Yii::t('app', 'All labels'),
            ],
            \frontend\modules\franchisee\models\FranchiseeTariffs::getLabelsLabels()
        ),
        'value' => function ($model, $key, $index, $column) {
          return \frontend\modules\franchisee\models\FranchiseeTariffs::getLabelsLabels($model->label);
        },
    ],
    'price',
    [
        'attribute' => 'active',
        'filterType' => \frontend\components\GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => \yii\helpers\ArrayHelper::merge(
            [
                '' => Yii::t('app', 'All status'),
            ],
            \frontend\modules\franchisee\models\FranchiseeTariffs::getActiveLabels()
        ),
        'value' => function ($model, $key, $index, $column) {
          return \frontend\modules\franchisee\models\FranchiseeTariffs::getActiveLabels($model->active);
        },
    ],
    [
        'attribute' => 'created_at',
        'filterType' => \frontend\components\GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => \app\helpers\GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->created_at) return '-';
          $datetime = strtotime($model->created_at);
          if (!$datetime) return '-';
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
            'data-confirm-message' => Yii::t('app', 'Are you sure want to delete this item'),]
    ],

];

