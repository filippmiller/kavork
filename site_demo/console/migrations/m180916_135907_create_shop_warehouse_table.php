<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shop_warehouse`.
 */
class m180916_135907_create_shop_warehouse_table extends Migration
{
	public $tableName = '{{%shop_warehouse}}';
	public $productTableName = '{{%shop_product}}';
	public $cafeTableName = '{{%cafe}}';

	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable($this->tableName, [
			'cafe_id'    => $this->integer()->notNull(),
			'product_id' => $this->integer()->notNull(),
			'quantity'   => $this->integer()->notNull()->defaultValue(0),
		], 'ENGINE InnoDB');

    $this->execute('INSERT INTO shop_warehouse (cafe_id, product_id, quantity)
      SELECT cafe_id,id,in_stock FROM `shop_product` where in_stock>0
    ');

		$this->dropColumn('shop_product','in_stock');
		$this->addPrimaryKey('PK-cafe-product', $this->tableName, ['cafe_id', 'product_id']);

		$this->addForeignKey('FK-warehouse-to-product', $this->tableName, 'product_id', $this->productTableName, 'id', 'CASCADE', 'CASCADE');
		$this->addForeignKey('FK-warehouse-to-cafe', $this->tableName, 'cafe_id', $this->cafeTableName, 'id', 'CASCADE', 'CASCADE');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropForeignKey('FK-warehouse-to-product', $this->tableName);

		$this->dropTable($this->tableName);
	}
}
