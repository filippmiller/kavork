<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 10.04.19
 * Time: 11:00
 */

namespace frontend\modules\franchisee\models;

use frontend\modules\cafe\models\CafeParams;
use frontend\modules\users\models\Users;
use Yii;
use yii\base\Model;

/**
 * Class FranchiseeRegistration
 * @package frontend\modules\franchisee\models
 *
 */
class FranchiseeRegistration extends Model
{
  /**
   * @var FranchiseeTariffs
   */
  public $tariff = null;

  /**
   * @var FranchiseePayments
   */
  public $payment = null;

  /**
   * @var string
   */
  public $name = '';

  /**
   * @var string
   */
  public $email = '';

  /**
   * @var array
   */
  public $language_ids = [];

  /**
   * @var int
   */
  public $count = 1;

  /**
   * @var string
   */
  public $cafe_name = '';

  /**
   * @var string
   */
  public $phone = '';

  /**
   * @var int
   */
  public $params_id = false;

  /**
   * @var string
   */
  public $currency = '';


  public function __construct(array $config = [])
  {
    $this->language_ids[] = Yii::$app->language;
    parent::__construct($config);
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['name', 'email', 'cafe_name', 'params_id', 'currency', 'phone'], 'required'],
        [['languages', 'currency'], 'safe'],
        ['name', 'unique', 'targetClass' => Franchisee::className()],
        ['email', 'unique', 'targetClass' => Users::className()],
        [['name', 'cafe_name'], 'string', 'max' => 255],
        [['phone'], 'string', 'max' => 20],
        [['params_id'], 'integer'],
        [['language_ids'], 'safe'],
        [['language_ids'], 'required'],
        ['count', 'integer'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'name' => Yii::t('app', 'Franchisee name'),
        'cafe_name' => Yii::t('app', 'Cafe name'),
        'email' => Yii::t('app', 'Your Email'),
        'languages' => Yii::t('app', 'Languages'),
        'language_ids' => Yii::t('app', 'Languages'),
        'params_id' => Yii::t('app', 'Params'),
        'currency' => Yii::t('app', 'Currency'),
        'count' => Yii::t('app', 'Payment period'),
        'phone' => Yii::t('app', 'Your phone'),
    ];
  }

  public function preparePayment()
  {
    $payment = new FranchiseePayments();
    if (empty($this->tariff)) {
      return false;
    }
    $payment->tariff_id = $this->tariff->id;
    $payment->count = $this->count;
    $payment->sum = $this->tariff->getPrice($this->count);

    $payment->data = [
        'email' => $this->email,
        'name' => $this->name,
        'language_ids' => $this->language_ids,
        'cafe_name' => $this->cafe_name,
        'currency' => $this->currency,
        'params_id' => $this->params_id,
        'phone' => $this->phone,
    ];

    $this->payment = $payment;
    return true;
  }

  public function getParam()
  {
    $params = CafeParams::find();

    if (!empty($this->params_id)) {
      $params->andWhere(['id' => $this->params_id]);
    }

    $params = $params->one();
    if ($params) {
      $this->params_id = $params->id;
    }

    return $params;
  }
}