<?php

use app\helpers\GridHelper;
use kartik\grid\GridView;
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
        'attribute' => 'active_until',
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->active_until) return '-';
          $datetime = strtotime($model->active_until);
          return date(Yii::$app->params['lang']['datetime'], $datetime);
        },
    ],
    'balans',
    [
        'attribute' => 'tariff_id',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => \yii\helpers\ArrayHelper::merge(
            [
                '' => Yii::t('app', 'ALL')
            ],
            \frontend\modules\franchisee\models\FranchiseeTariffs::getList()
        ),
        'value' => function ($model, $key, $index, $column) {
          $tariff = $model->tariff_id ?
              \frontend\modules\franchisee\models\FranchiseeTariffs::find()
                  ->where(['id' => $model->tariff_id])
                  ->one() : null;

          return $tariff ? $tariff->lgName : '-';
        },
    ],
    'code',
    'max_cafe',
  //[
  //    'attribute' => 'roles',
  //    'format' => 'raw',
  //    'value' => function ($model) {
  //      if (empty($model->roles)) {
  //        return null;
  //      }
//
  //         $roles = explode(',', $model->roles);

  //         $content = [];
  //         foreach ($roles as $role) {
  //           $content[] = CafeAuthItem::getList($role);
  //         }
//
  //         return implode('<br>', $content);
  //      },
  //   ],
    [
        'attribute' => 'languages',
        'format' => 'raw',
        'value' => function ($model) {
          if (empty($model->languages)) {
            return null;
          }

          $languages = explode(',', $model->languages);
          $list = Yii::$app->params['lg_list'];

          $content = [];
          foreach ($languages as $language) {
            $content[] = isset($list[$language]) ? $list[$language] : Yii::t('app', 'Unknown');
          }

          return implode('<br>', $content);
        },
    ],
    [
        'attribute' => 'created_at',
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->created_at) return '-';
          $datetime = strtotime($model->created_at);
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
            'label' => "<div class=\"btn btn-info btn-xs admin\"><i class=\"fa fa-pencil\"></i> " . Yii::t('app', 'Edit data') . "</div>",
        ],
        'deleteOptions' => ['role' => 'modal-remote', 'title' => '',
            'label' => "<div class=\"btn btn-danger btn-xs admin\"><i class=\"glyphicon glyphicon-trash\"></i> " . Yii::t('app', 'Delete') . "</div>",
            'data-confirm' => false, 'data-method' => false,// for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => 'Are you sure?',
            'data-confirm-message' => 'Are you sure want to delete this item',]
    ],

];

