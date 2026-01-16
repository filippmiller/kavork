<?php

use yii\db\Migration;

/**
 * Class m190218_094649_product_in_stock
 */
class m190218_094649_product_in_stock extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->addColumn('shop_product','in_stock',$this->integer(0)->defaultValue(0)->null());
    //$this->execute('UPDATE `shop_product` SET `in_stock` = \'0\'');
    //$this->execute('ALTER TABLE `shop_product` CHANGE `in_stock` `in_stock` INT(1) NULL DEFAULT \'0\';');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m190218_094649_product_in_stock cannot be reverted.\n";

    return false;
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m190218_094649_product_in_stock cannot be reverted.\n";

      return false;
  }
  */
}
