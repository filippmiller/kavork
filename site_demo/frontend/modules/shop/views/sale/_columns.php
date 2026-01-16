<?php

use app\helpers\GridHelper;
use common\components\widget\NumberRangerWidget;
use frontend\modules\visits\models\VisitorLog;
use kartik\grid\GridView;
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
    [
        'attribute' => 'image',
        'label' => Yii::t('app', 'Image'),
        'filter' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
          if (!$model->product) {
            return null;
          }
          return Html::img($model->product->getImageUrl(), [
              'class' => 'img_table',
          ]);

        },
    ],
    [
        'attribute' => '_product_title',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
          return $model->getProductTitle();
        },
    ],
    [
        'attribute' => '_item_weight',
        'filter' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
          if (!$model->product) {
            return null;
          }
          return $model->product->weight;
        },
    ],
    [
        'attribute' => '_visitor_name',
        'value' => function ($model, $key, $index, $column) {
          if (!$model->sale_id) {
            return null;
          }

          $sale = $model->sale;

          if (!$sale->visitor_id) {
            return Yii::t('app', 'Anonymous');
          }

          return $sale->visitor->f_name . ' ' . $sale->visitor->l_name;
        },
    ],
    [
        'attribute' => 'quantity',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'quantity',
        ]),
        'value' => function ($model, $key, $index, $column) {
          return number_format($model->quantity, 0, '.', ' ');
        },
    ],
    [
        'attribute' => 'in_stock',
        'label' => Yii::t('app', 'in stock'),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->product) {
            return null;
          }
          $product = $model->product;

          return $product->in_stock == 1 ? '-' : $product->quantity;
        },
    ],
    [
        'attribute' => 'created_at',
        'label' => Yii::t('app', 'Sell Date'),
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->created_at) return '-';
          $datetime = strtotime($model->created_at);
          return date(Yii::$app->params['lang']['datetime'], $datetime);
        },
    ],
    [
        'attribute' => 'pay_state',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => \yii\helpers\ArrayHelper::merge(
            [
                '-2' => Yii::t('app', "ALL"),
            ],
            VisitorLog::payStatusList()
        ),
        'value' => function ($model, $key, $index, $column) {
          $sale = $model->getSale()->one();
          if (!$sale || !isset(VisitorLog::$colors_payment[$sale->pay_state])) return '';
          return '<div class="center-color ' . VisitorLog::$colors_payment[$sale->pay_state] . '">' . VisitorLog::payStatusList($sale->pay_state) . '</div>';
        },
        'noWrap' => true,
    ],
    [
        'attribute' => 'price',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'price',
        ]),
        'value' => function ($model, $key, $index, $column) {
          return number_format($model->price, 2, '.', ' ');
        },
    ],
    [
        'attribute' => 'sum',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'sum',
        ]),
        'value' => function ($model, $key, $index, $column) {
          return number_format($model->sum, 2, '.', ' ');
        },
    ],
    [
        'attribute' => 'vat',
        'filter' => false,
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
          if ($model->cost === false) return Yii::t('app', 'error');
          $out = array();
          $vat_list = $model->vat;
          if (!is_array($vat_list)) return "-";
          foreach ($vat_list as $vat) {
            $out[] = '<nobr>' .
                $vat['name'] .
                (isset($vat['value']) ? ' (' . $vat['value'] . '%)' : '') .
                ': ' . number_format($vat['vat'] * $model->quantity, 2, '.', ' ') . ' ' .
                Yii::$app->cafe->getCurrency() .
                '</nobr>';
          }
          return implode('<br>', $out);
        },
    ],

    [
        'attribute' => 'cost',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'cost',
            'value' => function ($model, $key, $index, $column) {
              return number_format($model->cost, 2, '.', ' ');
            },
        ]),
    ],
    [
        'attribute' => 'created_by',
        'label' => Yii::t('app', 'Admin'),
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => \yii\helpers\ArrayHelper::merge(
            [
                '0' => Yii::t('app', "ALL"),
            ],
            \yii\helpers\ArrayHelper::map((array)Yii::$app->cafe->getUsersList(), 'id', 'name')
        ),
        'value' => function ($model, $key, $index, $column) {
          return ($model->createdBy) ? $model->createdBy->name : null;
        },
    ],
    [
        'attribute' => 'description',
        'label' => Yii::t('app', 'Description'),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->product) {
            return null;
          }

          return $model->product->description;
        },
    ],
];

