<?php

namespace frontend\modules\cafe\components;

use frontend\modules\cafe\models\Cafe as CafeModule;
use frontend\modules\cafe\models\CafeAuthAssignment;
use frontend\modules\cafe\models\CafeParams;
use frontend\modules\tariffs\models\Tariffs;
use frontend\modules\users\models\UserCafe;
use frontend\modules\users\models\Users;
use frontend\modules\users\models\UserLog;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class Cafe extends Component
{

  private $cafe = false;
  private $iCan = [];
  private $usersList = false;
  public $tariff = [];
  public $params = [];
  public $max_cost;
  public $first_weekday;
  private $users_cafe = [];

  public function init()
  {
    parent::init();

    if (empty(Yii::$app->session)) return;

    $cafe_id = Yii::$app->session->get('cafe_id', false);
    if (!$cafe_id) return;
    $this->start($cafe_id);

  }

  public function start($cafe_id)
  {
    $cache = Yii::$app->cache;
    $data = $cache->getOrSet("cafe_params_" . $cafe_id, function () use ($cafe_id) {
      $data['cafe'] = CafeModule::findOne(['id' => $cafe_id]);

      if (!$data['cafe']) {
        Yii::$app->session->remove('cafe_id');
        Yii::$app->response->refresh();
        return false;
      }

      $data['params'] = $data['cafe']->getParam()->one();
      if ($data['params']) {
        $data['params'] = $data['params']->toArray();
      }

      $data['iCan'] = CafeAuthAssignment::find()
          ->select(['item_name'])
          ->where(['cafe_id' => $cafe_id])
          ->asArray()
          ->column();

      $tariffs = Tariffs::find()
          ->andWhere([
              'type_id' => Tariffs::TYPE_CAFE_ORIENTED,
              'cafe_id' => $data['cafe']['id'],
              'active' => 0,
              'franchisee_id' => $data['cafe']['franchisee_id'],
          ])
          ->orderBy('start_visit DESC')
          ->indexBy('start_visit')
          ->asArray()
          ->all();

      if (!$tariffs) {
        $tariffs = Tariffs::find()
            ->andWhere([
                'type_id' => Tariffs::TYPE_REGIONAL,
                'params_id' => $data['cafe']['params_id'],
                'active' => 0,
                'franchisee_id' => $data['cafe']['franchisee_id'],
            ])
            ->orderBy('start_visit DESC')
            ->indexBy('start_visit')
            ->asArray()
            ->all();
      }

      $data['tariff'] = $tariffs;

      $data['max_cost'] = 0;
      foreach ($data['tariff'] as $t) {
        if ($data['max_cost'] < $t['max_sum']) $data['max_cost'] = $t['max_sum'];
      }

      $data['users_cafe'] = !empty(Yii::$app->user) && !empty(Yii::$app->user->identity) ? Yii::$app->user->identity->cafes : [];

      return $data;
    }, 900);

    $this->cafe = $data['cafe'];
    $this->first_weekday = $data['params']['first_weekday'];

    if ($data['cafe']) {
      if ($data['params']) {
        Yii::$app->timeZone = $data['params']['time_zone'];
        $this->params = $data['params'];
      }

      $this->iCan = $data['iCan'];
      $this->tariff = $data['tariff'];
      $this->max_cost = $data['max_cost'];
      $this->users_cafe = $data['users_cafe'];

      Yii::$app->params['lang'] = ArrayHelper::merge(
          Yii::$app->params['lang'],
          CafeParams::composeLangParams($data['params'])
      );

      //ddd($this);
    } else {
      $this->iCan = [];
    }
  }

  public function can($item)
  {
    if (in_array($item, $this->iCan)) {
      return true;
    }

    return false;
  }

  public function testActiveUntil()
  {
    if (
        !Yii::$app->session->has('active_until') ||
        Yii::$app->session->get('active_until') < time()
    ) {
      $active_until = $this->cafe->getFranchisee()->one()->active_until;
      $active_until = strtotime($active_until);
      Yii::$app->session->set('active_until', $active_until);
    }

    return Yii::$app->session->get('active_until') > time();
  }

  public function getId()
  {
    return $this->cafe ? $this->cafe->id : null;
  }

  public function getName()
  {
    return $this->cafe ? $this->cafe->name : null;
  }

  public function getCurrency()
  {
    return $this->cafe ? Yii::t('app', $this->cafe->currency) : null;
  }

  public function getMaxCost()
  {
    return $this->cafe ? $this->max_cost : 100;
  }

  public function getTips_var()
  {
    if (empty($this->cafe->tips_var)) {
      return false;
    }
    return explode(',', $this->cafe->tips_var);
  }

  public function getMaxPersonsCount()
  {
    return $this->cafe ? $this->cafe->max_person : 100;
  }

  public function getCurrentPersonsCount()
  {
    if ($this->cafe) {
      return \frontend\modules\cafe\models\Cafe::getCurrentPersonsCount($this->getId());
    }

    return 0;
  }


  public function getUsersList()
  {
    if (!$this->cafe) return array();

    if (!$this->usersList) {
      $this->usersList = Users::find()
          ->where(['franchisee_id' => $this->cafe->franchisee_id])
          ->asArray()
          ->all();
    };

    return $this->usersList;
  }

  public function getCafeUsersList()
  {
    if (!$this->cafe) return array();
    return UserCafe::find()
        ->select(Users::tableName() . '.*')
        ->leftJoin(Users::tableName(), Users::tableName() . '.id=user_id')
        ->where([
            'cafe_id' => $this->cafe->id,
            'state' => 0
        ])
        ->asArray()
        ->all();

  }

  public function getLogo()
  {
    return $this->cafe ? $this->cafe->getLogo() : \frontend\modules\cafe\models\Cafe::$default_logo;
  }

  public function getFranchiseeId()
  {
    return $this->cafe ? $this->cafe['franchisee_id'] : null;
  }

  public function getParamsId()
  {
    return $this->cafe ? $this->cafe['params_id'] : null;
  }

  public function getVatCode()
  {
    return $this->cafe ? $this->cafe['vat_code'] : null;
  }

  public function getUsersCafe()
  {
    return $this->cafe ? $this->users_cafe : [];
  }

  public function getWidth()
  {
    return $this->cafe ? $this->cafe->width : 80;
  }

  public function getChildDiscount()
  {
    return $this->cafe ? $this->cafe['child_discount'] : 0;
  }

  public function get()
  {
    return $this->cafe ? $this->cafe : null;
  }

  public function getLanguageList()
  {
    if (!$this->cafe) {
      return Yii::$app->params['lg_list'];
    }

    $lg_list = $this->cafe->franchisee->language_ids;
    if (count($lg_list) == 0) {
      return Yii::$app->params['lg_list'];
    };
    $out = [];
    foreach ($lg_list as $lg) {
      $out[$lg] = Yii::$app->params['lg_list'][$lg];
    }
    return $out;
  }

  public function getWeakdays()
  {
    $out = [];
    $list = [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
    ];
    $di = $this->params['first_weekday'];
    for ($i = 0; $i < 7; $i++) {
      $k = ($i + $di) % 7;
      $out[$k . ''] = Yii::t('app', $list[$k]);
    }

    return $out;
  }

  public function getInitSuccessful()
  {
    return $this->cafe->initSuccessful || $this->cafe->testSuccessful(false);
  }

  public function getActiveUsers()
  {
      if (!$this->cafe) {
          return [];
      }
      return UserLog::find()
          ->where(['cafe_id' => $this->cafe->id, 'finish' => null])
          ->all();
  }
}