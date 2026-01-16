<?php

use yii\db\Migration;
use frontend\models\Transaction;
use frontend\modules\shop\models\ShopSale;

/**
 * Class m191201_144255_fix_anonimus_shop_transaction
 */
class m191201_144255_fix_anonimus_shop_transaction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
      $sales = ShopSale::find()
          ->leftJoin('transaction','`transaction`.sale_id = shop_sale.id')
          ->where('shop_sale.visitor_id is null and `transaction`.id is null and pay_state is not null')
          ->all();
      foreach ($sales as $sale){
        $sale->updateTransaction($sale->created_at);
      }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191201_144255_fix_anonimus_shop_transaction cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191201_144255_fix_anonimus_shop_transaction cannot be reverted.\n";

        return false;
    }
    */
}
