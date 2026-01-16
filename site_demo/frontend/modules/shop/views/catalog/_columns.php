<?php

use app\helpers\GridHelper;
use common\components\widget\NumberRangerWidget;
use frontend\modules\franchisee\models\Franchisee;
use frontend\modules\shop\models\ShopCategory;
use frontend\modules\shop\models\ShopSupplier;
use frontend\modules\users\models\Users;
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
    'title',
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
    'barcode',
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
        'label' => Yii::t('main', 'Quantity'),
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
          if ($model->in_stock == 1) {
            return Yii::t('app', 'infinite amount');
          }
          if (isset($model->franchisee)) {
            $cafes = $model->franchisee->cafes;

            $content = [];
            foreach ($cafes as $cafe) {
              $quantity = $model->getQuantity($cafe->id);
              if ($quantity > 0) {
                $content[] = $quantity;
              }
            }

            if (!empty($content)) {
              return implode('<br>', $content);
            }
          }
          return null;
        },
    ],
    'description',
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
        'attribute' => 'franchisee_id',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
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
            Users::getCafesList()
        ),
        'value' => function ($model, $key, $index, $column) {
          return isset($model->cafe) ? $model->cafe->name : null;
        },
    ],
    [
        'attribute' => 'external_sale_available',
        'format' => 'boolean',
        'filter' => GridHelper::getBooleanFilter(),
        'hAlign' => GridView::ALIGN_CENTER,
    ],

    [
        'attribute' => 'is_active',
        'format' => 'boolean',
        'filter' => GridHelper::getBooleanFilter(),
        'hAlign' => GridView::ALIGN_CENTER,
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
            'data-confirm-message' => Yii::t('app', 'Are you sure want to delete this item'),],
    ],

];

