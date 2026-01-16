<?php

use yii\db\Migration;

/**
 * Class m180916_114853_drop_old_tables
 */
class m180918_114853_drop_old_tables extends Migration
{
  public $tables = [
      'goods_transit', //перенос в shop_transaction
      'order_list', //перенос в shop_transaction
      'sales', //пустая
      //'shop_category',+
      //'shop_items',+
      //'suppliers',+
  ];

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    foreach ($this->tables as $tableName) {
      $tableName = $this->db->tablePrefix . $tableName;
      if ($this->db->getTableSchema($tableName, true) !== null) {
        $this->dropTable($tableName);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    return true;
  }
}
