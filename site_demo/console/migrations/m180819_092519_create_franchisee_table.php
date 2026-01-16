<?php

use yii\db\Migration;

/**
 * Handles the creation of table `franchisee`.
 */
class m180819_092519_create_franchisee_table extends Migration
{
  public $tableName = '{{%franchisee}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->createTable($this->tableName, [
        'id' => $this->primaryKey(),
        'name' => $this->string()->notNull(),
        'max_cafe' => $this->integer()->defaultValue(1),
        'tariff_id' => $this->integer()->defaultValue(1),
        'date_end' => $this->dateTime(),
        'active_until' => $this->dateTime(),
        'code' => $this->string()->notNull(),
        'roles' => $this->text(),
        'created_at' => $this->dateTime(),
    ], 'ENGINE InnoDB');

    $this->insert($this->tableName, [
        'id' => 1,
        'name' => 'Our cafe',
        'active_until' => '2020-01-01 00:00:01',
        'code' => 'ANTI',
        'roles' => '',
        'created_at' => new \yii\db\Expression('NOW()'),
    ]);

    $this->update('{{%visitor}}', ['franchisee_id' => 1]);

    //$this->addForeignKey('FK-tariff_fr-to-franchisee', $this->tableName, 'franchisee_id', '{{%franchisee_tariffs}}', 'id', 'CASCADE', 'CASCADE');
    //$this->addForeignKey('FK-user-to-franchisee', '{{%user}}', 'tariff_id', $this->tableName, 'id', 'CASCADE', 'CASCADE');
    $this->addForeignKey('FK-cafe-to-franchisee', '{{%cafe}}', 'franchisee_id', $this->tableName, 'id', 'CASCADE', 'CASCADE');
    $this->addForeignKey('FK-visitor-to-franchisee', '{{%visitor}}', 'franchisee_id', $this->tableName, 'id', 'CASCADE', 'CASCADE');
    $this->addForeignKey('FK-tariff-to-franchisee', '{{%tariffs}}', 'franchisee_id', $this->tableName, 'id', 'CASCADE', 'CASCADE');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropForeignKey('FK-user-to-franchisee', '{{%user}}');
    $this->dropForeignKey('FK-cafe-to-franchisee', '{{%cafe}}');
    $this->dropForeignKey('FK-visitor-to-franchisee', '{{%visitor}}');

    $this->dropTable($this->tableName);
  }
}
