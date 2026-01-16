<?php

use yii\helpers\Html;

return [
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
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
            //'style' => 'max-width: 150px; max-height: auto;',
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
        'attribute' => 'quantity',
        'value' => function ($model, $key, $index, $column) {
          return $model->_summary_quantity;
        },
    ],
    [
        'attribute' => 'in_stock',
        'label' => Yii::t('app', 'In stock'),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->product) {
            return null;
          }
          return $model->product->quantity;
        },
    ],
    [
        'attribute' => 'price',
        'value' => function ($model, $key, $index, $column) {
          return number_format($model->price, 2, '.', ' ');
        },
    ],
    [
        'attribute' => 'sum',
        'value' => function ($model, $key, $index, $column) {
          return number_format($model->_summary_sum, 2, '.', ' ');
        },
    ],
    [
        'attribute' => 'vat',
        'filter' => false,
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
          if ($model->cost === false) {
            return Yii::t('app', 'error');
          }

          $content = [];

          $vat_list = json_decode(Yii::$app->cafe->params['vat_list'], true);
          foreach ($vat_list as $k => $vat) {
            foreach ($model->_summary_attributes as $name => $summaryValue) {
              if ($name == '_summary_vat_' . $vat['name']) {
                $content[] = '<nobr>' .
                    $vat['name'] .
                    (isset($vat['value']) ? ' (' . $vat['value'] . '%)' : '') .
                    ': ' . number_format($summaryValue, 2, '.', ' ') . ' ' .
                    Yii::$app->cafe->getCurrency() .
                    '</nobr>';
              }
            }
          }

          if (!empty($content)) {
            return implode('<br>', $content);
          }

          return null;
        },
    ],
    [
        'attribute' => 'vat_total',
        'filter' => false,
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) {
          if ($model->cost === false) {
            return Yii::t('app', 'error');
          }
          $vat_total = 0;

          $vat_list = json_decode(Yii::$app->cafe->params['vat_list'], true);
          foreach ($vat_list as $k => $vat) {
            foreach ($model->_summary_attributes as $name => $summaryValue) {
              if ($name == '_summary_vat_' . $vat['name']) {
                $vat_total += $summaryValue;
              }
            }
          }

          if ($vat_total > 0) {
            return number_format($vat_total, 2, '.', ' ') . ' ' .
                Yii::$app->cafe->getCurrency();
          }

          return null;
        },
    ],

    [
        'attribute' => 'cost',
        'value' => function ($model, $key, $index, $column) {
          return number_format($model->_summary_cost, 2, '.', ' ');
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

