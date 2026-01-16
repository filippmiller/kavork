<?php

use yii\db\Migration;

/**
 * Handles the creation of table `template`.
 */
class m180827_104422_create_template_table extends Migration
{
  public $tableName = '{{%template}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->createTable($this->tableName, [
        'id' => $this->primaryKey(),
        'scope_id' => $this->integer()->notNull(),
        'cafe_id' => $this->integer()->null(),
        'franchisee_id' => $this->integer()->null(),
        'type_id' => $this->integer()->notNull(),

        'content' => $this->text(),

        'updated_at' => $this->integer()->notNull()->defaultValue(0),
        'created_at' => $this->integer()->notNull()->defaultValue(0),
    ], 'ENGINE InnoDB');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable($this->tableName);
  }
}
