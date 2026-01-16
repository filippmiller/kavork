<?php

use yii\db\Migration;

/**
 * Class m181130_112309_mails
 */
class m181130_112309_mails extends Migration
{
  public $tableName = '{{%template_mails}}';
  public $cafeTableName = '{{%cafe}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->dropTable("mails");

    $this->createTable($this->tableName, [
        'id' => $this->primaryKey(),
      //'scope_id'      => $this->integer()->notNull(),
      //'franchisee_id' => $this->integer()->null(),
      //'type_id'       => $this->integer()->notNull(),

        'title' => $this->string(255),
        'content' => $this->text(),
        'user_filter' => $this->json(),
        'background' => $this->string(),
        'width' => $this->integer(),
      //'status' => $this->boolean(),

        'cafe_id' => $this->integer()->null(),

        'updated_at' => $this->integer()->notNull()->defaultValue(0),
        'created_at' => $this->integer()->notNull()->defaultValue(0),
    ], 'ENGINE InnoDB');

    $this->addForeignKey('FK-template_mail_to_cafe', $this->tableName, 'cafe_id', $this->cafeTableName, 'id', 'CASCADE', 'CASCADE');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropForeignKey('FK-template_mail_to_cafe', $this->tableName);

    $this->dropTable($this->tableName);
  }
}
