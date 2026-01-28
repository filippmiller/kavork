<?php

namespace frontend\modules\franchisee\models;

use frontend\modules\cafe\models\Cafe;
use frontend\modules\paypal\models\Paypal;
use frontend\modules\tariffs\models\Tariffs;
use frontend\modules\users\models\Users;
use Yii;


/**
 * This is the model class for table "{{%franchisee_payments}}".
 *
 * @property int $id ID
 * @property int $franchisee_id
 * @property int $count
 * @property float $sum
 * @property string $code
 * @property int $status
 * @property int $tariff_id
 * @property string $comment
 * @property string $created_at
 * @property array $data
 *
 *
 * @property Franchisee $franchisee
 */
class FranchiseePayments extends \yii\db\ActiveRecord
{

  const STATUS_WAIT = 0;
  const STATUS_CANCEL = 1;
  const STATUS_DONE = 2;

  /**
   * @var Tariffs
   */
  private $tariff;

  /**
   * @var String
   */
  public $payment_url;

  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return '{{%franchisee_payments}}';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['count', 'tariff_id'], 'required'],
        [['franchisee_id', 'status', 'tariff_id', 'count'], 'integer'],
        [['sum'], 'number'],
        [['data'], 'safe'],
        [['code', 'comment', 'created_at'], 'string', 'max' => 255],
        [['franchisee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Franchisee::className(), 'targetAttribute' => ['franchisee_id' => 'id']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'id' => Yii::t('app', 'ID'),
        'franchisee_id' => Yii::t('app', 'Franchisee'),
        'code' => Yii::t('app', 'Code'),
        'status' => Yii::t('app', 'Status'),
        'tariff_id' => Yii::t('app', 'TariffLanding'),
        'comment' => Yii::t('app', 'Comment'),
        'created_at' => Yii::t('app', 'Created At'),
        'sum' => '' . Yii::t('app', 'Payment amount') . ', USD($)',
        'count' => Yii::t('app', 'Payment period'),
    ];
  }

  public function validate($attributeNames = null, $clearErrors = true)
  {
    $valid = parent::validate($attributeNames, $clearErrors);
    if (strpos($this->formName(), 'Search') !== false) return $valid;
    return $this->calcPrice() && $valid;
  }

  static function getStatus($id = false)
  {
    $list = [
        self::STATUS_WAIT => Yii::t('app', "Wait"),
        self::STATUS_CANCEL => Yii::t('app', "Cancel"),
        self::STATUS_DONE => Yii::t('app', "Done"),
    ];

    if ($id === false) {
      if (!YII_DEBUG) {
        unset($list[self::STATUS_WAIT]);
      }
      return $list;
    }
    return (isset($list[$id]) ? $list[$id] : '-');

  }

  private function calcPrice()
  {
    if (!$this->tariff_id) {
      $this->addError('tariff_id', \Yii::t('app', 'Tariff package not changed'));
      return false;
    }

    /*if (!$this->isNewRecord) { //давало ошибку при создании платежа
      return true;
    }*/

    $tariff = FranchiseeTariffs::find()
        ->andWhere([
            'active' => FranchiseeTariffs::ACTIVE_YES,
            'id' => $this->tariff_id,
        ]);

    if (!empty($this->franchisee_id)) {
      $franchisee = $this->franchisee;

      if (!$franchisee) {
        $this->addError('tariff_id', \Yii::t('app', 'Tariff package is not available'));
        return false;
      }

      //if ($this->tariff_id != $franchisee->tariff_id) {
        $tariff->andWhere(['>=', 'cafe_count', $franchisee->getCafeCount()]);
      //}
    }

    $tariff = $tariff->one();
    if (!$tariff) {
      $this->addError('tariff_id', \Yii::t('app', 'Tariff package is not available'));
      return false;
    }

    $this->tariff = $tariff;

    $this->sum = $tariff->getPrice($this->count);
    return true;
  }

  public function makePay()
  {
    if (empty($this->sum)) {
      if (!$this->calcPrice()) {
        return false;
      }
    }

    $pay = new Paypal();
    $pay->addItem([
        'name' => ($this->tariff ? 'By ' . $this->tariff->lgName . ' for ' : 'For ') . $this->count . ' months',
        'price' => $this->sum,
        'tax' => 0
    ]);
    $payment = $pay->make_payment();

    $this->code = $payment->getToken();
    $this->payment_url = $payment->getApprovalLink();

    $this->created_at = date('Y-m-d H:i:s');
    $this->status = self::STATUS_WAIT;
    return true;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getFranchisee()
  {
    return $this->hasOne(Franchisee::className(), ['id' => 'franchisee_id']);
  }

  public function applyTariff()
  {
    if (!$this->tariff_id) {
      //$this->addError('tariff_id', \Yii::t('app', 'Tariff package not changed'));
      return false;
    }

    $tariff = FranchiseeTariffs::find()
        ->andWhere([
            'active' => FranchiseeTariffs::ACTIVE_YES,
            'id' => $this->tariff_id,
        ]);
    $tariff = $tariff->one();
    if (!$tariff) {
      //$this->addError('tariff_id', \Yii::t('app', 'Tariff package is not available'));
      return false;
    }

    $franchisee = $this->franchisee;
    if (!$franchisee) {
      if (empty($this->data)) {
        $this->addError('tariff_id', \Yii::t('app', 'Franchisee data is not available'));
        return false;
      }


      //Создаем франшизу
      $franchisee = new Franchisee();
      $franchisee->name = $this->data['name'];
      $franchisee->language_ids = $this->data['language_ids'];

      $code = explode(' ', $this->data['name']);
      foreach ($code as &$c) {
        $c = mb_strtoupper($c[0]);
      }
      $code = implode('', $code);
      $franchisee->code = $code;
      $code .= '_';
      while (
          mb_strlen($franchisee->code) < 3 ||
          Franchisee::find()->where(['code' => $franchisee->code])->count()
      ) {
        $code .= chr(random_int(65, 90));
        $franchisee->code = $code;
      }
      $franchisee->max_cafe = 1;
      $franchisee->roles = $tariff->roles;
      $franchisee->roles_ids = explode(',', $tariff->roles);
      //$franchisee->id = 4;
      $franchisee->save();
      //ddd($franchisee);

      //Создаем кафе
      $cafe = new Cafe();
      $cafe->max_person = 100;
      $cafe->franchisee_id = $franchisee->id;
      $cafe->name = $this->data['cafe_name'];
      $cafe->params_id = $this->data['params_id'];
      $cafe->currency = $this->data['currency'];
      $cafe->tips_var = '0.5,1,1.5,2,2.5';
      $cafe->selfservice_timaout = 5;
      //$cafe->id = 7;
      $cafe->save();

      //создаем пользователя
      $user = new Users();
      $user->name = explode('@', $this->data['email'])[0];
      $name = $user->name;
      $user->phone = $this->data['phone'];
      $user->state = 0;
      $user->franchisee_id = $franchisee->id;
      $i = 1;
      while (
      Users::find()->where(['name' => $user->name])->count()
      ) {
        $i++;
        $user->name = $name . $i;
      }
      $user->email = $this->data['email'];
      $password = '';
      $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
      $max = 10;
      while ($max--) $password .= $chars[rand(0, StrLen($chars) - 1)];
      $user->new_password = $password;
      $user->save();

      if (!empty(Yii::$app->session))

        $user_data =
            $user->toArray() +
            [
                'password' => $password
            ];

      if (isset(Yii::$app->session)) {
        Yii::$app->session->addFlash('success', Yii::t('app', 'Create new user <b>{name}</b> with password <b>{password}</b>', $user_data));
        if (strpos($user->email, 'demo') === false) {
          $content = Yii::$app->view->renderFile('@console/views/mails/new_user.twig', $user_data);
          Yii::$app
              ->mailer
              ->compose()
              //->setTextBody('Текст сообщения')
              ->setHtmlBody($content)
              ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->params['adminName']])
              ->setTo([
                  $user->email
              ])
              ->setSubject(Yii::t('app', 'New User Registration'))
              ->send();
        }
        if (!empty(\Yii::$app->user)) {
          \Yii::$app->user->login($user, 3600 * 24 * 30);
        }
      }

      //даем роль админа кафе
      $auth = \Yii::$app->authManager;
      $role = $auth->getRole('admin');
      $auth->assign($role, $user->id);

      $this->franchisee_id = $franchisee->id;
      $this->save();
      //ddd($this->data, $franchisee, $cafe, $this, $user, $password);
      //$this->addError('tariff_id', \Yii::t('app', 'Tariff package is not available'));
      //return false;
    }

    $this->tariff = $tariff;

    $active_until = strtotime($franchisee->active_until);
    $active_until = $active_until < time() ? time() : $active_until;

    $franchisee->roles = $tariff->roles; //перекидываем все роли тарифа в роли кафе
    $franchisee->roles_ids = explode(',', $tariff->roles);

    //Далее смена данных если только сменили тариф
    if ($this->tariff_id != $franchisee->tariff_id) {

      //Количество кафе можно тольок увеличить
      if ($tariff->cafe_count > $franchisee->max_cafe) {
        $franchisee->max_cafe = $tariff->cafe_count;
      }

      $prew_tariff = FranchiseeTariffs::find()
          ->where(['id' => $franchisee->tariff_id])
          ->one();

      //считаем сумму сколько осталось дней по прошлому тарифу
      $delta_full_day = (time() - $active_until) / 86400;

      if ($delta_full_day > 100) {
        //переводим оставшиеся дни в деньги
        $sum_last = $delta_full_day * $prew_tariff->day_price;

        //Получаем новое число дней
        $dey_new = $sum_last / $prew_tariff->day_price;

        //пересчитываем новую дату окончания взамен на остаток от прошлого тарифа
        $active_until = time() + $dey_new * 86400;
      }

      $franchisee->tariff_id = $this->tariff_id;
    }

    $active_until += $tariff->days_period * $this->count * 86400;
    $franchisee->active_until = date('Y-m-d H:i:s', $active_until);
    return $franchisee->save();
  }
}
