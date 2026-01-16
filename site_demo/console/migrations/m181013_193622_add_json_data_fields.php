<?php

use yii\db\Migration;

/**
 * Class m181013_193622_add_json_data_fields
 */
class m181013_193622_add_json_data_fields extends Migration
{
  public $cafeTableName = '{{%cafe}}';
  public $franchiseeTableName = '{{%franchisee}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->addColumn($this->cafeTableName, 'data', $this->json());
    $this->addColumn($this->franchiseeTableName, 'data', $this->json());
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropColumn($this->franchiseeTableName, 'data');
    $this->dropColumn($this->cafeTableName, 'data');
  }
}
