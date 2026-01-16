<?php

namespace frontend\modules\users\models;

use Yii;

/**
 * This is the model class for table "user_log".
 *
 * @property int $id
 * @property int $user_id
 * @property string $ses_id
 * @property int $start
 * @property int $finish
 * @property int $cafe
 *
 * @property Cafe $cafe0
 * @property User $user
 */
class AdminSession extends UserLog
{
  const SCENARIO_STOP = 'stop';
  const SCENARIO_START = 'start';

  public $password;
  private $_user;

  public function attributeLabels()
  {
    return [
        'user_id' => Yii::t('app', 'User ID'),
        'password' => Yii::t('app', 'Password'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    $passwordWhenStart = function ($model) {
      if (Yii::$app->cafe->can('sessionStartPasswordRequest')) {
        return true;
      }

      return false;
    };

    $passwordWhenStop = function ($model) {
      if (Yii::$app->user->can('UserLogSessionStopWithoutPassword')) {
        return false;
      }

      if (Yii::$app->cafe->can('sessionStopPasswordRequest')) {
        return true;
      }

      return false;
    };

    return [
        [['user_id'], 'number'],
        [['user_id'], 'integer'],
        [['user_id'], 'validateUser'],

        ['password', 'string', 'min' => 6, 'on' => self::SCENARIO_START, 'when' => $passwordWhenStart],
        ['password', 'validatePassword', 'on' => self::SCENARIO_START, 'when' => $passwordWhenStart],
        ['password', 'required', 'on' => self::SCENARIO_START, 'when' => $passwordWhenStart],

        ['password', 'string', 'min' => 6, 'on' => self::SCENARIO_STOP, 'when' => $passwordWhenStop],
        ['password', 'validatePassword', 'on' => self::SCENARIO_STOP, 'when' => $passwordWhenStop],
        ['password', 'required', 'on' => self::SCENARIO_STOP, 'when' => $passwordWhenStop],
    ];
  }

  /**
   * Validates the password.
   * This method serves as the inline validation for password.
   *
   * @param string $attribute the attribute currently being validated
   * @param array $params the additional name-value pairs given in the rule
   */
  public function validateUser($attribute, $params)
  {
    $user = $this->getUser();
    if (!$user) {
      $this->addError($attribute, Yii::t('app', 'User not found.'));
    }


    if ($this->isNewRecord) {
      $inCafe = UserLog::find()
          ->where(['user_id' => $user->id, 'finish' => null])
          ->one();
      if (count($inCafe)) {
        $cafe = $inCafe->getCafe()->asArray()->one();
        $this->addError($attribute, Yii::t('app', 'User already in a cafe {name}', $cafe));
      }
    }
  }

  public function validatePassword($attribute, $params)
  {
    if (!$this->hasErrors()) {
      $user = $this->getUser();
      if (!$user || !$user->validatePassword($this->password)) {
        $this->addError($attribute, Yii::t('app', 'Incorrect password.'));
      }
    }
  }

  /**
   * Finds user by [[username]]
   *
   * @return User|null
   */
  public function getUser()
  {
    if ($this->_user === null) {
      $this->_user = Users::find()
          ->where(['id' => $this->user_id])
          ->one();
    }

    return $this->_user;
  }
}