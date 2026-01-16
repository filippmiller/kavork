<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 19.10.18
 * Time: 16:45
 */

namespace frontend\modules\selfservice\models;

use frontend\modules\visits\models\StartVisit;
use frontend\modules\visits\models\VisitorLog;
use Yii;
use yii\base\Model;

class SelfServiceNewUser extends Model
{
  public $first_name;
  public $last_name;
  public $email;

  public $guest_m = 0;
  public $guest_chi = 0;

  public $visitor;
  public $visit;

  public function rules()
  {
    return [
      // Step 1
        [['first_name'], 'required', 'message' => Yii::t('app', 'Fill in required fields')],
        [['first_name'], 'string', 'max' => 32],

      // Step 2
        [['last_name'], 'string', 'max' => 32],

      // Step 3
        [['email'], 'email'],

      // Step 4
        [['guest_m', 'guest_chi'], 'integer'],
        [['guest_m', 'guest_chi'], 'default', 'value' => 0],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'first_name' => Yii::t('app', 'First name'),
        'last_name' => Yii::t('app', 'Last name'),
        'email' => Yii::t('app', 'Email'),
    ];
  }

  public function start()
  {
    if (!$this->validate()) {
      return false;
    }

    $transaction = Yii::$app->db->beginTransaction();
    try {
      $visitor = new StartVisit();
      $visit = new VisitorLog();

      $visitor->type = VisitorLog::TYPE_NEW;
      $visit->type = VisitorLog::TYPE_NEW;

      if (!empty($this->first_name)) {
        $visitor->f_name = $this->first_name;
      }

      if (!empty($this->last_name)) {
        $visitor->l_name = $this->last_name;
      }

      if (!empty($this->email)) {
        $visitor->email = $this->email;
      }

      if ($visitor->save()) {
        $visit->visitor_id = $visitor->id;
        $visit->cafe_id = Yii::$app->cafe->getId();
        $visit->user_id = Yii::$app->user->id;

        $visit->guest_m = $this->guest_m;
        $visit->guest_chi = $this->guest_chi;

        if ($visit->save()) {
          $transaction->commit();

          $this->visitor = $visitor;
          $this->visit = $visit;

          return true;
        }
      }

      $transaction->rollBack();

    } catch (\Exception $exception) {
      $transaction->rollBack();
    }

    return false;
  }
}