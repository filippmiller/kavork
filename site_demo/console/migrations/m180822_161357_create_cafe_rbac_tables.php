<?php

use yii\db\Migration;

/**
 * Class m180822_161357_create_cafe_rbac_tables
 */
class m180822_161357_create_cafe_rbac_tables extends Migration
{
	public $itemTableName = '{{%cafe_auth_item}}';
	public $assignmentTableName = '{{%cafe_auth_assignment}}';
	public $cafeTableName = '{{%cafe}}';

	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable($this->itemTableName, [
			'name'        => $this->string(64)->notNull(),
			'description' => $this->text(),
			'created_at'  => $this->integer(),
			'updated_at'  => $this->integer(),
			'PRIMARY KEY ([[name]])',
		], 'ENGINE InnoDB');

		$this->createTable($this->assignmentTableName, [
			'item_name'  => $this->string(64)->notNull(),
			'cafe_id'    => $this->integer()->notNull(),
			'created_at' => $this->integer(),
			'PRIMARY KEY ([[item_name]], [[cafe_id]])',
			'FOREIGN KEY ([[item_name]]) REFERENCES ' . $this->itemTableName . ' ([[name]]) ON DELETE CASCADE ON UPDATE CASCADE',
		], 'ENGINE InnoDB');

		$this->addForeignKey('FK-cafe_auth_assignment-to-cafe', $this->assignmentTableName, 'cafe_id', $this->cafeTableName, 'id', 'CASCADE', 'CASCADE');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropTable($this->assignmentTableName);
		$this->dropTable($this->itemTableName);
	}
}
