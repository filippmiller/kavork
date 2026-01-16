<?php

namespace frontend\modules\templates\models;

use frontend\modules\cafe\models\Cafe;
use Yii;

/**
 * This is the model class for table "{{%template_to_cafe}}".
 *
 * @property int $template_id
 * @property int $cafe_id
 * @property int $type_id
 *
 * @property Cafe $cafe
 * @property Template $template
 */
class TemplateToCafe extends \common\components\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return '{{%template_to_cafe}}';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['template_id', 'cafe_id', 'type_id'], 'required'],
        [['template_id', 'cafe_id', 'type_id'], 'integer'],
        [['template_id', 'cafe_id', 'type_id'], 'unique', 'targetAttribute' => ['template_id', 'cafe_id', 'type_id']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'template_id' => Yii::t('app', 'Template ID'),
        'cafe_id' => Yii::t('app', 'Cafe ID'),
        'type_id' => Yii::t('app', 'Type ID'),
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
  public function getTemplate()
  {
    return $this->hasOne(Template::className(), ['id' => 'template_id']);
  }
}
