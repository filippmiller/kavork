<?php

use app\helpers\GridHelper;
use common\components\widget\NumberRangerWidget;
use frontend\modules\certificate\models\Certificate;
use frontend\modules\visits\models\VisitorLog;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

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
    'id',
    [
        'attribute' => 'visitor',
        'value' => function ($model, $key, $index, $column) {
          if (!$model->visitor_id) return Yii::t('app', 'Anonymous');
          $visitor = $model->visitor;
          return $visitor->f_name . ' ' . $visitor->l_name;
        },
    ],
    [
        'attribute' => 'visitor_email',
        'value' => function ($model, $key, $index, $column) {
          if (!$model->visitor_id) {
            if (!empty($model->notice) && !empty($model->notice['visitor_email'])) {
              return $model->notice['visitor_email'];
            }
            return '';
          };
          $visitor = $model->visitor;
          return $visitor->email;
        },
    ],
    [
        'attribute' => 'visitor_id',
        'value' => function ($model, $key, $index, $column) {
          if (!$model->visitor_id) return '-';
          return $model->visitor_id;
        },
    ],
    [
        'attribute' => 'visitor_code',
        'value' => function ($model, $key, $index, $column) {
          if (!$model->visitor_id) return '';
          $visitor = $model->visitor;
          return $visitor->code;
        },
    ],
    'guest_m',
    'guest_chi',
    [
        'attribute' => 'add_time',
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->add_time) return '-';
          $datetime = strtotime($model->add_time);
          return date(Yii::$app->params['lang']['datetime'], $datetime);
        },
    ],
    [
        'attribute' => 'finish_time',
        'filterType' => GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => GridHelper::getFilterDateRangeConfig(),
        'value' => function ($model, $key, $index, $column) {
          if (!$model->finish_time || strtotime($model->finish_time) < 100000) return '-';
          $datetime = strtotime($model->finish_time);
          return date(Yii::$app->params['lang']['datetime'], $datetime);
        },
    ],
    [
        'attribute' => 'certificate_type',
        'filter' => Certificate::getTypeLabels(),
        'format' => 'raw',
        'value' => function ($model) {
          return $model->getCertificateTypeString();
        },
    ],
    [
        'attribute' => 'certificate_number',
    ],
    [
        'attribute' => 'duration',
        'value' => function ($model, $key, $index, $column) {
          return Yii::$app->helper->echo_time($model->duration);
        }
    ],
    [
        'attribute' => 'sum',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'sum',
        ]),
        'value' => function ($model, $key, $index, $column) {
          if ($model->cost === false) return Yii::t('app', 'error');
          return number_format($model->sum, 2, '.', ' ') . ' ' . Yii::$app->cafe->getCurrency();
        }
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
                ': ' . number_format($vat['vat'], 2, '.', ' ') . ' ' .
                Yii::$app->cafe->getCurrency() .
                '</nobr>';
          }
          return implode('<br>', $out);
        }
    ],
    [
        'attribute' => 'cost',
        'filter' => NumberRangerWidget::widget([
            'model' => $searchModel,
            'attribute' => 'cost',
        ]),
        'value' => function ($model, $key, $index, $column) {
          if ($model->cost === false) return Yii::t('app', 'error');
          return number_format($model->cost, 2, '.', ' ') . ' ' . Yii::$app->cafe->getCurrency();
        }
    ],
    [
        'attribute' => 'pay_state',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '-2' => Yii::t('app', "ALL"),
            ],
            \frontend\modules\visits\models\VisitorLog::payStatusList()
        ),
        'value' => function ($model, $key, $index, $column) {
          return '<div class="center-color ' . VisitorLog::$colors_payment[$model->pay_state] . '">' . VisitorLog::payStatusList($model->pay_state) . '</div>';
        }
    ],
    [
        'attribute' => 'status',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' =>
            [
                '0' => Yii::t('app', "ALL"),
                '1' => Yii::t('app', "present"),
                '2' => Yii::t('app', "absent")
            ],
        'value' => function ($model, $key, $index, $column) {
          $st_id = $model->finish_time ? 0 : 1;
          $st_name = $st_id ? Yii::t('app', "present") : Yii::t('app', "absent");
          $st_color = $st_id ? "bg-green" : "modernui-neutral-bg";
          return '<div class="center-color ' . $st_color . '">' . $st_name . '</div>';
        }
    ],
    [
        'attribute' => 'type',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '-1' => Yii::t('app', "ALL"),
            ],
            \frontend\modules\visits\models\VisitorLog::typeList()
        ),
        'value' => function ($model, $key, $index, $column) {
          return '<div class="center-color ' . VisitorLog::$colors[$model->type] . '">' . VisitorLog::typeList($model->type) . '</div>';
        }
    ],
    'comment',
    [
        'attribute' => 'pause',
        'value' => function ($model, $key, $index, $column) {
          $time = $model->pause;
          if (!$time || $time < 0) return "-";

          $s = $time % 60;
          $time = round(($time - $s) / 60);
          $m = ($time) % 60;
          $h = round(($time - $m) / 60);

          if ($m < 10) $m = '0' . $m;
          if ($s < 10) $s = '0' . $s;
          return $h . ':' . $m;
        }
    ],
  /*   [
       'attribute' => 'certificate_type',
       'filter'=>NumberRangerWidget::widget([
         'model'=>$searchModel,
         'attribute'=>'certificate_type',
       ])
     ],
     [
       'attribute' => 'certificate_val',
       'filter'=>NumberRangerWidget::widget([
         'model'=>$searchModel,
         'attribute'=>'certificate_val',
       ])
     ],*/
    [
        'attribute' => 'visit_cnt',
        'filter' => false,
        'enableSorting' => false,
    ],
  /* 'pay_man',
   [
     'attribute' => 'guest_m',
     'filter'=>NumberRangerWidget::widget([
       'model'=>$searchModel,
       'attribute'=>'guest_m',
     ])
   ],
   [
     'attribute' => 'guest_chi',
     'filter'=>NumberRangerWidget::widget([
       'model'=>$searchModel,
       'attribute'=>'guest_chi',
     ])
   ],
   'cnt_disk',
   [
     'attribute' => 'chi',
     'filter'=>NumberRangerWidget::widget([
       'model'=>$searchModel,
       'attribute'=>'chi',
     ])
   ],
   [
     'attribute' => 'sum_no_cert',
     'filter'=>NumberRangerWidget::widget([
       'model'=>$searchModel,
       'attribute'=>'sum_no_cert',
     ])
   ],
   [
     'attribute' => 'pre_enter',
     'filter'=>NumberRangerWidget::widget([
       'model'=>$searchModel,
       'attribute'=>'pre_enter',
     ])
   ],
   [
     'attribute' => 'kiosk_disc',
     'filter'=>NumberRangerWidget::widget([
       'model'=>$searchModel,
       'attribute'=>'kiosk_disc',
     ])
   ],
   [
     'attribute' => 'terminal_ans',
     'filter'=>NumberRangerWidget::widget([
       'model'=>$searchModel,
       'attribute'=>'terminal_ans',
     ])
   ],
   'certificate_number',*/
    [
        'attribute' => 'user_id',
        'filterType' => GridView::FILTER_SELECT2,
        'format' => 'raw',
        'filter' => ArrayHelper::merge(
            [
                '0' => Yii::t('app', "ALL"),
                '-1' => Yii::t('app', "nobody"),
            ],
            ArrayHelper::map((array)Yii::$app->cafe->getUsersList(), 'id', 'name')
        ),
        'value' => function ($model, $key, $index, $column) {
          $user = $model->user;
          return $user ? $user->name : '-';
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
            'view' => function ($url, $model) {
              /* @var $model VisitorLog */
              if ($model->finish_time) return "";
              return Html::a(
                  '<span class="glyphicon glyphicon-eye-open"></span> ' . Yii::t('app', 'View'),
                  ['/visits/view', 'id' => $model->id],
                  ['class' => 'btn btn-science-blue btn-xs', "role" => "modal-remote"]
              );
            },
            'stop' => function ($url, $model) {
              /* @var $model VisitorLog */
              if ($model->finish_time) return "";
              return Html::a(
                  '<i class="fa fa-stop"></i> ' . Yii::t('app', 'Stop'),
                  ['/visits/stop', 'id' => $model->id],
                  ['class' => 'btn btn-warning btn-xs', "role" => "modal-remote"]
              );
            },
            'check_print' => function ($url, $model) {
              /* @var $model VisitorLog */
              if (!$model->finish_time) return "";
              if ($model->canMakeCheck()) {
                return Html::button(
                    '<i class="fa fa-print"></i> ' . Yii::t('app', 'Print check'),
                    ['class' => 'btn btn-info btn-xs btn_print_check', "onClick" => 'app.print_check(' . $model->id . '); return false;']
                );
              }

              return '';
            },
            'check_mail' => function ($url, $model) {
              /* @var $model VisitorLog */
              if (!$model->finish_time) return "";
              if ($model->canMakeCheck() && $model->canSendMail()) {
                return Html::a(
                    '<i class="fa fa-envelope-o"></i> ' . Yii::t('app', 'Send mail'),
                    ['/visits/admin/check-send-mail', 'id' => $model->id],
                    ['class' => 'btn btn-info btn-xs', "role" => "modal-remote"]
                );
              }

              return '';
            },
            'certificate' => function ($url, $model) {
              /* @var $model VisitorLog */
              if ($model->certificate_type === Certificate::TYPE_NONE) {
                return Html::a('<i class="fa fa-plus"></i>&nbsp;' . Yii::t('app', 'Add Certif'), ['update-certificate', 'id' => $model->id], [
                    'class' => 'btn btn-success btn-xs',
                    'role' => 'modal-remote',
                ]);
              }

              return '';
            },

            'certificate_delete' => function ($url, $model) {
              /* @var $model VisitorLog */
              if ($model->certificate_type !== Certificate::TYPE_NONE) {
                return Html::a('<i class="fa fa-close"></i>&nbsp;' . Yii::t('app', 'Delete Certificate'), ['delete-certificate', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-xs',
                    'role' => 'modal-remote',
                    'data-confirm' => false, 'data-method' => false,// for overide yii data api
                    'data-request-method' => 'get',
                    'data-toggle' => 'tooltip',
                    'data-confirm-title' => Yii::t('app', 'Are you sure?'),
                    'data-confirm-message' => Yii::t('app', 'Are you sure want to delete certificate from visit'),
                ]);
              }

              return '';
            },
        ],
        'updateOptions' => [
            'role' => 'modal-remote',
            'title' => '',
            'label' => "<div class=\"btn btn-science-blue btn-xs admin\"><i class=\"fa fa-pencil\"></i> " . Yii::t('app', 'Edit data') . "</div>",
        ],
        'deleteOptions' => ['role' => 'modal-remote', 'title' => '',
            'label' => "<div class=\"btn btn-danger btn-xs admin\"><i class=\"glyphicon glyphicon-trash\"></i> " . Yii::t('app', 'Delete') . "</div>",
            'data-confirm' => false, 'data-method' => false,// for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => Yii::t('app', 'Are you sure?'),
            'data-confirm-message' => Yii::t('app', 'Are you sure want to delete this item'),]
    ],

];

