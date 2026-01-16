<?php

namespace frontend\modules\cafe\models;

use Yii;

/**
 * This is the model class for table "{{%cafe_auth_assignment}}".
 *
 * @property string $item_name
 * @property int $cafe_id
 * @property int $created_at
 *
 * @property Cafe $cafe
 * @property CafeAuthItem $itemName
 */
class CafeAuthAssignment extends \common\components\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return '{{%cafe_auth_assignment}}';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['item_name', 'cafe_id'], 'required'],
        [['cafe_id', 'created_at'], 'integer'],
        [['item_name'], 'string', 'max' => 64],
        [['item_name', 'cafe_id'], 'unique', 'targetAttribute' => ['item_name', 'cafe_id']],
        [['cafe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cafe::className(), 'targetAttribute' => ['cafe_id' => 'id']],
        [['item_name'], 'exist', 'skipOnError' => true, 'targetClass' => CafeAuthItem::className(), 'targetAttribute' => ['item_name' => 'name']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'item_name' => Yii::t('app', 'Item Name'),
        'cafe_id' => Yii::t('app', 'Cafe ID'),
        'created_at' => Yii::t('app', 'Created At'),
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
  public function getItemName()
  {
    return $this->hasOne(CafeAuthItem::className(), ['name' => 'item_name']);
  }
}
