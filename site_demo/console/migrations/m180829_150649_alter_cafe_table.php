<?php

use yii\db\Migration;

/**
 * Class m180829_150649_alter_cafe_table
 */
class m180829_150649_alter_cafe_table extends Migration
{
  public $tableName = '{{%cafe}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->addColumn($this->tableName, 'child_discount', $this->integer()->notNull()->defaultValue(0)->after('vat_code'));
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropColumn($this->tableName, 'child_discount');
  }

}
