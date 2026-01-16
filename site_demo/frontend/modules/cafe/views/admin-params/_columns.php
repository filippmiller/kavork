<?php

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
    'name',
    [
        'attribute' => 'vat_list',
        'format' => 'raw',
        'value' => function ($model) {
          $vats = json_decode($model->vat_list, true);

          $content = [];
          if (is_array($vats)) {
            foreach ($vats as $vat) {
              $content[] = $vat['name'] . ': ' . $vat['value'] . '%';
            }
          }

          return !empty($content) ? implode('<br>', $content) : null;
        }
    ],
    [
        'attribute' => 'banknote_list',
        'format' => 'raw',
        'value' => function ($model) {
          $banknotes = !empty($model->banknote_list) ? explode(',', $model->banknote_list) : [];
          $content = [];
          foreach ($banknotes as $banknoteValue) {
            $content[] = $banknoteValue;
          }

          return !empty($content) ? implode(', ', $content) : null;
        },
    ],
    [
        'attribute' => 'time_zone',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '' => Yii::t('app', 'ALL')
            ],
            Yii::$app->params['timeZone']
        ),
        'value' => function ($model, $key, $index, $column) {
          $param = Yii::$app->params['timeZone'];
          return isset($param[$model->time_zone]) ? $param[$model->time_zone] : "-";
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
            'label' => "<div class=\"btn btn-info btn-xs admin\"><i class=\"fa fa-pencil\"></i> " . Yii::t('app', 'Edit data') . "</div>",
        ],
        'deleteOptions' => ['role' => 'modal-remote', 'title' => '',
            'label' => "<div class=\"btn btn-warning btn-xs admin\"><i class=\"fa fa-stop\"></i> " . Yii::t('app', 'Delete') . "</div>",
            'data-confirm' => false, 'data-method' => false,// for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => Yii::t('app', 'Are you sure?'),
            'data-confirm-message' => Yii::t('app', 'Are you sure want to delete this item'),]
    ],

];

