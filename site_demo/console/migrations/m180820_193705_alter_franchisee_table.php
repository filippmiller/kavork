<?php

use yii\db\Migration;

/**
 * Class m180820_193705_alter_franchisee_table
 */
class m180820_193705_alter_franchisee_table extends Migration
{
  public $tableName = '{{%franchisee}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->addColumn($this->tableName, 'languages', $this->text()->after('roles'));

    // Set English for all franchisee
    $this->update($this->tableName, [
        'languages' => 'en-EN',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropColumn($this->tableName, 'languages');
  }

}
