<?php

namespace frontend\modules\timetable\models;

use edofre\fullcalendar\models\Event;
use frontend\modules\cafe\models\Cafe;
use frontend\modules\users\models\Users;
use Yii;

/**
 * This is the model class for table "user_timetable".
 *
 * @property int $id
 * @property int $user_id
 * @property string $start
 * @property string $end
 * @property int $cafe_id
 *
 * @property Cafe $cafe
 * @property User $user
 */
class UserTimetable extends \common\components\ActiveRecord
{

  public $event_id;

  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'user_timetable';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['user_id', 'cafe_id'], 'integer'],
        [['start', 'end'], 'safe'],
        [['cafe_id'], 'required'],
        [['cafe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cafe::className(), 'targetAttribute' => ['cafe_id' => 'id']],
        [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'id']],
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
        'start' => Yii::t('app', 'Start'),
        'end' => Yii::t('app', 'End'),
        'cafe_id' => Yii::t('app', 'Cafe ID'),
        'title' => Yii::t('app', 'User Timetables'),
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
  public function getUser()
  {
    return $this->hasOne(Users::className(), ['id' => 'user_id']);
  }

  public function getEvent()
  {
    //Testing

    $Event = new Event();
    $Event->id = $this->id;
    $Event->title = $this->user->name;
    $Event->color = !empty($this->user->color) ? $this->user->color : '#000';
    $Event->start = date('Y-m-d\TH:i:s\Z', strtotime($this->start));
    $Event->end = date('Y-m-d\TH:i:s\Z', strtotime($this->end));

    $Event = $Event->toArray();

    if (!empty($this->event_id)) {
      $Event['_id'] = $this->event_id;
    }
    return $Event;
  }
}
