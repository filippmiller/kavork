<?php

namespace frontend\modules\report\controllers;

use app\helpers\GridHelper;
use frontend\components\Controller;
use frontend\models\Transaction;
use frontend\modules\shop\models\ShopTransaction;
use frontend\modules\users\models\UserLog;
use frontend\modules\visitor\models\Visitor;
use frontend\modules\visits\models\VisitorLog;
use miloschuman\highcharts\HighchartsAsset;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\JqueryAsset;
use yii\web\Response;


class AdminController extends Controller
{

  private $users = [];

  //private $colors = ['#006ac1', 'rgb(247, 163, 92)'];
  private $colors = ['rgb(19, 149, 214)', 'rgb(255, 187, 0)'];

  private function getLineGraph($title, $data, $hasDate = true, $type = 'datetime')
  {
    $tf = Yii::$app->params['lang']['time24Hour'] ? '%H:%M' : '%I:%M%p';

    if ($type == 'datetime') {

      $xAxis = [
          'type' => $type,
          'crosshair' => true,
          'dateTimeLabelFormats' => [
              'day' => '%e %b',
              'minute' => $tf,
              'hour' => $tf,
          ]
      ];
    } else {
      $xAxis = [];
      foreach ($data[0] as $d) {
        $xAxis[] = $d[0];
      }
      $xAxis = [
          'type' => 'area',
          'categories' => $xAxis,
          'crosshair' => true,
      ];
    }
    $series = [[
        'type' => 'area',
        'name' => Yii::t('report', 'Visits'),
        'data' => $data[0]
    ]];
    if (isset($data[1])) {
      $series[] = [
          'name' => Yii::t('report', 'Shop'),
          'type' => 'area',
          'data' => $data[1]
      ];
    }

    return [
        'title' => ['text' => $title,],
      //'font' => 'Open Sans',
      //'colors' => ['#3498DB'],
        'colors' => $this->colors,
        'credits' => ['enabled' => false],
        'chart' => ['height' => '200'],
        'xAxis' => $xAxis,
        'yAxis' => ['title' => ['text' => Yii::t('report', 'Sum')]],
        'legend' => ['enabled' => isset($data[1])],
        'tooltip' => [
            'xDateFormat' => $hasDate ? "%A, %b %e " . $tf : $tf,
            'pointFormat' => '{point.y:.2f} ' . Yii::$app->cafe->currency
        ],
      /*'plotOptions' => [
          'area' => [
              'fillColor' => [
                  'linearGradient' => [
                      'x1' => 0,
                      'y1' => 0,
                      'x2' => 0,
                      'y2' => 1
                  ],
                  'stops' => [
                      [0, 'rgba(23, 132, 222, 0.4);'],
                      [1, 'rgba(23, 132, 222, 0.4);']

                  ],
                  'marker' => ['radius' => 1],
                  'lineWidth' => 1,
                  'states' => ['hover' => ['lineWidth' => 1]],
                  'threshold' => null
              ]
          ]
      ],*/

        'series' => $series,
    ];
  }

  private function getLineCategory($title, $data)
  {
    $series = [[
      //'type' => 'area',
        'name' => Yii::t('report', 'Visits'),
        'data' => $data[0]
    ]];
    if (isset($data[1])) {
      $series[] = [
          'name' => Yii::t('report', 'Shop'),
        //'type' => 'area',
          'data' => $data[1]
      ];
    }
    return [
        'title' => ['text' => $title,],
        'colors' => $this->colors,
        'credits' => ['enabled' => false],
        'chart' => ['type' => 'column', 'height' => '200'],
      //'chart' => ['zoomType'=> 'x'],
      //'xAxis' => ['categories' => $categories],
        'xAxis' => ['type' => 'category', 'crosshair' => true,],
        'yAxis' => [
            'title' => ['text' => Yii::t('app', 'Sum')],
            'min' => 0,
        ],
        'legend' => ['enabled' => isset($data[1])],
        'tooltip' => [
            'pointFormat' => '{point.y:.2f} ' . Yii::$app->cafe->currency
        ],
      /***'plotOptions' => [
       * 'area' => [
       * 'fillColor' => [
       * 'linearGradient' => [
       * 'x1' => 0,
       * 'y1' => 0,
       * 'x2' => 0,
       * 'y2' => 1
       * ],
       * 'stops' => [
       * [0, '#7cb5ec'],
       * [1, '#FFFFFF']
       * ],
       * 'marker' => ['radius' => 1],
       * 'lineWidth' => 1,
       * 'states' => ['hover' => ['lineWidth' => 1]],
       * 'threshold' => null
       * ]
       * ]
       * ],*/
        'series' => $series
    ];
  }

  private function getChart($title, $data)
  {
    return [
        'title' => ['text' => $title,],
      //'legend' => ['enabled' => false],
        'credits' => ['enabled' => false],
        //'colors' => ['#3498DB', '#7bad18', '#fa6800', '#4617b4', '#f0a30a', '#006ac1', '#87794e', '#e51400', '#dc4fad', '#c1004f', '#7200ac'],
		'colors' => ['#3498DB', '#7bad18', '#ff2e12', '#FBBB0F', '#006ac1', '#87794e', '#647687', '#76608a', '#dc4fad', '#c1004f', '#7200ac'],
        'chart' => [
            'type' => 'pie',
            'height' => '400',
            'plotBackgroundColor' => null,
            'plotBorderWidth' => null,
            'plotShadow' => false,
        ],
        'tooltip' => [
            'useHTML' => true,
            'backgroundColor' => 'rgba(0,0,0,0.8)',
            'borderColor' => 'rgba(0,0,0,0.8)',
            'borderRadius' => 0,
            'borderWidth' => 1,
            'headerFormat' => '<span style="font-size:12px;color:#ffffff;">{point.key}</span></br>',
            'pointFormat' => '<span style="font-size:18px;color:#ffffff;">{series.name}: <span style="font-weight:600;">{point.percentage:.2f}%</span></span>',
        ],
        'plotOptions' => [
            'pie' => [
                'allowPointSelect' => false,
                'cursor' => 'pointer',
                'dataLabels' => [
                    'enabled' => true,
                    'format' => '<b>{point.name}</b>: {point.percentage:.2f} %',
                    'style' => [
                        'color' => '#000000'
                    ],
                    'connectorColor' => 'silver'
                ],
                'point' => ['events' => [
                    'mouseOver' => new \yii\web\JsExpression('ChartMouseOver'),
                    'mouseOut' => new \yii\web\JsExpression('ChartMouseOut'),
                ]]

            ]
        ],
        'series' => [[
          //'type' => 'category',
            'name' => 'Duration',
            'data' => $data,
        ]]
    ];
  }

  public function actions()
  {
    if (Yii::$app->user->isGuest || !Yii::$app->cafe->can('ReportView')) {
      throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
    }

    $lang_hc = [
        'months' => ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        'weekdays' => ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
        'contextButtonTitle' => "Chart context menu",
        'downloadJPEG' => "Download JPEG image",
        'downloadPDF' => "Download PDF document",
        'downloadPNG' => "Download PNG image",
        'downloadSVG' => "Download SVG vector image",
        'printChart' => "Print chart",
        'rangeSelectorFrom' => "From",
        'rangeSelectorTo' => "To",
        'rangeSelectorZoom' => "Zoom",
        'resetZoom' => "Reset zoom",
        'resetZoomTitle' => "Reset zoom level 1:1",
        'shortMonths' => ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    ];

    foreach ($lang_hc as $k => $lang) {
      if (is_array($lang)) {
        foreach ($lang as &$lang2) {
          $lang2 = Yii::t('report', $lang2);
        }
        $lang_hc[$k] = $lang;
      } else {
        $lang_hc[$k] = Yii::t('report', $lang);
      }
    }

    Yii::$app->view->registerJs('Highcharts.setOptions({lang:' . json_encode($lang_hc) . '})');
    HighchartsAsset::register(Yii::$app->view)->withScripts(['highstock', 'modules/exporting', 'modules/drilldown']);
    return parent::actions(); // TODO: Change the autogenerated stub
  }

  public function actionIndex()
  {

    if (!Yii::$app->user->can('ReportView')) {
      throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
    }

    $this->view->registerJsFile('/js/report.js', ['depends' => [JqueryAsset::className()]]);
    return $this->render('index', ['title' => Yii::t('report', 'Report')]);
  }

  public function actionGet()
  {
    $request = Yii::$app->request;

    if (!$request->isPost || !$request->isAjax) {
      throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
    }

    $date_range = explode(' - ', $request->post('datetime_range'));
    foreach ($date_range as &$d) {
      $d = GridHelper::getDbDateFromDateRangeFormat($d, null, false);
    }
    $where = [
        'date_range' => $date_range,
    ];

    //ddd(Yii::$app->params);
    if ($request->post('tt') == 1) {
      $where['time_range'] = [
          $request->post('begin_time'),
          $request->post('end_time'),
      ];

      $format = Yii::$app->params['lang']['time'];
      $format = str_replace(':s', '', $format);
      foreach ($where['time_range'] as &$t) {
        $t = \DateTime::createFromFormat($format, $t);
        $t = $t->getTimestamp();
      }
    }

    $this->addGraph($where, $request->post('datetime_range'));

    if ($request->post('type') == 1) {
      return $this->getSummary($where);
    }

    if ($request->post('type') == 2) {
      return $this->getDayly($where);
    }

    if ($request->post('type') == 3) {
      return $this->getWeek($where);
    }

    if ($request->post('type') == 4) {
      return $this->getMonthly($where);
    }

    if ($request->post('type') == 5) {
      return $this->getDuration($where);
    }
    return "В работе";
  }

  public function actionTransactions()
  {
    if (!Yii::$app->user->can('TransactionView')) {
      throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
    }
    $request = Yii::$app->request;

    if ($request->isPost || !Yii::$app->cafe->can('TransactionsView')) {
      if (class_exists('yii\debug\Module')) {
        Yii::$app->getModule('debug')->instance->allowedIPs = [];
        $this->off(\yii\web\View::EVENT_END_BODY, [\yii\debug\Module::getInstance(), 'renderToolbar']);
      }

      $date_range = explode(' - ', $request->post('datetime_range'));
      foreach ($date_range as &$d) {
        $d = GridHelper::getDbDateFromDateRangeFormat($d, null, false);
      }

      $cafe_id = Yii::$app->cafe->id;

      $pay_state_arr = [
          '',
          '>0',
          '=' . VisitorLog::PAY_METHOD_CASH,
          '=' . VisitorLog::PAY_METHOD_CARD,
      ];
      $pay_state = 'method' . $pay_state_arr[$request->post('type')];


      $result = Transaction::find()
          ->andWhere(['cafe_id' => $cafe_id])
          ->andWhere($pay_state)
          ->andWhere('DATE(created_at)>=\'' . $date_range[0] . '\'')
          ->andWhere('DATE(created_at)<=\'' . $date_range[1] . '\'')
          ->andWhere('cost>0')
          ->asArray();

      if (!empty($request->post('source'))) {
        if ($request->post('source') == 2) {
          $result->andWhere([
              'sale_id' => null
          ]);
        } elseif ($request->post('source') == 3) {
          $result->andWhere([
              'visit_id' => null
          ]);
        }
      }

      $total = clone $result;
      $total->select(['sum' => 'sum(sum)', 'count' => 'count(id)', 'cost' => 'sum(cost)']);

      $result->orderBy([
          'DATE(created_at)' => SORT_DESC,
          'created_at' => SORT_ASC,
      ])
          ->select([
              'id',
              'visitor_id',
              'finish' => 'created_at',
              'pay_state' => 'method',
              'sum' => 'sum(sum)',
              'cost' => 'sum(cost)',
              'IF(sale_id is null ,\'visit\',\'shop\') as source',
          ])
          ->groupBy([
              'method',
              'created_at',
              'if(pay_man,pay_man,-id)',
            //'IF(sale_id is null ,\'visit\',\'shop\')'
          ]);

      $data = [
          'result' => $result->all(),
          'controller' => $this,
          'currency' => Yii::$app->cafe->currency,
          'source' => $request->post('source')
      ];

      if (!$request->isAjax) {
        Yii::$app->response->setDownloadHeaders('transactions.csv');
        return strip_tags($this->renderPartial('csv', $data));
      }

      $data['total'] = $total->one();
      $data['total']['count'] = count($data['result']);
      //ddd($data);
      return $this->renderAjax('transactions_view', $data);
    }

    $this->view->registerJsFile('/js/report.js', ['depends' => [JqueryAsset::className()]]);
    $this->view->registerJs('getTransaction();');
    return $this->render('transactions', [
        'title' => Yii::t('app', 'Transaction list'),
    ]);
  }

  public function actionView($id, $source = 0)
  {
    $request = Yii::$app->request;
    if (!$request->isAjax && !YII_DEBUG) {
      throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    $model = Transaction::find()
        ->where(['id' => $id])
        ->one();

    if (!$model) {
      throw new ForbiddenHttpException(Yii::t('app', 'Page does not exist'));
      return false;
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    $models = Transaction::find()
        ->where([
            'method' => $model->method,
            'cafe_id' => $model->cafe_id,
            'created_at' => $model->created_at,
          //$model->sale_id?'visit_id':'sale_id'=>null
        ]);

    if ($source == 2) {
      $models->andWhere([
          'sale_id' => null
      ]);
    } elseif ($source == 3) {
      $models->andWhere([
          'visit_id' => null
      ]);
    }

    if ($model->pay_man) {
      $models->andWhere([
          'pay_man' => $model->pay_man,
      ]);
    } else {
      $models->andWhere([
          'id' => $model->id,
      ]);
    }

    $total = clone $models;
    $total = $total
        ->select([
            'sum' => 'sum(sum)',
            'count' => 'count(id)',
            'cost' => 'sum(cost)'
        ])
        ->asArray()
        ->one();

    $models = $models->all();

    //ddd($model,$models,$total);

    $footer = Html::button(Yii::t('app', 'Close'), [
        'class' => 'btn btn-default pull-left',
        'data-dismiss' => "modal",
    ]);

    if ($total['count'] == 1) {
      $link = $model->sale_id ?
          "/shop/report/view?id=" . $model->sale_id :
          "/visits/default/view?id=" . $model->visit_id;
      $footer .= Html::a(
          '<i class="fa fa-search"></i> ' .Yii::t('report', 'Detail'). '',
          $link,
          [
              'role' => "modal-new",
              'class' => "btn btn-info"
          ]
      );
    }

    return [
        'title' => Yii::t('app', 'Transaction detail'),
        'content' => $this->renderAjax('view', [
            'model' => $model,
            'items' => $models,
            'total' => $total,
            'cafe' => Yii::$app->cafe->get(),
        ]),
        'footer' => $footer,
    ];
  }

  private function getAdmin($id)
  {

    return '-';
  }


  public function getUser($id = false)
  {
    if (!$id || empty($id)) {
      return Yii::t('app', 'Anonymous');
    }

    if (!isset($this->users[$id])) {
      $user = Visitor::find()->where(['id' => $id])->one();
      $this->users[$id] = !$user ? Yii::t('app', 'Anonymous') : $user->f_name . ' ' . $user->l_name;
    }
    return $this->users[$id];
  }

  private function getSummary($w)
  {
    $data = [
        'currency' => Yii::$app->cafe->currency,
        'sum_table' => [
            'pay' => [],
            'not_pay' => [],
        ]
    ];

    $pay = ArrayHelper::index($this->getSumBy(ArrayHelper::merge($w, ['pay' => true]), 'cafe_id'), 'gr');
    $not_pay = ArrayHelper::index($this->getSumBy(ArrayHelper::merge($w, ['pay' => false]), 'cafe_id'), 'gr');;
    if (Yii::$app->cafe->can('shopAll')) {
      $shop = ArrayHelper::index($this->getSumByShop($w, 'cafe_id'), 'gr');;
    } else {
      $shop = false;
    }

    $this->payLine(Yii::$app->cafe->id, $data['sum_table'], $pay, $not_pay, $shop);

    $data['not_pay_user'] = $this->getSumBy(ArrayHelper::merge($w, ['pay' => false]));

    $data['admins'] = UserLog::find()
        ->andWhere(['cafe_id' => Yii::$app->cafe->id])
        ->andWhere('DATE(finish)>=\'' . $w['date_range'][0] . '\'')
        ->andWhere('DATE(finish)<=\'' . $w['date_range'][1] . '\'')
        ->leftJoin('user', 'user.id=user_log.user_id')
        ->groupBy('user_id')
        ->select(['user_id', 'user.name', 'sum(UNIX_TIMESTAMP(finish)-UNIX_TIMESTAMP(start)) as duration'])
        ->asArray()
        ->all();
    $data['isSummary'] = true;
    return $this->renderAjax('summary', $data);
  }

  private function getDayly($w)
  {
    $data = [
        'currency' => Yii::$app->cafe->currency,
        'sum_table' => [
            'pay' => [],
            'not_pay' => [],
        ]
    ];

    $pay = ArrayHelper::index($this->getSumBy(ArrayHelper::merge($w, ['pay' => true]), 'DATE(finish_time)'), 'gr');
    $not_pay = ArrayHelper::index($this->getSumBy(ArrayHelper::merge($w, ['pay' => false]), 'DATE(finish_time)'), 'gr');;
    if (Yii::$app->cafe->can('shopAll')) {
      $shop = ArrayHelper::index($this->getSumByShop($w, 'DATE(created_at)'), 'gr');;
    } else {
      $shop = false;
    }

    $start_data = \DateTime::createFromFormat('Y-m-d', $w['date_range'][0])->getTimestamp();
    $end_data = \DateTime::createFromFormat('Y-m-d', $w['date_range'][1])->getTimestamp();

    $title_date_format = Yii::$app->params['lang']['date'];
    for ($d = $start_data; $d <= $end_data; $d += 60 * 60 * 24) {
      $this->payLine(date('Y-m-d', $d), $data['sum_table'], $pay, $not_pay, $shop, date($title_date_format, $d));
    }
    return $this->renderAjax('summary', $data);
  }


  private function getWeek($w)
  {
    $data = [
        'currency' => Yii::$app->cafe->currency,
        'sum_table' => [
            'pay' => [],
            'not_pay' => [],
        ]
    ];

    $start_data = \DateTime::createFromFormat('Y-m-d', $w['date_range'][0])->setTime(0, 0, 0)->getTimestamp();
    $end_data = \DateTime::createFromFormat('Y-m-d', $w['date_range'][1])->getTimestamp();

    $dd = (Yii::$app->cafe->params['first_weekday'] == 0) ? '%V%X' : '%v%x';
    $dd = 'DATE_FORMAT(finish_time,\'' . $dd . '\')';

    $pay = ArrayHelper::index($this->getSumBy(ArrayHelper::merge($w, ['pay' => true]), $dd), 'gr');
    $not_pay = ArrayHelper::index($this->getSumBy(ArrayHelper::merge($w, ['pay' => false]), $dd), 'gr');
    if (Yii::$app->cafe->can('shopAll')) {
      $shop = $this->getSumByShop($w, str_replace('finish_time', 'created_at', $dd));
      $shop = ArrayHelper::index($shop, 'gr');
    } else {
      $shop = false;
    }

    $title_date_format = Yii::$app->params['lang']['date'];
    $dd = (-7 + (Yii::$app->cafe->params['first_weekday'] == 0 ? 0 : 1)) * 24 * 60 * 60;
    $fd = date('w', $start_data) - Yii::$app->cafe->params['first_weekday'];
    for ($d = $start_data; $d - $fd * 60 * 60 * 24 <= $end_data; $d += 60 * 60 * 24 * 7) {
      $d1 = $d - $fd * 60 * 60 * 24;
      $d2 = $d1 + 60 * 60 * 24 * 7 - 1;

      if ($d1 < $start_data) $d1 = $start_data;
      if ($d2 > $end_data) $d2 = $end_data;

      $title = date($title_date_format, $d1) . ' - ' . date($title_date_format, $d2);
      $this->payLine(date('Wo', $d + $dd), $data['sum_table'], $pay, $not_pay, $shop, $title);
    }
    return $this->renderAjax('summary', $data);
  }

  private function getMonthly($w)
  {
    $data = [
        'currency' => Yii::$app->cafe->currency,
        'sum_table' => [
            'pay' => [],
            'not_pay' => [],
        ]
    ];

    $start_data = \DateTime::createFromFormat('Y-m-d', $w['date_range'][0])->setTime(0, 0, 0)->getTimestamp();
    $end_data = \DateTime::createFromFormat('Y-m-d', $w['date_range'][1])->getTimestamp();

    $dd = 'DATE_FORMAT(finish_time,\'%m%Y\')';

    $pay = ArrayHelper::index($this->getSumBy(ArrayHelper::merge($w, ['pay' => true]), $dd), 'gr');
    $not_pay = ArrayHelper::index($this->getSumBy(ArrayHelper::merge($w, ['pay' => false]), $dd), 'gr');;
    if (Yii::$app->cafe->can('shopAll')) {
      $shop = $this->getSumByShop($w, str_replace('finish_time', 'created_at', $dd));
      $shop = ArrayHelper::index($shop, 'gr');
    } else {
      $shop = false;
    }

    $title_date_format = Yii::$app->params['lang']['date'];

    $m = date('n', $start_data);
    $y = date('Y', $start_data);
    for (; mktime(0, 0, 0, $m, 1, $y) <= $end_data; $m++) {
      $d1 = mktime(0, 0, 0, $m, 1, $y);
      $d2 = mktime(0, 0, 0, $m + 1, 0, $y);

      if ($d1 < $start_data) $d1 = $start_data;
      if ($d2 > $end_data) $d2 = $end_data;

      $title = date($title_date_format, $d1) . ' - ' . date($title_date_format, $d2);
      $this->payLine(date('mY', $d1), $data['sum_table'], $pay, $not_pay, $shop, $title);
    }
    return $this->renderAjax('summary', $data);
  }

  private function getDuration($w)
  {

    $max_range = 15; //максимальная длина диапазона
    $dd = 'LEAST(' . $max_range . ',FLOOR((UNIX_TIMESTAMP(finish_time)-UNIX_TIMESTAMP(add_time)-pause)/3600))';

    $pay_db = ArrayHelper::index($this->getSumBy(ArrayHelper::merge($w, ['pay' => true]), $dd), 'gr');

    $total = 0;
    $pay = [];
    foreach ($pay_db as $item) {
      if ($item['gr'] < 0) continue;
      $total += $item['cnt'];
      $item['to'] = $item['gr'] < $max_range ? $item['gr'] + 1 : Yii::t('report', 'More');
      $item['label'] = $item['gr'] . ' - ' . $item['to'];
      $pay[] = $item;
    }

    $data = [
        'currency' => Yii::$app->cafe->currency,
        'sum_table' => [
            'pay' => $pay,
            'total' => $total
        ]
    ];

    $char = [];
    foreach ($data['sum_table']['pay'] as &$item) {
      $item['val'] = round($item['cnt'] / $data['sum_table']['total'] * 100, 2);
      $char[] = [
          'name' => $item['label'],
          'y' => $item['cnt'] * 1,
          'id' => $item['gr'],
      ];
    }
    $this->addCharsetJS('charts-duration', $this->getChart('', $char));
    $this->view->registerJs('duration_init();');

    //ddd($data['sum_table']);
    return $this->renderAjax('duration', $data);
  }

  private function payLine($code, &$out, $pay, $not_pay = false, $shop = false, $title = false)
  {
    $base = [
        'sum' => 0,
        'cost' => 0,
        'cnt' => 0,
        'guest_m' => 0,
        'guest_chi' => 0,
        'tax' => 0
    ];


    $pay = !empty($pay[$code]) ? $pay[$code] : $base;
    $pay['gr'] = $title ? $title : $code;
    $out['pay'][] = $pay;

    if (!isset($out['total'])) $out['total'] = 0;
    $out['total'] += $pay['cnt'];

    if ($not_pay !== false) {
      $not_pay = !empty($not_pay[$code]) ? $not_pay[$code] : $base;
      $not_pay['gr'] = $title ? $title : $code;
      $out['not_pay'][] = $not_pay;
    }

    if ($shop !== false) {
      $shop = !empty($shop[$code]) ? $shop[$code] : $base;
      $shop['gr'] = $title ? $title : $code;
      $out['shop'][] = $shop;
    }
  }

  private function addGraph($w, $title)
  {
    $w['pay'] = true;

    $data = [];
    $data['bayDay'] = $this->getSumBy($w, 'DATE(finish_time)');
    $data['bayHour'] = $this->getSumBy($w, 'DATE_FORMAT(`finish_time`, \'%H\' )');
    $data['bayWeekday'] = $this->getSumBy($w, 'DATE_FORMAT(`finish_time`, \'%w\' )');

    if (Yii::$app->cafe->can('shopAll')) {
      $data_shop = [];
      $data_shop['bayDay'] = $this->getSumByShop($w, 'DATE(`created_at`)');
      $data_shop['bayHour'] = $this->getSumByShop($w, 'DATE_FORMAT(`created_at`, \'%H\' )');
      $data_shop['bayWeekday'] = $this->getSumByShop($w, 'DATE_FORMAT(`created_at`, \'%w\' )');
    }
//https://github.com/miloschuman/yii2-highcharts
    //https://www.highcharts.com/demo/line-basic
    //$this->view->registerJs('new Highcharts.chart("t1",{"title":{"text":"Fruit Consumption"},"xAxis":{"categories":["Apples","Bananas","Oranges"]},"yAxis":{"title":{"text":"Fruit eaten"}},"series":[{"name":"Jane","data":[1,0,4]},{"name":"John","data":[5,7,3]}]});');


    $start_data = \DateTime::createFromFormat('Y-m-d  H:i:s', $w['date_range'][0] . ' 00:00:00')->getTimestamp();
    $end_data = \DateTime::createFromFormat('Y-m-d  H:i:s', $w['date_range'][1] . ' 00:00:00')->getTimestamp();

    $char = [];
    $char_data = ArrayHelper::map($data['bayHour'], 'gr', 'cost');
    $d = mktime(0, 0, 0, 0);
    for ($i = 0; $i <= 23; $i += 1) {
      //$d = date('Y-m-d', $i);
      $j = $i;
      if ($i < 10) $j = '0' . $j;
      if (!isset($char_data[$j])) {
        $value = 0;
      } else {
        $value = $char_data[$j];
      }
      $char[] = [date(Yii::$app->params['lang']['time_char'], $d + $i * 60 * 60), $value];
    }
    $char = [$char];

    if (Yii::$app->cafe->can('shopAll')) {
      $char[1] = [];
      $char_data = ArrayHelper::map($data_shop['bayHour'], 'gr', 'cost');
      for ($i = 0; $i <= 23; $i += 1) {
        //$d = date('Y-m-d', $i);
        $j = $i;
        if ($i < 10) $j = '0' . $j;
        if (!isset($char_data[$j])) {
          $value = 0;
        } else {
          $value = $char_data[$j];
        }
        $char[1][] = [date(Yii::$app->params['lang']['time_char'], $d + $i * 60 * 60), $value];
      }
    }
    $this->addCharsetJS('bayHour', $this->getLineGraph(Yii::t('report', 'Time of day'), $char, true, ''));

    if (date('Y-m-d', $start_data) == date('Y-m-d', $end_data)) return;

    $char = [];
    $char_data = ArrayHelper::map($data['bayDay'], 'gr', 'cost');
    $dt = -mktime(0, 0, 0) % (24 * 60 * 60);

    for ($i = $start_data; $i <= $end_data; $i += 24 * 60 * 60) {
      $d = date('Y-m-d', $i);
      if (!isset($char_data[$d])) {
        $value = 0;
      } else {
        $value = $char_data[$d];
      }
      $char[] = [($i + $dt) * 1000, $value];
    }
    $char = [$char];

    if (Yii::$app->cafe->can('shopAll')) {
      $char[1] = [];
      $char_data = ArrayHelper::map($data_shop['bayDay'], 'gr', 'cost');
      for ($i = $start_data; $i <= $end_data; $i += 24 * 60 * 60) {
        $d = date('Y-m-d', $i);
        if (!isset($char_data[$d])) {
          $value = 0;
        } else {
          $value = $char_data[$d];
        }
        $char[1][] = [($i + $dt) * 1000, $value];
      }
    }
    $this->addCharsetJS('bayDay', $this->getLineGraph($title, $char));

    //дальше в цикле проставляем переводы
    $weakDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    foreach ($weakDays as &$w) {
      $w = Yii::t('report', $w);
    };

    $weakDaysList = [];
    $char = [];
    $char_data = ArrayHelper::map($data['bayWeekday'], 'gr', 'cost');
    for ($i = 0; $i < 7; $i += 1) {
      $k = ($i + Yii::$app->cafe->params['first_weekday']) % 7;

      if (!isset($char_data[$k])) {
        $value = 0;
      } else {
        $value = $char_data[$k];
      }

      $weakDaysList[] = $weakDays[$k];
      $char[] = [$weakDays[$k], $value];
    }
    $char = [$char];

    if (Yii::$app->cafe->can('shopAll')) {
      $char_data = ArrayHelper::map($data_shop['bayWeekday'], 'gr', 'cost');
      for ($i = 0; $i < 7; $i += 1) {
        $k = ($i + Yii::$app->cafe->params['first_weekday']) % 7;

        if (!isset($char_data[$k])) {
          $value = 0;
        } else {
          $value = $char_data[$k];
        }

        $weakDaysList[] = $weakDays[$k];
        $char[1][] = [$weakDays[$k], $value];
      }
    }
    $this->addCharsetJS('bayWeekday', $this->getLineCategory(Yii::t('report', 'Day of week'), $char));
  }

  public function addCharsetJS($id, $params)
  {
    $this->view->registerJs('new Highcharts.chart("' . $id . '",' . Json::encode($params) . ');');
  }

  private function getSumBy($w, $group_by = false, $model = false, $time_col = 'finish_time', $dop_select = true)
  {
    if ($model == false) {
      $model = VisitorLog::className();
    }

    $visits = $model::find()
        ->andWhere(['cafe_id' => Yii::$app->cafe->id])
        ->andWhere('DATE(' . $time_col . ')>=\'' . $w['date_range'][0] . '\'')
        ->andWhere('DATE(' . $time_col . ')<=\'' . $w['date_range'][1] . '\'');

    if (!empty($w['time_range'])) {
      $visits->andWhere('TIME(' . $time_col . ')>=\'' . date('H:i:s', $w['time_range'][0]) . '\'');
      $visits->andWhere('TIME(' . $time_col . ')<=\'' . date('H:i:s', $w['time_range'][1]) . '\'');
    }

    if (isset($w['pay'])) {
      if ($w['pay'] === true) {
        $visits->andWhere('pay_state >0');
      } else if ($w['pay'] === false) {
        $visits->andWhere('pay_state <0');
      } else {
        $visits->andWhere('pay_state = ' . $w['pay']);
      }
    }

    if (isset($w['and'])) {
      $visits->andWhere($w['and']);
    }

    if ($group_by) {
      $visits->groupBy([$group_by]);
      $visits->asArray();
      $select = [
          $group_by . ' as gr',
          'sum(`sum`) as `sum`',
          'sum(cost) as cost',
          'count(id) as cnt',
      ];

      if ($dop_select === true) {
        $select[] = 'sum(guest_m) as guest_m';
        $select[] = 'sum(guest_chi) as guest_chi';
      } elseif ($dop_select == true) {
        $select += $dop_select;
      }
      $visits->select($select);
    }

    $visits = $visits->all();
    foreach ($visits as &$v) {
      $v['sum'] = round($v['sum'], 2);
      $v['cost'] = round($v['cost'], 2);
      $v['tax'] = round($v['cost'] - $v['sum'], 2);
      //$v['sum'] = number_format($v['sum'], 2, '.', '');
      // $v['cost'] = number_format($v['cost'], 2, '.', '');
    }
    return $visits;
  }

  private function getSumByShop($w, $group_by = false)
  {
    $time_col = 'created_at';
    $model = ShopTransaction::className();
    $w += ['and' => 'not(sale_id is null)'];
    unset($w['pay']);
    return $this->getSumBy($w, $group_by, $model, $time_col, false);
  }
}
