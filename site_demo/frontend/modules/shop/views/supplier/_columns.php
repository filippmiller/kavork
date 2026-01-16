<?php

use frontend\modules\franchisee\models\Franchisee;
use frontend\modules\users\models\Users;
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
    [
        'attribute' => 'cafe_id',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', 'ALL'),
            ],
            ArrayHelper::map(Users::getCafesList(), 'id', 'name')
        ),
        'value' => function ($model, $key, $index, $column) {
          return isset($model->cafe) ? $model->cafe->name : null;
        },
    ],
    'title',
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
            'data-confirm-message' => Yii::t('app', 'Are you sure want to delete this item'),
        ],
    ],

];

