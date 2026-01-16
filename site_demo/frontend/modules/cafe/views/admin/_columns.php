<?php

use common\components\widget\NumberRangerWidget;
use frontend\modules\franchisee\models\Franchisee;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

return [
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
    'id',
    'name',
    'address',
    [
        'attribute' => 'max_person',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'max_person',
        ])
    ],
    'child_discount',
    [
        'attribute' => 'franchisee_id',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'visible' => Yii::$app->user->can('AllFranchisee'),
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', 'ALL'),
            ],
            Franchisee::getList()
        ),
        'value' => function ($model, $key, $index, $column) {
          return isset($model->franchisee) ? $model->franchisee->name : null;
        },
    ],
  // [
  //     'attribute' => 'role_ids',
  //     'format' => 'raw',
  //     'visible' => Yii::$app->user->can('CafeRulesView'),
  //      'value' => function ($model, $key, $index, $column) {
  //        $roles = $model->authItems;
  //        $content = [];
  //        foreach ($roles as $role) {
  //          $content[] = CafeAuthItem::getList($role['name']);
  //        }
  //        return implode('<br>', $content);

  //     },
  //  ],
    [
        'attribute' => 'currency',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', 'ALL'),
            ],
            Yii::$app->params['currency']
        ),
        'value' => function ($model, $key, $index, $column) {
          $param = Yii::$app->params['currency'];
          return isset($param[$model->currency]) ? $param[$model->currency] : "-";
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
        'buttons' => [
            'rules' => function ($url, $model) {
              return Html::a('<i class="fa fa-sitemap"></i>&nbsp;' . Yii::t('app', 'Rules'), ['update-rules', 'id' => $model->id], [
                  'class' => 'btn btn-success btn-xs',
                  'role' => 'modal-remote',
              ]);
            },
            'discounts' => function ($url, $model) {
              return Html::a('<i class="fa fa-gift"></i>&nbsp;' . Yii::t('app', 'Discounts'), ['update-discounts', 'id' => $model->id], [
                  'class' => 'btn btn-default btn-xs',
                  'role' => 'modal-remote',
              ]);
            },
        ],
        'updateOptions' => [
            'role' => 'modal-remote',
            'title' => '',
            'label' => "<div class=\"btn btn-info btn-xs admin\"><i class=\"fa fa-pencil\"></i> " . Yii::t('app', 'Edit data') . "</div>",
        ],
        'deleteOptions' => ['role' => 'modal-remote', 'title' => '',
            'label' => "<div class=\"btn btn-danger btn-xs admin\"><i class=\"fa fa-trash\"></i> " . Yii::t('app', 'Delete') . "</div>",
            'data-confirm' => false, 'data-method' => false,// for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => Yii::t('app', 'Are you sure?'),
            'data-confirm-message' => Yii::t('app', 'Are you sure want to delete this item'),]
    ],

];

