<?php

namespace frontend\modules\shop\models;

use frontend\modules\cafe\models\Cafe;
use Yii;

/**
 * This is the model class for table "{{%shop_warehouse}}".
 *
 * @property int $cafe_id
 * @property int $product_id
 * @property int $quantity
 *
 * @property ShopProduct $product
 */
class ShopWarehouse extends \common\components\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return '{{%shop_warehouse}}';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['cafe_id', 'product_id'], 'required'],
        [['cafe_id', 'product_id', 'quantity'], 'integer'],
        [['cafe_id', 'product_id'], 'unique', 'targetAttribute' => ['cafe_id', 'product_id']],
        [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['product_id' => 'id']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'cafe_id' => Yii::t('app', 'Cafe ID'),
        'product_id' => Yii::t('app', 'Product ID'),
        'quantity' => Yii::t('app', 'Quantity'),
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getProduct()
  {
    return $this->hasOne(ShopProduct::className(), ['id' => 'product_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCafe()
  {
    return $this->hasOne(Cafe::className(), ['id' => 'cafe_id']);
  }
}
