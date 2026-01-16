<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 19.10.18
 * Time: 16:45
 */

namespace frontend\modules\selfservice\models;

use frontend\modules\visits\models\VisitorLog;
use Yii;
use yii\base\Model;

class SelfServiceExistingUser extends Model
{
  public $name;
  public $visitor_id;

  public $guest_m = 0;
  public $guest_chi = 0;

  public $visit;

  public function rules()
  {
    return [
        [['name'], 'string', 'max' => 255],

        [['visitor_id'], 'required', 'message' => Yii::t('app', 'Fill in required fields')],
        [['visitor_id'], 'integer'],
        [['visitor_id'], 'validateVisitor'],

        [['guest_m', 'guest_chi'], 'integer'],
        [['guest_m', 'guest_chi'], 'default', 'value' => 0],
    ];
  }

  public function validateVisitor($attribute, $params, $validator)
  {
    $exists = VisitorLog::find()->where([
        'visitor_id' => $this->{$attribute},
        'finish_time' => null,
    ])->exists();

    if ($exists) {
      $this->addError($attribute, Yii::t('app', 'User already in cafe'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'name' => Yii::t('app', 'Name and surname'),
    ];
  }

  public function start()
  {
    if (!$this->validate()) {
      return false;
    }

    $transaction = Yii::$app->db->beginTransaction();
    try {
      $visit = new VisitorLog();

      $visit->type = VisitorLog::TYPE_REGULAR;

      $visit->visitor_id = $this->visitor_id;
      $visit->cafe_id = Yii::$app->cafe->getId();
      $visit->user_id = Yii::$app->user->id;

      $visit->guest_m = $this->guest_m;
      $visit->guest_chi = $this->guest_chi;

      if ($visit->save()) {
        $transaction->commit();

        $this->visit = $visit;

        return true;
      }

      $transaction->rollBack();

    } catch (\Exception $exception) {
      $transaction->rollBack();
    }

    return false;
  }
}