<?php

namespace frontend\modules\mails\models;

use frontend\modules\cafe\models\Cafe;
use frontend\modules\users\models\UserCafe;
use frontend\modules\users\models\Users;
use Yii;

/**
 * This is the model class for table "mails_log".
 *
 * @property int $id
 * @property string $name
 * @property string $cteated_at
 * @property int $last_visitor_id
 * @property int $mail_id
 * @property int $user_id
 * @property int $cafe_id
 * @property int $count
 * @property string $content
 * @property string $params
 *
 * @property Cafe $cafe
 * @property TemplateMails $mail
 * @property User $user
 */
class MailsLog extends \common\components\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'mails_log';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['cteated_at'], 'safe'],
        [['last_visitor_id', 'mail_id', 'user_id', 'cafe_id', 'count'], 'integer'],
        [['content', 'params'], 'string'],
        [['name'], 'string', 'max' => 255],
        [['cafe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cafe::className(), 'targetAttribute' => ['cafe_id' => 'id']],
        [['mail_id'], 'exist', 'skipOnError' => true, 'targetClass' => TemplateMail::className(), 'targetAttribute' => ['mail_id' => 'id']],
        [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserCafe::className(), 'targetAttribute' => ['user_id' => 'id']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'id' => Yii::t('app', 'ID'),
        'name' => Yii::t('app', 'Name'),
        'cteated_at' => Yii::t('app', 'Cteated At'),
        'last_visitor_id' => Yii::t('app', 'Last Visitor ID'),
        'mail_id' => Yii::t('app', 'Mail'),
        'user_id' => Yii::t('app', 'User'),
        'cafe_id' => Yii::t('app', 'Cafe'),
        'status' => Yii::t('app', 'Status'),
        'count' => Yii::t('app', 'Count'),
        'content' => Yii::t('app', 'Content'),
        'params' => Yii::t('app', 'Params'),
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
  public function getMail()
  {
    return $this->hasOne(TemplateMail::className(), ['id' => 'mail_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(Users::className(), ['id' => 'user_id']);
  }

  public static function getStatus($value = false)
  {
    $data = [
        0 => Yii::t('app', "Wait"),
        1 => Yii::t('app', "In processed"),
        2 => Yii::t('app', "Finish"),
    ];
    if ($value === false) return $data;

    return isset($data[$value]) ? $data[$value] : '-';
  }

  public function setTemplate($template)
  {
    $this->name = $template->title;
    $this->mail_id = $template->id;
    $content = json_decode($template->content, true);
    $content['width'] = $template->width;
    $content['background'] = $template->background;
    $this->content = json_encode($content);
  }
}
