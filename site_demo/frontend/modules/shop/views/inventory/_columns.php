<?php

use common\components\widget\NumberRangerWidget;
use frontend\modules\shop\models\ShopCategory;
use frontend\modules\shop\models\ShopSupplier;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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
        'attribute' => 'image',
        'filter' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
          return Html::img($model->getImageUrl(), [
              'class' => 'img_table',
          ]);
        },
    ],
    'title',
    'description',
    'barcode',
    [
        'attribute' => 'weight',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'weight',
            'value' => function ($model, $key, $index, $column) {
              return number_format($model->weight, 2, '.', ' ');
            },
        ]),
    ],
    [
        'attribute' => 'category_id',
        'format' => 'raw',
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', 'ALL'),
            ],
            ShopCategory::getList()
        ),
        'value' => function ($model) {
          return $model->category ? $model->category->title : null;
        },
    ],
    [
        'attribute' => 'supplier_id',
        'format' => 'raw',
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', 'ALL'),
            ],
            ShopSupplier::getList()
        ),
        'value' => function ($model) {
          return $model->supplier ? $model->supplier->title : null;
        },
    ],
    [
        'attribute' => 'accounting_critical_minimum',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'accounting_critical_minimum',
            'value' => function ($model, $key, $index, $column) {
              return number_format($model->accounting_critical_minimum, 2, '.', ' ');
            },
        ]),
    ],
    [
        'attribute' => 'quantity',
        'label' => Yii::t('main', 'Quantity'),
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
          if ($model->in_stock == 1) {
            return Yii::t('app', 'infinite amount');
          }
          if (isset($model->franchisee)) {
            $cafes = $model->franchisee->cafes;

            $content = [];

            $quantity = $model->getQuantity(Yii::$app->cafe->id);
            if ($quantity > 0) {
              $content[] = $quantity;
            }


            if (!empty($content)) {
              return implode('<br>', $content);
            }
          }
          return null;
        },
    ],
    [
        'attribute' => 'price',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'price',
            'value' => function ($model, $key, $index, $column) {
              return number_format($model->price, 2, '.', ' ');
            },
        ]),
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'template' => '<div class="button_action">' . $actions . '</div>',
        'vAlign' => 'middle',
        'urlCreator' => function ($action, $model, $key, $index) {
           return Url::to(['/shop/catalog/'.$action, 'id' => $key]);
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

