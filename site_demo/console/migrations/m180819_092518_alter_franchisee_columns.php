<?php

use yii\db\Migration;

/**
 * Class m180819_093249_alter_franchisee_columns
 */
class m180819_092518_alter_franchisee_columns extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn('{{%visitor}}', 'franchisee_id', $this->integer()->null()->after('id'));
		$this->renameColumn('{{%cafe}}', 'franchisee', 'franchisee_id');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('{{%visitor}}', 'franchisee_id');
		$this->renameColumn('{{%user}}', 'franchisee_id', 'franchisee');
		$this->renameColumn('{{%cafe}}', 'franchisee_id', 'franchisee');
	}
}
