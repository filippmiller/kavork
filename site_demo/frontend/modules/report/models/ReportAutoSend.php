<?php

namespace frontend\modules\report\models;

use frontend\modules\cafe\models\Cafe;
use frontend\modules\shop\models\ShopTransaction;
use frontend\modules\users\models\UserLog;
use frontend\modules\visitor\models\Visitor;
use frontend\modules\visits\models\VisitorLog;
use Yii;

/**
 * This is the model class for table "report_auto_send".
 *
 * @property int $id
 * @property string $email
 * @property int $type
 * @property int $cafe_id
 * @property int $status
 *
 * @property Cafe $cafe
 */
class ReportAutoSend extends \common\components\ActiveRecord
{

  const TYPE_DAILY = 1;
  const TYPE_WEEKLY = 2;
  const TYPE_MONTHLY = 3;

  const PERIOD_TODAY = 0;
  const PERIOD_THIS_MONTH = 1;
  const PERIOD_THIS_WEEK = 2;
  const PERIOD_PREV_MONTH = 3;
  const PERIOD_YESTERDAY = 4;
  const PERIOD_PREV_WEEK = 5;

  const SOURCE_TOTAL_PAY = 0;
  const SOURCE_TOTAL_NOT_PAY = 1;
  const SOURCE_VISIT_BY_HOUR = 2;
  //const SOURCE_VISIT_BY_DAY = 3;
  const SOURCE_VISIT_NOT_PAY = 4;
  const SOURCE_ADMIN_TIME = 5;

  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'report_auto_send';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['type', 'cafe_id', 'status'], 'integer'],
        [['email'], 'email'],
        [['cafe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cafe::className(), 'targetAttribute' => ['cafe_id' => 'id']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'id' => Yii::t('app', 'ID'),
        'email' => Yii::t('app', 'Email'),
        'type' => Yii::t('app', 'Type'),
        'cafe_id' => Yii::t('app', 'Cafe ID'),
        'status' => Yii::t('app', 'Status'),
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCafe()
  {
    return $this->hasOne(Cafe::className(), ['id' => 'cafe_id']);
  }

  public static function getTypes($index = null)
  {
    $labels = [
        self::TYPE_DAILY => Yii::t('app', 'Everyday'),
        self::TYPE_WEEKLY => Yii::t('app', 'Weekly'),
        self::TYPE_MONTHLY => Yii::t('app', 'Monthly'),
    ];

    if ($index !== null) {
      return isset($labels[$index]) ? $labels[$index] : Yii::t('app', 'Unknown');
    } else {
      return $labels;
    }
  }

  public static function getPeriod($code, $first_week_day)
  {
    $t = mktime(0, 0, 0, date("n"), date("j") - 1, date("Y"));
    $month = date("n", $t);
    $day = date("j", $t);
    $year = date("Y", $t);
    $start_week = $day - (date('w', $t) - $first_week_day);
    if ($code == self::PERIOD_TODAY) {
      return [
          mktime(0, 0, 0, $month, $day, $year),
          mktime(23, 59, 59, $month, $day, $year),
      ];
    }
    if ($code == self::PERIOD_YESTERDAY) {
      return [
          mktime(0, 0, 0, $month, $day - 1, $year),
          mktime(23, 59, 59, $month, $day - 1, $year),
      ];
    }
    if ($code == self::PERIOD_THIS_MONTH) {
      return [
          mktime(0, 0, 0, $month, 1, $year),
          mktime(23, 59, 59, $month, $day, $year),
      ];
    }
    if ($code == self::PERIOD_THIS_WEEK) {
      return [
          mktime(0, 0, 0, $month, $start_week, $year),
          mktime(23, 59, 59, $month, $day, $year),
      ];
    }
    if ($code == self::PERIOD_PREV_MONTH) {
      return [
          mktime(0, 0, 0, $month - 1, 1, $year),
          mktime(23, 59, 59, $month, 0, $year),
      ];
    }
    if ($code == self::PERIOD_PREV_WEEK) {
      return [
          mktime(0, 0, 0, $month, $start_week - 7, $year),
          mktime(23, 59, 59, $month, $start_week - 1, $year),
      ];
    }
  }

  public static function getData($period, $date_source, $cafe_id)
  {
    //$period[0]=0;
    $start_data = date('Y-m-d', $period[0]);
    $end_data = date('Y-m-d', $period[1]);
    if (
        $date_source == self::SOURCE_TOTAL_PAY ||
        $date_source == self::SOURCE_TOTAL_NOT_PAY
    ) {
      $pay = ($date_source == self::SOURCE_TOTAL_PAY) ?
          [VisitorLog::PAY_METHOD_CASH, VisitorLog::PAY_METHOD_CARD] :
          [VisitorLog::PAY_METHOD_NOT_PAID];
      $data = VisitorLog::find()
          ->andWhere(['cafe_id' => $cafe_id])
          ->andWhere(['pay_state' => $pay])
          ->andWhere('DATE(finish_time)>=\'' . $start_data . '\'')
          ->andWhere('DATE(finish_time)<=\'' . $end_data . '\'')
          ->select([
              'count(id) as cnt',
              'sum(cost) as cost',
              'sum(`sum`) as `sum`',
              'sum(`guest_m`) as `guest_m`',
              'sum(`guest_chi`) as `guest_chi`',
              'sum(IF(type=' . VisitorLog::TYPE_NEW . ',1,0)) as `new_visitor`',
              'sum(IF(type=' . VisitorLog::TYPE_ANONYMOUS . ',1,0)) as `anonymus`',
              'sum(IF(type=' . VisitorLog::TYPE_REGULAR . ',1,0)) as `regular`',
              'sum(IF(pay_state=' . VisitorLog::PAY_METHOD_NOT_PAID . ',1,0)) as `not_pay_cnt`',
              'sum(IF(pay_state=' . VisitorLog::PAY_METHOD_CARD . ',1,0)) as `card_cnt`',
              'sum(IF(pay_state=' . VisitorLog::PAY_METHOD_CASH . ',1,0)) as `cash_cnt`',
              'sum(IF(pay_state=' . VisitorLog::PAY_METHOD_NOT_PAID . ',`sum`,0)) as `not_pay_sum`',
              'sum(IF(pay_state=' . VisitorLog::PAY_METHOD_CARD . ',`sum`,0)) as `card_sum`',
              'sum(IF(pay_state=' . VisitorLog::PAY_METHOD_CASH . ',`sum`,0)) as `cash_sum`',
              'sum(IF(pay_state=' . VisitorLog::PAY_METHOD_NOT_PAID . ',`cost`,0)) as `not_pay_cost`',
              'sum(IF(pay_state=' . VisitorLog::PAY_METHOD_CARD . ',`cost`,0)) as `card_cost`',
              'sum(IF(pay_state=' . VisitorLog::PAY_METHOD_CASH . ',`cost`,0)) as `cash_cost`',
          ])
          ->asArray()
          ->one();
		  if(
		  $date_source != self::SOURCE_TOTAL_NOT_PAY){
      $shop=ShopTransaction::find()
          ->andWhere(['cafe_id' => $cafe_id])
          ->andWhere(['operation_type_id' => ShopTransaction::OPERATION_TYPE_SALE])
		  ->andWhere('DATE(created_at)>=\'' . $start_data . '\'')
          ->andWhere('DATE(created_at)<=\'' . $end_data . '\'')
          ->select([
              'sum(cost) as shop_cost',
              'sum(`sum`) as `shop_sum`',
              'sum(`quantity`) as `shop_quantity`',
              ])
          ->asArray()
          ->one();
      $data = array_merge($data,$shop);	  
	  	  }
	  $data['total_cost']=$data['cost']+$data['shop_sum'];
      $data['total_sum']=$data['sum']+$data['shop_sum'];
      return $data;
    }
    if ($date_source == self::SOURCE_VISIT_BY_HOUR) {
      $group_by = 'DATE_FORMAT(`finish_time`, \'%H\' )';
      return VisitorLog::find()
          ->andWhere(['cafe_id' => $cafe_id])
          ->andWhere('DATE(finish_time)>=\'' . $start_data . '\'')
          ->andWhere('DATE(finish_time)<=\'' . $end_data . '\'')
          ->groupBy([$group_by])
          ->select([
              $group_by . ' as gr',
              'sum(`sum`) as `sum`',
              'sum(cost) as cost',
              'count(id) as cnt',
              'sum(guest_m) as guest_m',
              'sum(guest_chi) as guest_chi',
          ])
          ->asArray()
          ->all();
    }
    if ($date_source == self::SOURCE_VISIT_NOT_PAY) {
      return VisitorLog::find()
          ->andWhere(['cafe_id' => $cafe_id])
          ->leftJoin(Visitor::tableName(), 'visitor.id = visitor_log.visitor_id')
          ->andWhere(['pay_state' => VisitorLog::PAY_METHOD_NOT_PAID])
          ->andWhere('DATE(finish_time)>=\'' . $start_data . '\'')
          ->andWhere('DATE(finish_time)<=\'' . $end_data . '\'')
          ->select([
              'visitor_log.*',
              'visitor.*',
          ])
          ->asArray()
          ->all();
    }
    if ($date_source == self::SOURCE_ADMIN_TIME) {
      return UserLog::find()
          ->andWhere(['cafe_id' => $cafe_id])
          ->andWhere('DATE(finish)>=\'' . $start_data . '\'')
          ->andWhere('DATE(finish)<=\'' . $end_data . '\'')
          ->leftJoin('user', 'user.id=user_log.user_id')
          ->groupBy('user_id')
          ->select(['user_id', 'user.name', 'sum(UNIX_TIMESTAMP(finish)-UNIX_TIMESTAMP(start)) as duration'])
          ->asArray()
          ->all();
    }
  }

  public static function getArrayReport($type)
  {
    $data = [
        self::TYPE_DAILY => [
            [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Day')]
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report per Day')]
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment per Day')]
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Session Admins per Day')]
            ],

            [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Full Week')]
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report per Full Week')]
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment per Full Week')]
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Admins per Full Week')]
            ],

            [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Current Month')]
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report Current Month')]
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment Current Month')]
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Admins per Current Month')]
            ],

            [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Prev Month')]
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report per Prev Month')]
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment per Prev Month')]
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Admins per Prev Month')]
            ],
        ],
        self::TYPE_WEEKLY => [
            [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Day')]
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report per Day')]
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment per Day')]
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Session Admins per Day')]
            ],

            [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Full Week')]
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report per Full Week')]
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment per Full Week')]
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Admins per Full Week')]
            ],

            [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Current Month')]
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report Current Month')]
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment Current Month')]
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Admins per Current Month')]
            ],

            [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Prev Month')]
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report per Prev Month')]
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment per Prev Month')]
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Admins per Prev Month')]
            ],
        ],
        self::TYPE_MONTHLY => [
            [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Day')]
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report per Day')]
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment per Day')]
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_TODAY,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Session Admins per Day')]
            ],

            [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Full Week')]
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report per Full Week')]
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment per Full Week')]
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_PREV_WEEK,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Admins per Full Week')]
            ],

            [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Current Month')]
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report Current Month')]
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment Current Month')]
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_THIS_MONTH,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Admins per Current Month')]
            ],

            [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#7bad18']
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Prev Month')]
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_VISIT_BY_HOUR,
                'layout' => 'hour_graph_sum',
                'data' => ['title' => Yii::t('report', 'Hourly Report per Prev Month')]
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_line',
                'data' => ['color' => '#fa6800']
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_TOTAL_NOT_PAY,
                'layout' => 'sum_table',
                'data' => ['title' => Yii::t('report', 'Non-payment per Prev Month')]
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_VISIT_NOT_PAY,
                'layout' => 'table_not_pay',
                'data' => []
            ], [
                'period' => self::PERIOD_PREV_MONTH,
                'date_source' => self::SOURCE_ADMIN_TIME,
                'layout' => 'table_admin',
                'data' => ['title' => Yii::t('report', 'Admins per Prev Month')]
            ],
        ],
    ];

    if (!isset($data[$type])) return [];

    return $data[$type];
  }
}
