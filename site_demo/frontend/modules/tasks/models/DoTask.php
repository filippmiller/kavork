<?php

namespace frontend\modules\tasks\models;

use frontend\modules\cafe\models\Cafe;
use frontend\modules\users\models\Users;
use Yii;

/**
 * This is the model class for table "do_task".
 *
 * @property int $id
 * @property int $cafe_id
 * @property string $datetime
 * @property int $status
 * @property int $task_id
 * @property string $closedate
 * @property string $comment
 * @property string $text
 * @property int $user_id
 *
 * @property Cafe $cafe
 * @property Task $task
 * @property User $user
 */
class DoTask extends \common\components\ActiveRecord
{

  const STATUS_SNOOZE = -1;
  const STATUS_IN_WORK = 0;
  const STATUS_COMPLITED = 1;
  const STATUS_FAILED = 2;
  const STATUS_TIME_UP = 3;


  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'do_task';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['cafe_id', 'status', 'task_id', 'user_id'], 'integer'],
        [['datetime'], 'required'],
        [['datetime', 'closedate'], 'safe'],
        [['comment', 'text'], 'string'],
        [['cafe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cafe::className(), 'targetAttribute' => ['cafe_id' => 'id']],
        [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::className(), 'targetAttribute' => ['task_id' => 'id']],
        [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'id']],
        ['comment', 'required', 'when' => function ($model) {
          return $model->status == self::STATUS_FAILED;
        }],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'id' => Yii::t('app', 'ID'),
        'cafe_id' => Yii::t('app', 'Cafe ID'),
        'datetime' => Yii::t('app', 'Datetime'),
        'status' => Yii::t('app', 'Status'),
        'task_id' => Yii::t('app', 'Task ID'),
        'closedate' => Yii::t('app', 'Closedate'),
        'comment' => Yii::t('app', 'Comment'),
        'user_id' => Yii::t('app', 'User ID'),
    ];
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
  public function getTask()
  {
    return $this->hasOne(Task::className(), ['id' => 'task_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(Users::className(), ['id' => 'user_id']);
  }

  public static function getStatus($index = null)
  {

    $labels = [
        //self::STATUS_SNOOZE => Yii::t('app', 'snooze'),
        self::STATUS_IN_WORK => Yii::t('app', 'In work'),
        self::STATUS_COMPLITED => Yii::t('app', 'Completed'),
        self::STATUS_FAILED => Yii::t('app', 'Failed'),
        self::STATUS_TIME_UP => Yii::t('app', 'Time is up'),
    ];

    if ($index !== null) {
      return isset($labels[$index]) ? $labels[$index] : Yii::t('app', 'Unknown');
    } else {
      return $labels;
    }
  }
}
