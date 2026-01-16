<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shop_supplier`.
 */
class m180916_120548_create_shop_supplier_table extends Migration
{
	public $tableName = '{{%shop_supplier}}';

	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
	  $this->renameTable('suppliers',$this->tableName);
	  $this->renameColumn($this->tableName,'cafe','cafe_id');
	  $this->renameColumn($this->tableName,'name','title');
	  $this->alterColumn($this->tableName,'title',$this->string()->notNull());
    $this->addColumn($this->tableName,'franchisee_id',$this->integer()->notNull()->after('id'));
    $this->addColumn($this->tableName,'created_by',$this->integer()->defaultValue(0));
    $this->addColumn($this->tableName,'updated_by',$this->integer()->defaultValue(0));
    $this->addColumn($this->tableName,'updated_at',$this->dateTime());
    $this->addColumn($this->tableName,'created_at',$this->dateTime());

		/*$this->createTable($this->tableName, [
			'id'            => $this->primaryKey(),
			'franchisee_id' => $this->integer()->notNull(),
			'cafe_id'       => $this->integer()->null(),
			'title'         => $this->string()->notNull(),

			'created_by' => $this->integer()->defaultValue(0),
			'updated_by' => $this->integer()->defaultValue(0),
			'updated_at' => $this->dateTime(),
			'created_at' => $this->dateTime(),
		], 'ENGINE InnoDB');*/
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropTable($this->tableName);
	}
}
