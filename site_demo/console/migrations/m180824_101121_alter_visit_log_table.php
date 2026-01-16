<?php

use yii\db\Migration;

/**
 * Class m180824_101121_alter_visit_log_table
 */
class m180824_101121_alter_visit_log_table extends Migration
{
  public $tableName = '{{%visitor_log}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->alterColumn($this->tableName, 'pay_man', $this->integer()->null());
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->alterColumn($this->tableName, 'pay_man', $this->text());
  }
}
