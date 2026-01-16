<?php

use yii\db\Migration;

/**
 * Class m181210_123942_report_auto_send
 */
class m181210_123942_report_auto_send extends Migration
{

  public $tableName = '{{%report_auto_send}}';
  public $cafeTableName = '{{%cafe}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->createTable($this->tableName, [
        'id' => $this->primaryKey(),
        'email' => $this->string(255),
        'type' => $this->integer(),
        'cafe_id' => $this->integer(),
        'status' => $this->integer(),
    ], 'ENGINE InnoDB');

    $this->addForeignKey('FK-template_reports_autosend_to_cafe', $this->tableName, 'cafe_id', $this->cafeTableName, 'id', 'CASCADE', 'CASCADE');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropForeignKey('FK-template_reports_autosend_to_cafe', $this->tableName);

    $this->dropTable($this->tableName);
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m181210_123942_report_auto_send cannot be reverted.\n";

      return false;
  }
  */
}
