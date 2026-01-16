<?php

use yii\db\Migration;

/**
 * Class m180819_114229_alter_cafe_params_table
 */
class m180819_114229_alter_cafe_params_table extends Migration
{
  public $tableName = '{{%cafe_params}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->dropColumn($this->tableName, 'show_sum');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->addColumn($this->tableName, 'show_sum', $this->integer()->defaultValue(1)->after('vat_list'));
  }
}
