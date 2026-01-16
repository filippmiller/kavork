<?php

namespace frontend\modules\visits\models;

use frontend\modules\visitor\models\Visitor;
use Yii;

class StartVisit extends Visitor
{
  public $type = false;

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'id' => Yii::t('app', 'ID'),
        'f_name' => Yii::t('app', 'F Name'),
        'l_name' => Yii::t('app', 'L Name'),
        'code' => Yii::t('app', 'Code'),
        'email' => Yii::t('app', 'Email'),
        'phone' => Yii::t('app', 'Phone'),
        'lg' => Yii::t('app', 'lg'),
        'type' => Yii::t('app', 'visitor type'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['f_name', 'l_name', 'code', 'email', 'phone', 'lg'], 'trim'],
        [['type'], 'required'],
        [['f_name', 'l_name', 'code', 'email', 'phone', 'lg'], 'string'],
        [['id', 'type'], 'integer'],
        [['email'], 'email'],

        [['code'], 'validateUniqueInFranchisee'
          , 'when' => function ($model) {
          return $model->type == VisitorLog::TYPE_NEW || $model->type == VisitorLog::TYPE_REGULAR;
        }],

        [['f_name'], "required", 'when' => function ($model) {
          return $model->type != VisitorLog::TYPE_ANONYMOUS;
        }, 'whenClient' => "function (attribute, value) {
            return $('#startvisit-type input:checked').val() != 0;
        }"],

        [['f_name'], 'validateCheckRunningVisit', 'when' => function ($model) {
          return $model->type != VisitorLog::TYPE_ANONYMOUS;
        }, 'whenClient' => "function (attribute, value) {
            return $('#startvisit-type input:checked').val() != 0;
        }"],
    ];
  }

  /**
   * Check if User is already in Cafe
   */
  public function validateCheckRunningVisit($attribute, $params)
  {
    if ($this->id) {
      $exists = VisitorLog::find()
          ->andWhere(['visitor_id' => $this->id])
          ->andWhere(['finish_time' => null])
          ->exists();

      if ($exists) {
        $this->addError($attribute, Yii::t('app', "User is already in Cafe", [
            'attribute' => $attribute,
            'value' => $this->$attribute,
        ]));
      }
    }
  }

  /**
   * Check for uniques in current Franchisee
   */
  public function validateUniqueInFranchisee($attribute, $params)
  {
    $exists = self::find()
        ->andWhere(['!=', 'id', $this->id])
        ->andWhere([$attribute => $this->$attribute])
        ->andWhere(['franchisee_id' => Yii::$app->cafe->getFranchiseeId()])
        ->exists();

    if ($exists) {
      $this->addError($attribute, Yii::t('app', "There is already a user with this {attribute}", [
          'attribute' => $attribute,
          'value' => $this->$attribute,
      ]));
    }
  }
}
