<?php

namespace frontend\modules\visits\models;

use common\components\VatHelper;
use frontend\models\Transaction;
use frontend\modules\cafe\models\Cafe;
use frontend\modules\cafe\models\CafeParams;
use frontend\modules\certificate\models\Certificate;
use frontend\modules\shop\models\ShopSale;
use frontend\modules\tariffs\models\Tariffs;
use frontend\modules\users\models\Users;
use frontend\modules\visitor\models\Visitor;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "visitor_log".
 *
 * @property int $id
 * @property int $user_id
 * @property int $visitor_id
 * @property int $type
 * @property int $cafe_id
 * @property string $add_time
 * @property string $comment
 * @property string $finish_time
 * @property double $cost
 * @property double $sum cost without vat
 * @property double $tip
 * @property double $tps
 * @property double $tvq
 * @property string $notice
 * @property int $pay_state
 * @property int $pause_start
 * @property int $pause
 * @property int $certificate_type
 * @property double $certificate_val
 * @property string $visit_cnt
 * @property string $pay_man
 * @property int $guest_m
 * @property int $guest_chi
 * @property string $cnt_disk
 * @property int $is_child
 * @property double $sum_no_cert
 * @property int $pre_enter
 * @property int $kiosk_disc
 * @property int $terminal_ans
 * @property string $certificate_number
 */
class VisitorLog extends \common\components\ActiveRecord
{
  const SCENARIO_ANONYMOUS_MAIL_SET = 'anonymous_email_set'; // Setting email to anonymous Visit

  const ACTION_CHANGE_USER = 'change-user';

  const TYPE_ANONYMOUS = 0;
  const TYPE_NEW = 1;
  const TYPE_REGULAR = 2;
  const TYPE_GROUP = 50;

  const PAY_METHOD_NOT_PAID = -1;
  const PAY_METHOD_CASH = 1;
  const PAY_METHOD_CARD = 2;
  const PAY_METHOD_MULI = 10;

  const UNGROUP_TYPE_MATURE = 1;
  const UNGROUP_TYPE_CHILD = 2;

  const CHECK_PRINT = 'checkPrint';
  const CHECK_MAIL = 'checkMail';

  const NOTICE_TYPE_CHILD_DISCOUNT = 'child_discount';
  const NOTICE_TYPE_DISCOUNTS = 'discounts';
  const NOTICE_TYPE_VISITOR_EMAIL = 'visitor_email';

  public $tax;

  public $display_visit_timing = true;
  public $unite_persons = 0;
  public $unite_cost = 0;
  public $unite_sum = 0;
  public $unite_prepay = 0;
  public $unite_vat = [];
  public $unite_people = [];
  public $unite_shop = 0;

  public $limit_guest_m = 50;
  public $limit_guest_chi = 50;

  public $ungroup_model;
  public $ungroup_type;

  public $certificate_time = '01:00';
  public $certificate_discount = '50';
  public $certificate_cash = '1';

  public $anonymous_email;

  public $send_mail_status = 0;

  private $_prepay = false;
  private $_prepayvisit = false;

  static $colors = [
      self::TYPE_ANONYMOUS => 'bg-science-blue',
      self::TYPE_NEW => 'bg-lima',
      self::TYPE_REGULAR => 'bg-regular',
      self::TYPE_GROUP => 'bg-amber',
  ];

  static $colors_payment = [
      self::PAY_METHOD_NOT_PAID => 'btn-danger',
      0 => '',
      self::PAY_METHOD_CASH => 'bg-tree-poppy',
      self::PAY_METHOD_CARD => 'bg-info',
      self::PAY_METHOD_MULI => 'bg-regular',
  ];

  static function typeList($type = false)
  {
    $type_list = [
        self::TYPE_NEW => Yii::t('app', "New user"),
        self::TYPE_REGULAR => Yii::t('app', "Regular"),
        self::TYPE_GROUP => Yii::t('app', "Group"),
    ];

    if (Yii::$app->cafe->can("AnonymousVisitor")) {
      $type_list[self::TYPE_ANONYMOUS] = Yii::t('app', "Anonymous");
    };

    ksort($type_list);

    if (!is_numeric($type)) return $type_list;
    return (isset($type_list[$type]) ? $type_list[$type] : false);
  }

  public static function payStatusList($type = false)
  {
    $type_list = array();

    if (Yii::$app->cafe->can('payNOT')) {
      $type_list[self::PAY_METHOD_NOT_PAID] = Yii::t('app', 'Not Paid');
    }
    $type_list[0] = '-';
    if (Yii::$app->cafe->can('payCash')) {
      $type_list[self::PAY_METHOD_CASH] = Yii::t('app', 'Cash');
    }
    if (Yii::$app->cafe->can('payCard')) {
      $type_list[self::PAY_METHOD_CARD] = Yii::t('app', 'Card');
    }
    $type_list[self::PAY_METHOD_MULI] = Yii::t('app', 'Multi');

    if (!is_numeric($type)) return $type_list;
    return (isset($type_list[$type]) ? $type_list[$type] : false);
  }

  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'visitor_log';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['guest_m', 'guest_chi'], 'default', 'value' => 0],
        [['cafe_id'], 'default', 'value' => Yii::$app->cafe->id],
        [['user_id', 'visitor_id', 'type', 'cafe_id', 'pay_state', 'pause_start', 'pause', 'certificate_type', 'guest_m', 'guest_chi', 'is_child', 'pre_enter', 'kiosk_disc', 'terminal_ans', 'certificate_discount', 'pay_man'], 'integer'],
        [['add_time', 'finish_time', 'vat'], 'safe'],
        [['comment', 'visit_cnt', 'cnt_disk', 'certificate_number', 'certificate_time'], 'string'],
        [['cost', 'sum', 'certificate_val', 'sum_no_cert', 'certificate_cash', 'tip'], 'number'],

        ['comment', 'required', 'when' => function ($model) {
          return $model->pay_state == self::PAY_METHOD_NOT_PAID;
        }],
        ['comment', 'string', 'min' => 10],

        ['certificate_number', 'string', 'min' => 6],
      /*[['certificate_number'], "required", 'when' => function ($model) {
        return $model->certificate_type > 0;
      }, 'whenClient' => "function (attribute, value) {
              return $('#visitorlog-certificate_type').val() > 0;
          }"],*/

        [['certificate_time'], "required", 'when' => function ($model) {
          return $model->certificate_type == Certificate::TYPE_FREE_TIME;
        }, 'whenClient' => "function (attribute, value) {
                return $('#visitorlog-certificate_type').val() == 2;
            }"],

        [['certificate_discount'], "required", 'when' => function ($model) {
          return $model->certificate_type == Certificate::TYPE_DISCOUNT_PERCENT;
        }, 'whenClient' => "function (attribute, value) {
                return $('#visitorlog-certificate_type').val() == 4;
            }"],

        [['certificate_cash'], "required", 'when' => function ($model) {
          return $model->certificate_type == Certificate::TYPE_DISCOUNT_CASH;
        }, 'whenClient' => "function (attribute, value) {
                return $('#visitorlog-certificate_type').val() == 5;
            }"],

        ['notice', 'safe'],

        ['anonymous_email', 'required', 'on' => self::SCENARIO_ANONYMOUS_MAIL_SET],
        ['anonymous_email', 'email', 'on' => self::SCENARIO_ANONYMOUS_MAIL_SET],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'id' => Yii::t('app', 'ID'),
        'user_id' => Yii::t('app', 'User ID'),
        'visitor_code' => Yii::t('app', 'Visitor (code)'),
        'visitor_email' => Yii::t('app', 'Visitor (email)'),
        'visitor_id' => Yii::t('app', 'Visitor (ID)'),
        'visitor' => Yii::t('app', 'Visitor'),
        'type' => Yii::t('app', 'Type'),
        'cafe_id' => Yii::t('app', 'Cafe ID'),
        'add_time' => Yii::t('app', 'Add Time'),
        'comment' => Yii::t('app', 'Comment'),
        'finish_time' => Yii::t('app', 'Finish Time'),
        'cost' => Yii::t('app', 'Cost'),
        'sum' => Yii::t('app', 'Sum'),
        'vat' => Yii::t('app', 'vat'),
        'notice' => Yii::t('app', 'Notice'),
        'pay_state' => Yii::t('app', 'Pay State'),
        'pause_start' => Yii::t('app', 'Pause Start'),
        'pause' => Yii::t('app', 'Pause'),
        'certificate_type' => Yii::t('app', 'Certificate Type'),
        'certificate_val' => Yii::t('app', 'Certificate Val'),
        'visit_cnt' => Yii::t('app', 'Visit Cnt'),
        'pay_man' => Yii::t('app', 'Pay Man'),
        'guest_m' => Yii::t('app', 'Guest M'),
        'guest_chi' => Yii::t('app', 'Guest Chi'),
        'cnt_disk' => Yii::t('app', 'Cnt Disk'),
        'is_child' => Yii::t('app', 'Is Child'),
        'sum_no_cert' => Yii::t('app', 'Sum No Cert'),
        'pre_enter' => Yii::t('app', 'Pre Enter'),
        'kiosk_disc' => Yii::t('app', 'Kiosk Disc'),
        'terminal_ans' => Yii::t('app', 'Terminal Ans'),
        'certificate_number' => Yii::t('app', 'Certificate Number'),
        'duration' => Yii::t('app', 'duration'),
        'status' => Yii::t('app', 'status'),
        'certificate_time' => Yii::t('app', 'certificate time'),
        'certificate_discount' => Yii::t('app', 'certificate discount'),
        'certificate_cash' => Yii::t('app', 'certificate cash'),

        'anonymous_email' => Yii::t('app', 'Email'),
    ];
  }

  public function getDuration()
  {
    // Handle invalid or null add_time
    $addTime = strtotime($this->add_time);
    if ($addTime === false || $addTime === null) {
      return 0;
    }

    $duration = ($this->finish_time ? strtotime($this->finish_time) : time());
    $duration -= $addTime;

    // Handle null pause value
    $duration -= ($this->pause ?? 0);


    if ($this->pause_start > 0) {
      $duration -= (time() - $this->pause_start);
    }

    // Ensure duration is not negative
    return max(0, $duration);
  }

  public function getCertificateTypeLabel()
  {
    $label = Certificate::getTypeLabels($this->certificate_type);

    switch ($this->certificate_type) {
      case Certificate::TYPE_FREE_TIME:
        $label = $label . ': ' . $this->certificate_val . Yii::t('app', ' minute(s)');
        break;
      case Certificate::TYPE_DISCOUNT_PERCENT:
        $label = $label . ': ' . $this->certificate_val . Yii::t('app', '%');
        break;
      case Certificate::TYPE_DISCOUNT_CASH:
        $label = $label . ': ' . $this->certificate_val . Yii::t('app', $this->cafe->currency);
        break;
    }

    return $label;
  }

  /**
   * Certificate type including Child Discount if applied
   */
  public function getCertificateTypeString()
  {
    $content = [];

    if (isset($this->notice[VisitorLog::NOTICE_TYPE_CHILD_DISCOUNT])) {
      $content[] = Yii::t('main', "Children's discount - {0}%", $this->notice[VisitorLog::NOTICE_TYPE_CHILD_DISCOUNT]);
    }

    if ($this->certificate_type != Certificate::TYPE_NONE) {
      $content[] = $this->getCertificateTypeLabel();
    }

    $discountInfo = $this->getDiscountsString();
    if (!empty($discountInfo)) {
      $content[] = $discountInfo;
    }

    return implode('<br>', $content);
  }

  public function afterFind()
  {
    if (!$this->finish_time) {
      $this->calcCost();
    } else {
      if (!is_array($this->vat)) {
        $this->vat = json_decode($this->vat, true);
      }
    }

    if (empty($this->notice)) {
      $this->notice = [];
    } else {
      if (!is_array($this->notice)) {
        $decoded_notice = json_decode($this->notice, true);
        $this->notice = is_array($decoded_notice) ? $decoded_notice : [];
      }

      if (isset($this->notice[VisitorLog::NOTICE_TYPE_CHILD_DISCOUNT])) {
        $val = $this->notice[self::NOTICE_TYPE_CHILD_DISCOUNT];
        $val = preg_replace("/[^0-9]/", '', $val) * 1;
        $notice = $this->notice;
        $notice[self::NOTICE_TYPE_CHILD_DISCOUNT] = $val;

        $this->notice = $notice;
      }
    }

    if (!$this->user_id && isset(Yii::$app->user)) {
      $this->user_id = Yii::$app->user->id;
    }

    parent::afterFind(); // TODO: Change the autogenerated stub
  }

  public function calcCost()
  {
    if (count(Yii::$app->cafe->tariff) == 0) {
      //ddd($this::className());
      $this->cost = false;
      $this->sum = Yii::t('app', 'No rates found for this cafe.');

      $flash = Yii::$app->session->allFlashes;
      if (!empty($flash['error'])) {
        $flash = $flash['error'];
        foreach ($flash as $msg) {
          if (strpos($this->sum, $msg) !== false) {
            $flash = false;
            break;
          }
        }
      }

      if (empty($flash)) {
        $msg = $this->sum;
        Yii::$app->session->addFlash('error', $msg);
      }
      if (Yii::$app->user->can('TariffsCreate')) {
        $msg .= '<br>' .
            Html::a(
                '<i class="glyphicon glyphicon-plus" ></i >' .
                Yii::t('app', 'Create new Tariffs'),
                '/tariffs/admin/create',
                [
                    'class' => 'btn btn-default not_main',
                    'role' => "modal-remote"]
            );
      }
      $this->sum = $msg;

      return false;
    }

    if ($this->visitor_id) {
      $visits = explode("/", $this->visit_cnt);
      $visits = $visits[count($visits) - 1];
    } else {
      $visits = 1;
    }

    foreach (Yii::$app->cafe->tariff as $tariff) {
      if ($visits >= $tariff['start_visit']) break;
    }

    if ($visits < $tariff['start_visit']) {
      $this->cost = false;
      $this->sum = Yii::t('app', 'No rates found for this cafe.');
      return false;
    }

    $duration = $this->duration / 3600;

    if ($this->certificate_type == Certificate::TYPE_FREE_TIME) {
      // Duration in hours - Free time in hours
      $duration = $duration - ($this->certificate_val / 60);
    }

    if ($this->certificate_type == Certificate::TYPE_FREE_VISIT || $duration <= 0) {
      $sum = 0;
    } else {
      if ($duration < 1) {
        $sum = $tariff['first_hour'] * $duration;
      } else {
        $sum = $tariff['first_hour'];
        $duration = $duration - 1; // We already added price for first hour

        $hourPrice = $tariff['first_hour'];
        $fullHours = ceil($duration);

        $tariffData = json_decode($tariff['data'], true);
        $tariffHourData = $tariffData[Tariffs::DATA_HOURS_KEY];

        if (!empty($tariffHourData)) {
          for ($i = 1; $i <= $fullHours; $i++) {
            $hourNumber = $i + 1;

            foreach ($tariffHourData as $hourData) {
              if ($hourNumber >= $hourData['hour']) {
                $hourPrice = $hourData['price'];
              }
            }

            if ($duration >= 1) {
              $sum = $sum + $hourPrice;
              $duration = $duration - 1;
            } else {
              $sum = $sum + ($duration * $hourPrice);
              $duration = 0;
            }
          }
        } else {
          $sum += $hourPrice * $duration;
        }
      }

      if ($sum < $tariff['min_sum']) {
        $sum = $tariff['min_sum'];
      } else if ($tariff['max_sum'] > 0 && $sum > $tariff['max_sum']) {
        $sum = $tariff['max_sum'];
      };

      if ($this->certificate_type == Certificate::TYPE_DISCOUNT_PERCENT) {
        // Percent. Example for 20%: 100 - (100 * 0.2) = 80
        $sum = $sum - ($sum * ($this->certificate_val / 100));
      } else if ($this->certificate_type == Certificate::TYPE_DISCOUNT_CASH) {
        $sum = $sum - $this->certificate_val;
      }

      if ($sum < 0) {
        // For cases than Certificate values more than u owe
        $sum = 0;
      }
    }

    $original_sum = $sum;

    $child_discount = Yii::$app->cafe->getChildDiscount();
    $child_discount_applied = false;

    // If Visitor is Child - Apply cafe Child Discount
    if ($sum > 0 && $this->is_child) {
      if ($child_discount) {
        $child_discount_applied = true;
        $sum -= $original_sum * ($child_discount / 100);
      }
    }

    if ($sum > 0 && $this->getIsGroup()) {
      if ($this->guest_m) {
        $single_mature_sum = $original_sum;
        for ($i = 1; $i <= $this->guest_m; $i++) {
          $sum += $single_mature_sum;
        }
      }

      if ($this->guest_chi) {
        $child_discount_applied = true;
        $single_child_sum = $original_sum;

        if ($child_discount) {
          $single_child_sum = $single_child_sum * ($child_discount / 100);
        }

        for ($i = 1; $i <= $this->guest_chi; $i++) {
          $sum += $single_child_sum;
        }
      }
    }

    $notice = $this->notice;
    /*if (!is_array($notice)) {
      $notice = json_decode($notice, true);
    }*/

    unset($notice[self::NOTICE_TYPE_CHILD_DISCOUNT]);

    if ($child_discount_applied) {
      $notice[self::NOTICE_TYPE_CHILD_DISCOUNT] = $child_discount;
    }

    // Discounts
    $discounts = self::findDiscounts($this);
    if ($discounts) {
      $notice[self::NOTICE_TYPE_DISCOUNTS] = $discounts;
      $discountPercentSum = array_sum(ArrayHelper::getColumn($discounts, 'value'));

      if ($discountPercentSum > 100) {
        $discountPercentSum = 100;
      }

      $sum = $sum - ($sum * ($discountPercentSum / 100));
    }

    list($sum, $cost, $vat) = VatHelper::calculate($sum);

    $this->notice = $notice;
    $this->vat = $vat;
    $this->cost = $cost;
    $this->sum = $sum;

    return true;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCafe()
  {
    return $this->hasOne(Cafe::className(), ['id' => 'cafe_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getVisitor()
  {
    return $this->hasOne(Visitor::className(), ['id' => 'visitor_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(Users::className(), ['id' => 'user_id']);
  }

  public function beforeValidate()
  {
    if(Yii::$app->request->post('VisitorLog')) {
      $post = Yii::$app->request->post('VisitorLog');
      if(isset($post['certificate_type'])) {
        switch ($this->certificate_type) {
          case Certificate::TYPE_FREE_TIME:
            // Converting time to minutes. Example: 01:30 = 90 minutes
            $time = explode(':', $this->certificate_time);
            $this->certificate_val = ($time[0] * 60) + ($time[1]);
            break;
          case Certificate::TYPE_DISCOUNT_PERCENT:
            $this->certificate_val = $this->certificate_discount;
            break;
          case Certificate::TYPE_DISCOUNT_CASH:
            $this->certificate_val = $this->certificate_cash;
            break;
        }
      }
    }

    if ($this->type == self::TYPE_ANONYMOUS) {
      // Force visitor_id to null
      $this->visitor_id = null;
    }

    return parent::beforeValidate(); // TODO: Change the autogenerated stub
  }

  public function beforeSave($insert)
  {
    if ($this->type === null) {
      $this->type = $this->visitor_id ? VisitorLog::TYPE_REGULAR : VisitorLog::TYPE_ANONYMOUS;
    }
    if ($this->isNewRecord && !$this->add_time) {
      $this->add_time = date("Y-m-d H:i:s");
    }

    if (!$this->isNewRecord && $this->oldAttributes['visitor_id'] != $this->visitor_id) {
      $this->visit_cnt = false;

      $sale = ShopSale::find()
          ->where([
              'visitor_log_id' => $this->id,
              'pay_state' => 0
          ])
          ->one();
      if ($sale) {
        $sale->visitor_id = $this->visitor_id;
        $sale->save();
      }
    }

    if ($this->visitor_id && !$this->visit_cnt) {
      $d30 = VisitorLog::find()->andWhere(['visitor_id' => $this->visitor_id])->andWhere(['>', 'add_time', date("Y-m-d H:i:s", time() - 30 * 24 * 60 * 50)])->count();
      $d7 = VisitorLog::find()->andWhere(['visitor_id' => $this->visitor_id])->andWhere(['>', 'add_time', date("Y-m-d H:i:s", time() - 7 * 24 * 60 * 50)])->count();
      $visitor = $this->visitor;
      $visitor->visit_cnt = $this->visitor->qty + 1;
      $visitor->save();

      $d = $visitor->visit_cnt;

      if ($d == 0 && $this->type == VisitorLog::TYPE_REGULAR) {
        $this->type = VisitorLog::TYPE_NEW;
      }

      $d7++;
      $d30++;

      $this->visit_cnt = $d7 . '/' . $d30 . '/' . $d;
    }

    if (!$this->finish_time) {
      unset($this->vat);
      unset($this->cost);
      unset($this->sum);
    } else if ($this->vat && is_string($this->vat)) {
      $this->vat = json_decode($this->vat, true);
    }

    if ($this->scenario == self::SCENARIO_ANONYMOUS_MAIL_SET) {
      $notice = $this->notice;
      $notice[self::NOTICE_TYPE_VISITOR_EMAIL] = $this->anonymous_email;
      $this->notice = $notice;
    }

    if(!empty($this->visitor_id)) {
      if (!empty($this->notice[self::NOTICE_TYPE_VISITOR_EMAIL])) {
        if(empty($visitor->email)) {
          $visitor = $this->visitor;
          $visitor->email = $this->notice[self::NOTICE_TYPE_VISITOR_EMAIL];
          $visitor->save();
        }
      }
    }

    /*if (is_array($this->notice)) {
      $this->notice = json_encode($this->notice);
    }*/

    return parent::beforeSave($insert); // TODO: Change the autogenerated stub
  }


  static function getExportData($where, $template = false, $calc = true)
  {

    $visitors = VisitorLog::find()
        ->andWhere($where)
        ->all();
    $out = [];

    $icons = [
        self::TYPE_ANONYMOUS => 'fa fa-user-secret',
        self::TYPE_NEW => 'fa fa-user',
        self::TYPE_REGULAR => 'fa fa-user',
        self::TYPE_GROUP => 'fa fa-users',
    ];

    if ($template) {
      $template = file_get_contents(Yii::$app->viewPath . '/' . $template . '.twig');
    };

    foreach ($visitors as $visit) {
      $visitor = array();
      if ($visit->visitor_id) {
        $v = $visit->visitor;
        $visitor['l_name'] = $v['l_name'];
        $visitor['f_name'] = $v['f_name'];
      } else {
        $visitor['f_name'] = Yii::t('app', "Anonymous");
        $visitor['l_name'] = "";
      }

      if (!empty($visit['guest_m']) || !empty($visit['guest_chi'])) {
        $visit->type = VisitorLog::TYPE_GROUP;
      }

      $visitor['name'] = trim($visitor['l_name'] . ' ' . $visitor['f_name']);
      if ($calc) {
        $visitor['cost'] = $visit->cost;
      }
      $visitor['currency'] = Yii::$app->cafe->currency;
      $visitor['id'] = $visit['id'];
      $visitor['start_time'] = date(Yii::$app->params['lang']['time'], strtotime($visit['add_time']));
      $visitor['user'] = $visit->user->name;
      $visitor['type'] = $visit->type;
      $visitor['pause_start'] = $visit->pause_start;
      $visitor['visit_cnt'] = $visit->visit_cnt;
      $visitor['type_str'] = VisitorLog::typeList($visit->type);
      $visitor['color'] = VisitorLog::$colors[$visit->type];
      $visitor['icon'] = $icons[$visit->type];
      $visitor['persons_summary'] = 1 + $visit['guest_m'] + $visit['guest_chi']; // People summary in this Visit

      if ($template) {
        //$visitor = Yii::$app->viewPath;
        $visitor['code'] = md5($visitor['id']);
        $visitor = [
            'msg' => Yii::$app->TwigString->render($template, $visitor),
            'code' => $visitor['code']
        ];
      }
      $out[] = $visitor;
    }

    return $out;
  }

  static function getUserWaitPay($cafe_id = false)
  {
    if (!$cafe_id) {
      $cafe_id = Yii::$app->cafe->id;
    };

    return self::getExportData([
        'and',
        [
            'cafe_id' => $cafe_id,
            'pay_state' => 0,
        ],
        'finish_time is not null'
    ], 'wait_pay_tpl');
  }

  static function getUserInCafe($cafe_id = false)
  {
    if (!$cafe_id) {
      $cafe_id = Yii::$app->cafe->id;
    };

    return self::getExportData(
        [
            'cafe_id' => $cafe_id,
            'finish_time' => null
        ],
        false,
        false);
  }

  public function endPause($onlyCalc = false)
  {
    if (!$this->pause_start) {
      return;
    }

    // Ensure pause is initialized to 0 if null
    if ($this->pause === null) {
      $this->pause = 0;
    }

    $this->pause += time() - $this->pause_start;

    if (!$onlyCalc) {
      $this->pause_start = 0;
    }
  }

  /**
   * Populates model with aggregated VisitLog data for Unite
   *
   * @param $model
   * @param $models
   */
  public static function populateUniteData(&$model, $models)
  {
    /* @var $model self */
    /* @var $models self[] */
    foreach ($models as $visit) {
      // Force recalculate
      $visit->endPause(true);

      $model->unite_sum += $visit->sum;
      $model->unite_cost += $visit->cost;
      $model->unite_prepay += $visit->getPrePayVisit();
      $sale = $visit->shopSale;
      if ($sale) {
        $model->unite_shop += $sale->cost;
      }

      $vats = $visit->vat;
      if (!is_array($vats)) {
        $vats = json_decode($vats, true);
      }

      // VAT merging
      foreach ($vats as $vat) {
        $vat_merged = false;

        foreach ($model->unite_vat as $uniteVatIndex => $uniteVat) {
          // If VAT equals - merge it
          if ($uniteVat['name'] == $vat['name']) {
            $vat_merged = true;
            $vats[$uniteVatIndex]['vat'] += (float)$model->unite_vat[$uniteVatIndex]['vat'];
          }
        }

        // If VAT is absent - add it
        if (!$vat_merged) {
          $model->unite_vat[] = $vat;
        }
      }
      $model->unite_vat = $vats;

      $visitor = $visit->visitor;
      if ($visitor) {
        $model->unite_people[$visit->id] = $visitor->f_name . ' ' . $visitor->l_name . ' (' . $visitor->code . ')';
      } else {
        $model->unite_people[$visit->id] = Yii::t('app', 'Anonymous');
      }
    }
  }

  /**
   * Can email be sended to Visitor or not
   * Used to check availability of sending Check via Email
   *
   * @return bool
   */
  public function canSendMail()
  {
    return (
      // Anonymous and we have specified email
        (
            $this->type == self::TYPE_ANONYMOUS &&
            !empty($this->notice[self::NOTICE_TYPE_VISITOR_EMAIL])
        )
        ||
        // NOT Anonymous and we have valid Visitor with email
        (
            $this->type != self::TYPE_ANONYMOUS &&
            $this->visitor && !empty($this->visitor->email)
        )
    ) ? true : false;
  }

  /**
   * Get visit email
   *
   * @return null|string
   */
  public function getVisitorEmail()
  {
    if ($this->type == self::TYPE_ANONYMOUS && !empty($this->notice[self::NOTICE_TYPE_VISITOR_EMAIL])) {
      return $this->notice[self::NOTICE_TYPE_VISITOR_EMAIL];
    } else if ($this->type != self::TYPE_ANONYMOUS && $this->visitor && !empty($this->visitor->email)) {
      return $this->visitor->email;
    }

    return null;
  }

  /**
   * Can make check for Visit or not
   * Used to check availability of printing or mailing check
   *
   * @return bool
   */
  public function canMakeCheck()
  {
    if ($this->getIsUnite() && !$this->getIsUniteMaster()) {
      // If this is Unite and We are not Unite master - false
      return false;
    }

    return true;
  }

  public function getCheckData()
  {
    $cafe = $this->cafe;
    $params = $cafe->getParam()->asArray()->one();
    $langParams = CafeParams::composeLangParams($params);
    $cafeCurrency = Yii::t('app', $cafe->currency);

    $total_visit = [
        'cost' => 0,
        'sum' => 0,
        'vat' => [],
        'vat_total' => 0,
        'prepay' => 0,
        'to_pay' => 0,
    ];
    $total_cart = [
        'cost' => 0,
        'sum' => 0,
        'vat' => [],
        'vat_total' => 0,
        'total' => 0,
        'count' => 0,
    ];

    $visitor = $this->getVisitor()->one();
    if ($visitor) {
      Yii::$app->language = $visitor->lg;
    }

    if ($this->getIsUniteMaster()) {
      $visits = $this->getUniteModels();
    } else {
      $visits = [$this];
    }

    $summary_vat = [];
    $summary_cart_vat = [];

    $visits_data = [];
    $cart_data = [];
    foreach ($visits as $visit) {
      $visits_out = $visit->getAttributes(['id', 'add_time', 'finish_time', 'duration', 'pause', 'cost', 'sum', 'guest_m', 'guest_chi']);

      $visits_out['add_time'] = date($langParams['datetime'], strtotime($visits_out['add_time']));
      $visits_out['finish_time'] = date($langParams['datetime'], strtotime($visits_out['finish_time']));
      $visits_out['duration'] = Yii::$app->helper->echo_time($visits_out['duration']);
      $visits_out['pause'] = Yii::$app->helper->echo_time($visits_out['pause']);
      $visits_out['vat_total'] = $visits_out['cost'] - $visits_out['sum'];
      $visits_out['certificate'] = $visit->getCertificateTypeString();
      $visits_out['prepay'] = $visit->getPrePayVisit();
      $visits_out['to_pay'] = $visits_out['cost'] - $visit->getPrePayVisit();
      if ($visits_out['prepay'] < 0) $visits_out['prepay'] = 0;

      if ($visit->shopSale) {
        $sale = $visit->shopSale;
        if ($sale) {
          $transactions = $sale->transactions;
          if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
              $tVat = [];
              //d($transaction->vat);
              if (!empty($transaction->vat)) {
                foreach ($transaction->vat as $vat) {
                  $vat_merged = false;
                  $vat['vat'] = $vat['vat'] * $transaction->quantity;

                  /*foreach ($summary_cart_vat as $uniteVatIndex => $uniteVat) { //не понял для чего этот блок
                    // If VAT equals - merge it
                    if ($uniteVat['name'] == $vat['name']) {
                      $vat_merged = true;
                      $summary_cart_vat[$uniteVatIndex]['vat'] = ((float)$summary_cart_vat[$uniteVatIndex]['vat'] + (float)$summary_cart_vat[$uniteVatIndex]['vat']);
                    }
                  }*/

                  // If VAT is absent - add it
                  if (!$vat_merged) {
                    $summary_cart_vat[] = $vat;
                  }

                  $tVat[$vat['name']] = $vat;
                  //$tVat[$vat['name']]['vat'] .= ' ' . $cafeCurrency;
                }
              }

              $cart_data[] = [
                  'counter' => count($cart_data) + 1,
                  'name' => $transaction->getProductTitle(),
                  'sum' => $transaction->sum,//. ' ' . $cafeCurrency,
                  'vat' => $tVat,
                  'price' => $transaction->price,
                  'count' => $transaction->quantity,
                  'total' => $transaction->cost,//. ' ' . $cafeCurrency,
                  'to_pay' => $transaction->cost
              ];

              $total_cart['cost'] += $transaction->cost;
              $total_cart['sum'] += $transaction->sum;
              $total_cart['total'] += $transaction->cost;
              $total_cart['count'] += $transaction->quantity;
            }
          }
          //ddd($tVat);
        }
      }

      // VAT Merging copy-paste from populateUniteData()
      foreach ($visit->vat as $vat) {
        $vat_merged = false;

        foreach ($summary_vat as $uniteVatIndex => $uniteVat) {
          // If VAT equals - merge it
          if ($uniteVat['name'] == $vat['name']) {
            $vat_merged = true;
            $summary_vat[$uniteVatIndex]['vat'] = ((float)$summary_vat[$uniteVatIndex]['vat'] + (float)$summary_vat[$uniteVatIndex]['vat']);
          }
        }

        // If VAT is absent - add it
        if (!$vat_merged) {
          $summary_vat[] = $vat;
        }

        $visits_out['vat'][$vat['name']] = $vat;
        //$visits_out['vat'][$vat['name']]['vat'] .= ' ' . $cafeCurrency;
      }

      $total_visit['cost'] += $visits_out['cost'];
      $total_visit['sum'] += $visits_out['sum'];
      //$total_visit['vat_total'] += $visits_out['vat_total'];
      $total_visit['to_pay'] += $visits_out['to_pay'];
      $total_visit['prepay'] += $visits_out['prepay'];

      //$visits_out['cost'] .= ' ' . $cafeCurrency;
      //$visits_out['sum'] .= ' ' . $cafeCurrency;
      $visits_out['to_pay'] = number_format($visits_out['to_pay'], 2, '.', '');// . ' ' . $cafeCurrency;
      if (empty($visits_out['prepay']) || $visits_out['prepay'] <= 0) {
        unset($visits_out['prepay']);
      } else {
        $visits_out['prepay'] = number_format($visits_out['prepay'], 2, '.', '');// . ' ' . $cafeCurrency;
      }

      $visits_data[] = $visits_out;
    }

    $total = $total_visit;

    foreach ($summary_vat as $sVat) {
      $total_visit['vat'][$sVat['name']] = $sVat;
      //$total_visit['vat'][$sVat['name']]['vat'] .= ' ' . $cafeCurrency;
      $total_visit['vat_total'] += $sVat['vat'];

      $total['vat'][$sVat['name']] = $sVat;
      $total['vat_total'] += $sVat['vat'];
    }

    /*$total_visit['cost'] .= ' ' . $cafeCurrency;
    $total_visit['sum'] .= ' ' . $cafeCurrency;
    $total_visit['vat_total'] .= ' ' . $cafeCurrency;
*/
    /*if (!empty($cart_data)) {
      $total['cost'] += $total_cart['cost'];
      $total['sum'] += $total_cart['sum'];
      $total['to_pay'] += $total_cart['cost'];
    }*/
    foreach ($summary_cart_vat as $sCartVat) {
      $name = $sCartVat['name'];
      if (empty($total_cart['vat'][$name])) {
        $total_cart['vat'][$name] = $sCartVat;
        //d($sCartVat,$name);
      } else {
        $total_cart['vat'][$name]['vat'] += $sCartVat['vat'];
      }
      //$total_cart['vat'][$sCartVat['name']]['vat'] .= ' ' . $cafeCurrency;
      $total_cart['vat_total'] += $sCartVat['vat'];

      $total['vat_total'] += $sVat['vat'];
    }
//d($total_cart['vat']);
//ddd($summary_cart_vat);

    //$total_cart['cost'] .= ' ' . $cafeCurrency;
    //$total_cart['sum'] .= ' ' . $cafeCurrency;
    //$total_cart['vat_total'] .= ' ' . $cafeCurrency;

    $total_visit['to_pay'] = number_format($total_visit['to_pay'], 2, '.', '');// . ' ' . $cafeCurrency;

    if (empty($total_visit['prepay']) && $total_visit['prepay'] <= 0) {
      unset($total_visit['prepay']);
    } else {
      $total_visit['prepay'] = number_format($total_visit['prepay'], 2, '.', '');// . ' ' . $cafeCurrency;
    }

    $data = [
        "cafe" => $cafe->getAttributes(['id', 'name', 'max_person', 'address', 'currency', 'vat_code']),
        "visits" => $visits_data,
        "total_visits" => $total_visit,
        "visitor" => $this->visitor,
    ];

    if (!empty($cart_data)) {
      $data['cart'] = $cart_data;
      $total_cart['cost'] = number_format($total_cart['cost'], 2, '.', '');//. ' ' . $cafeCurrency;
      $total_cart['sum'] = number_format($total_cart['sum'], 2, '.', '');//. ' ' . $cafeCurrency;
      $total_cart['total'] = number_format($total_cart['total'], 2, '.', '');//. ' ' . $cafeCurrency;
      $total_cart['count'] = number_format($total_cart['count'], 0, '.', '');

      $total_cart['to_pay'] = $total_cart['cost'];
      $data['total_cart'] = $total_cart;

      /*if (empty($total['prepay']) && $total['prepay'] <= 0) {
        unset($total['prepay']);
        unset($total['to_pay']);
      } else {
        $total['to_pay'] = number_format($total['to_pay'], 2, '.', '') ;//. ' ' . $cafeCurrency;
        $total['prepay'] = number_format($total['prepay'], 2, '.', '') ;//. ' ' . $cafeCurrency;
      }
      $total['cost'] = number_format($total['cost'], 2, '.', '') ;//. ' ' . $cafeCurrency;
      $total['sum'] = number_format($total['sum'], 2, '.', '') ;//. ' ' . $cafeCurrency;
      $total['vat_total'] = number_format($total['vat_total'], 2, '.', '') ;//. ' ' . $cafeCurrency;

      foreach ($total['vat'] as &$sVat) {
        $sVat['vat'] = number_format($sVat['vat'], 2, '.', '') ;//. ' ' . $cafeCurrency;
      }

      //$data['total_total'] = $total;*/
    }
//ddd($data);
    $data['cafe']['logo'] = $cafe->getLogo();
    return $data;
  }

  /**
   * Changes pay_state and saves model or models if this is UNITE visit
   *
   * @return bool
   */
  public function makePay($method)
  {
    if ($this->getIsUnite()) {
      $models = $this->getUniteModels();
    } else {
      $models = [$this];
    }

    $transaction = Yii::$app->getDb()->beginTransaction();
    try {
      foreach ($models as $visit) {
        $visit->pay_state = $method;

        $saved = $visit->save();

        if ($saved) {
          $shopSale = $visit->shopSale;
          if ($shopSale) {
            $shopSale->pay_state = $method;
            $saved = $shopSale->save();
          }
        }

        if ($saved) {
          // OK
        } else {
          throw new \Exception('Visit save error');
        }
      }

      $transaction->commit();
    } catch (\Exception $e) {
      $transaction->rollBack();
      throw new \Exception(Yii::t('app', 'The visit pay_state change error.'));
    }

    return true;
  }

  /**
   * Is visit finished
   *
   * @return bool
   */
  public function getIsFinished()
  {
    return ($this->finish_time) ? true : false;
  }

  /**
   * Setup Ungrouping to model
   *
   * @return bool
   */
  public function setupUngroup($model, $type)
  {
    $this->ungroup_model = $model;
    $this->ungroup_type = $type;

    $this->limit_guest_m = $model->guest_m;
    $this->limit_guest_chi = $model->guest_chi;

    if ($type == self::UNGROUP_TYPE_MATURE) {
      $this->limit_guest_m -= 1;
    } elseif ($type == self::UNGROUP_TYPE_CHILD) {
      $this->is_child = 1; // Child under account of Mature
      $this->limit_guest_chi -= 1;
    }

    return true;
  }

  /**
   * Apply Ungroup to ungroup model
   *
   * @return bool
   */
  public function applyUngroup()
  {
    if ($this->getIsUngrouping()) {
      $this->ungroup_model->guest_m -= (int)$this->guest_m;
      $this->ungroup_model->guest_chi -= (int)$this->guest_chi;

      if ($this->ungroup_type == self::UNGROUP_TYPE_MATURE) {
        $this->ungroup_model->guest_m -= 1;
      } else if ($this->ungroup_type == self::UNGROUP_TYPE_CHILD) {
        $this->ungroup_model->guest_chi -= 1;
      }
    }

    return true;
  }

  /**
   * Is this Ungrouping
   *
   * @return bool
   */
  public function getIsUngrouping()
  {
    return ($this->ungroup_model) ? true : false;
  }

  /**
   * Is this Group
   *
   * @return bool
   */
  public function getIsGroup()
  {
    return (!empty($this->guest_m) || !empty($this->guest_chi)) ? true : false;
  }

  /**
   * Is this part of Unite
   *
   * @return bool
   */
  public function getIsUnite()
  {
    if ($this->pay_man == null) return false;
    if ($this->pay_man != $this->id) return true;

    return false;
    //return (VisitorLog::find()->where(['pay_man'=>$this->id])->count()>1);
  }

  public function makeTestUniq()
  {
    if ($this->pay_man) {
      return;
    }

    $this->display_visit_timing = false;

    $models = VisitorLog::find()->where(['id' => $this->pay_man])->all();
    self::populateUniteData($this, $models);

    //$this->unite_persons;
    $this->cost = $this->unite_cost;
    $this->sum = $this->unite_sum;
    $this->_prepay = $this->unite_prepay;
    //$this->unite_vat;
    //$this->unite_people;

    return "makeTestUniq";
  }

  /**
   * Is this unite Master
   *
   * @return bool
   */
  public function getIsUniteMaster()
  {
    return ($this->pay_man == $this->id);
  }

  /**
   * Returns all VisitLogs of Unite
   *
   * @return array|self[]
   */
  public function getUniteModels()
  {
    return self::find()->andWhere(['pay_man' => $this->id])->all();
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getShopSale()
  {
    $data = $this->hasOne(
        ShopSale::className(),
        [
            'visitor_log_id' => 'id'
        ]
    );

    if ($this->pay_state > 0) {
      $time = strtotime($this->finish_time) - 15;
      $transactions = Transaction::find()
          ->where(['>', 'created_at', date('Y-m-d H:i:s', $time)])
          ->asArray()
          ->all();
      $transactions = ArrayHelper::getColumn($transactions, 'sale_id');

      return $data->andWhere([ShopSale::tableName() . '.id' => $transactions]);
    } else {
      return $data->andWhere([ShopSale::tableName() . '.pay_state' => 0]);
    }
  }

  public function getArrayData()
  {
    $this->validate();
    return $this->toArray(['guest_m', 'guest_chi', 'certificate_type', 'certificate_number', 'certificate_val']);
  }

  public static function findDiscounts(VisitorLog $visit, $cafe = null)
  {
    if ($cafe === null) {
      $cafe = Yii::$app->cafe->get();
    }
    /* @var $cafe Cafe */

    if ($visit->visitor_id) {
      $visits = explode("/", $visit->visit_cnt);
    } else {
      $visits = [1, 1, 1];
    }
    // Visits format week/month/all

    $franchiseeDiscounts = $cafe->franchisee->getDiscounts();
    $cafeDiscounts = $cafe->getDiscounts();

    $periodicDiscount = null;
    $defaultDiscount = null;

    $compareDiscounts = function (&$competitor, $discount) {
      if ($competitor === null) {
        $competitor = $discount;
      } elseif ((float)$competitor['value'] <= (float)$discount['value']) {
        $competitor = $discount;
      }
    };

    $findDiscounts = function ($discount) use ($visits, &$periodicDiscount, &$defaultDiscount, $compareDiscounts) {
      $periodic = (boolean)$discount['periodic'];
      $use = (int)$discount['use'];
      $visitsCount = (int)$discount['number'];

      if (!isset($visits[$use])) {
        return false;
        //throw new \Exception('Invalid discount period');
      }

      $competitorVisitsCount = (int)$visits[$use];

      if ($periodic) {
        // Discount applies on every N visits
        if ($competitorVisitsCount % $visitsCount != 0) {
          return false;
        }
      } else {
        // Discount applies on exact visit count
        if ($competitorVisitsCount != $visitsCount) {
          return false;
        }
      }

      if ($periodic) {
        $compareDiscounts($periodicDiscount, $discount);
      } else {
        $compareDiscounts($defaultDiscount, $discount);
      }

      return true;
    };

    foreach ($franchiseeDiscounts as $discount) {
      $findDiscounts($discount);
    }

    foreach ($cafeDiscounts as $discount) {
      $findDiscounts($discount);
    }

    $result = null;

    if ($periodicDiscount !== null || $defaultDiscount !== null) {
      $result = [];

      if ($periodicDiscount !== null) {
        $result[] = $periodicDiscount;
      }

      if ($defaultDiscount !== null) {
        $result[] = $defaultDiscount;
      }
    }

    return $result;
  }

  public function afterSave($insert, $changedAttributes)
  {
    if (
        isset($changedAttributes['cost']) ||
        isset($changedAttributes['pay_state']) ||
        isset($changedAttributes['terminal_ans'])

    ) {
      $this->transitionTest($insert, $changedAttributes);

      if (
          isset($changedAttributes['pay_state']) &&
          $changedAttributes['pay_state'] == 0 &&
          $this->pay_state > 0
      ) {
        $sales = ShopSale::find()
            ->where([
                'pay_state' => 0
            ])
            ->all();

        if ($sales) {
          foreach ($sales as $sale) {
            $sale->pay_state = $this->pay_state;
            $sale->pay_man = $this->pay_man;
            $sale->save();
          }
        }
      }
    }
    if (isset($changedAttributes['visitor_id'])) {
      $transactions = Transaction::find()
          ->where(['visit_id' => $this->id])
          ->all();
      foreach ($transactions as $transaction) {
        $transaction->visitor_id = $this->visitor_id;
        $transaction->save();
      }

    }
    parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
  }

  private function transitionTest($insert, $changedAttributes)
  {
    if (isset($changedAttributes['add_time'])) {
      $delta = strtotime($this->add_time) - strtotime($changedAttributes['add_time']);
      $sql = 'UPDATE `transaction` SET `created_at` = ADDDATE(`created_at`,INTERVAL ' . $delta . ' SECOND) WHERE visit_id = ' . $this->id;
      Yii::$app->db
          ->createCommand($sql)
          ->execute();
    }

    //если только что перевели метод платежа с 0 на тип то это новый платеж
    $new_payment = isset($changedAttributes['pay_state']) && ($changedAttributes['pay_state'] == 0);

    //Если платеж стал неоплачен  или его сумма 0 то для нового платежа просто выходим, а старый удаляем последнюю оплату
    if (
        $this->pay_state == self::PAY_METHOD_NOT_PAID ||
        ($this->cost == 0 && $this->finish_time > 0)
    ) {
      if (!$new_payment) {
        Transaction::find()
            ->where(['visit_id' => $this->id])
            ->orderBy(['id' => SORT_DESC])
            ->one()
            ->delete();
      }
      return;
    }

    if ($this->pay_state == 0) return;

    //Для всех остальных случаев нам нужны списки платежей
    $transactions = Transaction::find()
        ->where(['visit_id' => $this->id])
        ->orderBy(['id' => SORT_DESC])
        ->all();

    //для нового платежа создаем платеж и выходим
    if ($new_payment || !$transactions) {
      $transaction = new Transaction();
      $transaction->sum = $this->sum - $this->getPrePay('sum');
      $transaction->cost = $this->cost - $this->getPrePay('cost');
      $transaction->visitor_id = $this->visitor_id;
      $transaction->visit_id = $this->id;
      $transaction->method = $this->pay_state;
      $transaction->created_at = date("Y-m-d H:i:s");
      $transaction->pay_man = $this->pay_man;
      $transaction->terminal_ans = $this->terminal_ans;

      //сохраненеи если сумма больше 0 только
      if ($transaction->sum > 0) {
        $transaction->save();
      }

      if ($this->pay_man == $this->id) {
        $visits = VisitorLog::find()
            ->where([
                'pay_man' => $this->id,
                'pay_state' => 0])
            ->all();
        if ($visits) {
          foreach ($visits as $visit) {
            $visit->pay_state = $this->pay_state;
            $visit->save();
          }
        }
      }

      if (
          $this->pay_state != self::PAY_METHOD_MULI &&

          $this->getPrePay('cash_cnt', true) != $this->getPrePay('cnt') &&
          $this->getPrePay('cash_cnt') != 0

      ) {
        $this->pay_state = self::PAY_METHOD_MULI;
        $this->save();
      }

      return;
    }

    if (isset($changedAttributes['cost'])) {
      $cost = $this->cost;
      $sum = $this->sum-$this->getPrePay('sum');
      //если доплата
      if ($cost > $this->getPrePay()) {
        $transactions[0]->cost += $cost - $this->getPrePay();
        $transactions[0]->sum += $sum;
      } elseif ($cost < $this->getPrePay()) {//если сумма уменьшилась
        $delta_cost = $this->getPrePay() - $cost;
        $sum = -$sum;
        while ($delta_cost > $transactions[0]->sum) {
          $delta_cost -= $transactions[0]->cost;
          $sum -= $transactions[0]->sum;
          $transactions[0]->delete();
          array_shift($transactions);
        }
        $transactions[0]->cost += $delta_cost;
        $transactions[0]->sum += $sum;
      }
    }

    if (isset($changedAttributes['pay_state']) && $this->pay_state != self::PAY_METHOD_MULI) {
      $transactions[0]->method = $this->pay_state;
    }

    if (isset($changedAttributes['terminal_ans'])) {
      $transactions[0]->terminal_ans = $this->terminal_ans;
    }
    $transactions[0]->save();
  }

  public function beforeDelete()
  {
    $transactions = Transaction::find()
        ->where(['visit_id' => $this->id])
        ->all();
    foreach ($transactions as $transaction) {
      $transaction->delete();
    }

    return parent::beforeDelete(); // TODO: Change the autogenerated stub
  }

  public function getPrePay($col = 'cost', $mast_recalc = false)
  {
    if ($this->_prepay === false || $mast_recalc) {
      $this->_prepay = Transaction::find()
          ->where(['visit_id' => $this->id])
          ->select([
              'sum(`sum`) as sum',
              'sum(`cost`) as cost',
              'count(id) as cnt',
            // '`sum` as sum_last',
            //'`cost` as cost_last',
              'sum(if(method=' . self::PAY_METHOD_CASH . ',1,0)) as cash_cnt'
          ])
          ->orderBy(['id' => SORT_ASC])
          ->asArray()
          ->one();
    }
    $value = $this->_prepay ? ($this->_prepay[$col] ?? 0) : 0;
    return round((float)$value, 2);
  }

  public function getPrePayVisit($col = 'cost')
  {
    if ($this->pay_state < 1) {
      return $this->getPrePay($col);
    }

    if (!$this->_prepayvisit) $this->getLastPay();

    $out = $this->_prepayvisit ? ($this->getPrePay($col) - $this->getLastPay($col)) : 0;
    return round($out, 2);
  }

  public function getLastPay($col = 'cost')
  {
    if ($this->_prepayvisit === false) {
      $this->_prepayvisit = Transaction::find()
          ->where(['visit_id' => $this->id])
          ->select('sum,cost')
          ->orderBy(['id' => SORT_DESC])
          ->asArray()
          ->one();
    }

    $out = $this->_prepayvisit ? ($this->_prepayvisit[$col] ?? 0) : 0;
    return round((float)$out, 2);
  }

  public function getDiscountsString()
  {
    if (empty($this->notice[self::NOTICE_TYPE_DISCOUNTS]) || !is_array($this->notice[self::NOTICE_TYPE_DISCOUNTS])) {
      return '';
    }

    $content = [];

    foreach ($this->notice[self::NOTICE_TYPE_DISCOUNTS] as $discount) {
      $string = '' . Yii::t('main', 'Discounts') . ': ';
      if ((boolean)$discount['periodic']) {
        $string .= Yii::t('main', 'Every') . ' ';
      }

      $string .= '' . $discount['number'] . ' ' . Yii::t('main', 'visit') . ' ';
      $string .= $discount['value'] . '%';

      $content[] = $string;
    }

    return implode('<br>', $content);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getTransactions()
  {
    return $this->hasMany(Transaction::className(), ['visit_id' => 'id']);
  }

}
