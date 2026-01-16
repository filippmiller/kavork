<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.10.18
 * Time: 11:33
 */

namespace frontend\modules\shop\models;

use common\components\ActiveRecord;
use frontend\modules\users\models\Users;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

class ShopBaseModel extends ActiveRecord
{
  public function behaviors()
  {
    $behaviors = parent::behaviors();

    if (self::tableName() !== '{{%shop_base_model}}') {
      if ($this->hasAttribute('created_at') || $this->hasAttribute('updated_at')) {
        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => $this->hasAttribute('created_at') ? 'created_at' : false,
            'updatedAtAttribute' => $this->hasAttribute('updated_at') ? 'updated_at' : false,
            'value' => date('Y-m-d H:i:s'),
        ];
      }

      if ($this->hasAttribute('created_by') || $this->hasAttribute('updated_by')) {

        if (isset(Yii::$app->user) && !Yii::$app->user->getIsGuest()) {
          $behaviors['blameable'] = [
              'class' => BlameableBehavior::class,
              'createdByAttribute' => $this->hasAttribute('created_by') ? 'created_by' : false,
              'updatedByAttribute' => $this->hasAttribute('updated_by') ? 'updated_by' : false,
          ];
        }
      }
    }

    return $behaviors;
  }

  public static function getExtendedCafeList()
  {
    $result = [];
    $items = Users::getCafesList();

    foreach ($items as $item) {
      $franchisee_id = $item['franchisee_id'] * -1;
      $franchisee_name = $item['franchisee_name'];

      if (!isset($result[$franchisee_name])) {
        $result[$franchisee_name][$franchisee_id] = ' --- ' . Yii::t('main', 'For all cafes of "{0}"', $franchisee_name) . ' --- ';
      }

      $result[$franchisee_name][$item['id']] = $item['name'];
    }

    return $result;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUpdatedBy()
  {
    return $this->hasOne(Users::className(), ['id' => 'updated_by']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCreatedBy()
  {
    return $this->hasOne(Users::className(), ['id' => 'created_by']);
  }
}