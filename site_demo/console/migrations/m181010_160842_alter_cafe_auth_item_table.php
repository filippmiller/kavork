<?php

use yii\db\Migration;

/**
 * Class m181010_160842_alter_cafe_auth_item_table
 */
class m181010_160842_alter_cafe_auth_item_table extends Migration
{
	public $tableName = '{{%cafe_auth_item}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn($this->tableName, 'parent', $this->string(64)->null()->after('name'));
	    $this->addForeignKey('FK-parent', $this->tableName, 'parent', $this->tableName, 'name', 'SET NULL', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
	    $this->dropForeignKey('FK-parent', $this->tableName);

	    $this->dropColumn($this->tableName, 'parent');
    }
}
