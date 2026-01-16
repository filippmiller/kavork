<?php

use yii\db\Migration;

/**
 * Class m180829_143312_alter_visitor_log
 */
class m180829_143312_alter_visitor_log extends Migration
{
  public $tableName = '{{%visitor_log}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->renameColumn($this->tableName, 'chi', 'is_child');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->renameColumn($this->tableName, 'is_child', 'chi');
  }
}
