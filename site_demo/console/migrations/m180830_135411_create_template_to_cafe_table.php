<?php

use yii\db\Migration;

/**
 * Handles the creation of table `template_to_cafe`.
 */
class m180830_135411_create_template_to_cafe_table extends Migration
{
	public $tableName = '{{%template_to_cafe}}';
	public $templateTableName = '{{%template}}';
	public $cafeTableName = '{{%cafe}}';

	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable($this->tableName, [
			'template_id' => $this->integer()->notNull(),
			'cafe_id'     => $this->integer()->notNull(),
			'type_id'     => $this->integer()->notNull(),
		], 'ENGINE InnoDB');

		$this->addPrimaryKey('PK-template_id-cafe_id', $this->tableName, ['template_id', 'cafe_id']);

		$this->addForeignKey('FK-template_to_cafe-to-template', $this->tableName, 'template_id', $this->templateTableName, 'id', 'CASCADE', 'CASCADE');
		$this->addForeignKey('FK-template_to_cafe-to-cafe', $this->tableName, 'cafe_id', $this->cafeTableName, 'id', 'CASCADE', 'CASCADE');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropForeignKey('FK-template_to_cafe-to-template', $this->tableName);
		$this->dropForeignKey('FK-template_to_cafe-to-cafe', $this->tableName);

		$this->dropTable($this->tableName);
	}
}
