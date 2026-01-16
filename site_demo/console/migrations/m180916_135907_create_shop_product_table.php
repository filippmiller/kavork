<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shop_product`.
 */
class m180916_135907_create_shop_product_table extends Migration
{
	public $tableName = '{{%shop_product}}';
	public $categoryTableName = '{{%shop_category}}';
	public $supplierTableName = '{{%shop_supplier}}';
	public $cafeTableName = '{{%cafe}}';
	public $franchiseeTableName = '{{%franchisee}}';

	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
    $this->renameTable('shop_items',$this->tableName);

    $this->renameColumn($this->tableName,'cafe','cafe_id');
    $this->renameColumn($this->tableName,'supplier','supplier_id');
    $this->renameColumn($this->tableName,'category','category_id');
    $this->renameColumn($this->tableName,'name','title');
    $this->renameColumn($this->tableName,'img','image');
    $this->renameColumn($this->tableName,'stock','external_sale_available');
    $this->renameColumn($this->tableName,'max_stock','accounting_critical_minimum');
    $this->renameColumn($this->tableName,'has_vat','tax_required');

    $this->dropColumn($this->tableName,'user_id');
    $this->dropColumn($this->tableName,'period_star');
    $this->dropColumn($this->tableName,'period_end');

    $this->alterColumn($this->tableName,'title',$this->string()->notNull());

    $this->execute('UPDATE `shop_product` SET `image` = CONCAT(\'/img\',`image`) where LENGTH(`image`)>0;');
    $this->execute('UPDATE `shop_product` SET `supplier_id` = NULL WHERE LENGTH(`supplier_id`)>2 || LENGTH(`supplier_id`)=0 ');
    $this->execute('UPDATE `shop_product` SET `accounting_critical_minimum` = 0 WHERE accounting_critical_minimum is null');
    $this->alterColumn($this->tableName,'supplier_id',$this->integer()->null());
    $this->alterColumn($this->tableName,'barcode',$this->string()->null());
    $this->alterColumn($this->tableName,'external_sale_available',$this->boolean()->defaultValue(0));
    $this->alterColumn($this->tableName,'accounting_critical_minimum',$this->integer()->notNull()->defaultValue(0));
    $this->alterColumn($this->tableName,'tax_required',$this->boolean()->defaultValue(1));
    $this->alterColumn($this->tableName,'weight',$this->integer()->defaultValue(0));
    $this->alterColumn($this->tableName,'price',$this->float()->defaultValue(0));
    $this->alterColumn($this->tableName,'is_active',$this->boolean()->defaultValue(1));


    $this->addColumn($this->tableName,'franchisee_id',$this->integer()->notNull()->after('id'));


    $this->addColumn($this->tableName,'created_by',$this->integer()->defaultValue(0));
    $this->addColumn($this->tableName,'updated_by',$this->integer()->defaultValue(0));
    $this->addColumn($this->tableName,'updated_at',$this->dateTime());
    $this->addColumn($this->tableName,'created_at',$this->dateTime());

		/*$this->createTable($this->tableName, [
			'id'            => $this->primaryKey(),
			'franchisee_id' => $this->integer()->notNull(),
			'cafe_id'       => $this->integer()->null(),
			'supplier_id'   => $this->integer()->null(),
			'category_id'   => $this->integer()->null(),
			'title'         => $this->string()->notNull(),
			'description'   => $this->text(),
			'image'         => $this->text(),

			'barcode'       => $this->string()->null(),

			'external_sale_available' => $this->boolean()->defaultValue(0),

			'accounting_critical_minimum' => $this->integer()->notNull()->defaultValue(0),

			'tax_required' => $this->boolean()->defaultValue(1),

			'weight' => $this->integer()->defaultValue(0),

			'price'          => $this->float()->defaultValue(0),
			'is_active'      => $this->boolean()->defaultValue(1),

			'created_by' => $this->integer()->defaultValue(0),
			'updated_by' => $this->integer()->defaultValue(0),
			'updated_at' => $this->dateTime(),
			'created_at' => $this->dateTime(),
		], 'ENGINE InnoDB');*/

    $this->execute('UPDATE `shop_product` SET `category_id` = NULL');
    $this->execute('UPDATE `shop_product` SET `franchisee_id` = \'1\'');

		$this->addForeignKey('FK-product-to-category', $this->tableName, 'category_id', $this->categoryTableName, 'id', 'RESTRICT', 'CASCADE');
		$this->addForeignKey('FK-product-to-supplier', $this->tableName, 'supplier_id', $this->supplierTableName, 'id', 'RESTRICT', 'CASCADE');
		$this->addForeignKey('FK-product-to-cafe', $this->tableName, 'cafe_id', $this->cafeTableName, 'id', 'SET NULL', 'CASCADE');
		$this->addForeignKey('FK-product-to-franchisee', $this->tableName, 'franchisee_id', $this->franchiseeTableName, 'id', 'RESTRICT', 'CASCADE');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropForeignKey('FK-product-to-franchisee', $this->tableName);
		$this->dropForeignKey('FK-product-to-cafe', $this->tableName);
		$this->dropForeignKey('FK-product-to-supplier', $this->tableName);
		$this->dropForeignKey('FK-product-to-category', $this->tableName);

		$this->dropTable($this->tableName);
	}
}
