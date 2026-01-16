<?php

use yii\db\Migration;

/**
 * Class m180820_151435_alter_cafe_params_table
 */
class m180820_151435_alter_cafe_params_table extends Migration
{
  public $tableName = '{{%cafe_params}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->alterColumn($this->tableName, 'vat_list', $this->text());
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->alterColumn($this->tableName, 'vat_list', $this->string(300));
  }
}
