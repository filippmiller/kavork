<?php

use yii\db\Migration;
use frontend\models\Transaction;

/**
 * Class m191202_170725_fix_delate_shop_transaction
 */
class m191202_170725_fix_delate_shop_transaction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
      $transictions = Transaction::find()
          ->leftJoin('shop_sale','`transaction`.sale_id = shop_sale.id')
          ->where('shop_sale.id is null and `transaction`.sale_id is not null')
          ->all();
      foreach ($transictions as $transiction){
        $transiction->delete();
      }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191202_170725_fix_delate_shop_transaction cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191202_170725_fix_delate_shop_transaction cannot be reverted.\n";

        return false;
    }
    */
}
