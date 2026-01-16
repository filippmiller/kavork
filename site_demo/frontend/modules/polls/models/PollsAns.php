<?php

namespace frontend\modules\polls\models;

use frontend\modules\visitor\models\Visitor;
use Yii;

/**
 * This is the model class for table "polls_ans".
 *
 * @property int $id
 * @property int $ans
 * @property string $txt
 * @property int $visitor_id
 * @property int $poll_id
 * @property string $created
 *
 * @property Visitor $visitor
 * @property Polls $poll
 */
class PollsAns extends \common\components\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'polls_ans';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['ans', 'visitor_id', 'poll_id'], 'integer'],
        [['txt'], 'string'],
        [['poll_id', 'ans'], 'required'],
        [['created'], 'safe'],
        [['visitor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Visitor::className(), 'targetAttribute' => ['visitor_id' => 'id']],
        [['poll_id'], 'exist', 'skipOnError' => true, 'targetClass' => Polls::className(), 'targetAttribute' => ['poll_id' => 'id']],
        ['txt', 'required', 'when' => function ($model) {
          return $model->ans == -1;
        }, 'whenClient' => "function (attribute, value) {
            return $('#pollsans-ans input:checked').val() == -1;
        }"]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'id' => Yii::t('app', 'ID'),
        'ans' => Yii::t('app', 'Answer'),
        'txt' => Yii::t('app', 'Other answer'),
        'visitor_id' => Yii::t('app', 'Visitor ID'),
        'poll_id' => Yii::t('app', 'Poll ID'),
        'created' => Yii::t('app', 'Created'),
    ];
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
  public function getPoll()
  {
    return $this
        ->hasOne(Polls::className(), ['id' => 'poll_id'])
        ->where(['cafe_id' => Yii::$app->cafe->id]);
  }

  public function getQuestion()
  {
    $poll = $this->poll;
    if (!$poll) return false;

    return $poll->question;
  }

  public function getAllowANS($show_other = false)
  {
    $poll = $this->poll;

    $answers = $poll->answers;

    if ($show_other && $poll->other_ans) {
      $answers['-1'] = Yii::t('app', 'Other answer');
    }

    $out = [];
    foreach ($answers as $k => $label) {
      if (is_array($label)) {
        if (isset($label['id'])) $k = $label['id'];
        if (isset($label['answers'])) $label = $label['answers'];
      }
      //if($this->ans === null )$this->ans = $k;
      $out[$k] = $label;
    }

    return $out;
  }
}
