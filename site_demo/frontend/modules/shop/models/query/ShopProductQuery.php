<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 29.09.18
 * Time: 16:49
 */

namespace frontend\modules\shop\models\query;

use Yii;

/**
 * This is the ShopProduct ActiveQuery.
 *
 */
class ShopProductQuery extends \yii\db\ActiveQuery
{
  public function active()
  {
    return $this->andWhere(['is_active' => 1]);
  }

  public function inShop()
  {
    return $this->andWhere(['external_sale_available' => 1]);
  }

  public function inCafe()
  {
    $cafe = Yii::$app->cafe;

    return $this->andWhere('[[franchisee_id]] = :franchisee_id AND ([[cafe_id]] IS NULL OR [[cafe_id]] = :cafe_id)',
        [
            ':franchisee_id' => $cafe->getFranchiseeId(),
            ':cafe_id' => $cafe->getId(),
        ]
    );
  }

  /**
   * @inheritdoc
   */
  public function all($db = null)
  {
    return parent::all($db);
  }

  /**
   * @inheritdoc
   */
  public function one($db = null)
  {
    return parent::one($db);
  }
}