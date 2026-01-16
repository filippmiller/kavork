<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shop_category`.
 */
class m180916_120834_create_shop_category_table extends Migration
{
  public $tableName = '{{%shop_category}}';
  public $cafeTableName = '{{%cafe}}';
  public $franchiseeTableName = '{{%franchisee}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->dropColumn($this->tableName, 'parent');
    $this->renameColumn($this->tableName, 'name', 'title');
    $this->alterColumn($this->tableName, 'title', $this->string()->notNull());
    $this->addColumn($this->tableName, 'cafe_id', $this->integer()->null()->after('id'));
    $this->addColumn($this->tableName, 'franchisee_id', $this->integer()->notNull()->after('id'));

    $this->addColumn($this->tableName, 'created_by', $this->integer()->defaultValue(0));
    $this->addColumn($this->tableName, 'updated_by', $this->integer()->defaultValue(0));
    $this->addColumn($this->tableName, 'updated_at', $this->dateTime());
    $this->addColumn($this->tableName, 'created_at', $this->dateTime());

    /*$this->createTable($this->tableName, [
      'id'            => $this->primaryKey(),
      'franchisee_id' => $this->integer()->notNull(),
      'cafe_id'       => $this->integer()->null(),
      'title'         => $this->string()->notNull(),

      'created_by' => $this->integer()->defaultValue(0),
      'updated_by' => $this->integer()->defaultValue(0),
      'updated_at' => $this->dateTime(),
      'created_at' => $this->dateTime(),
    ], 'ENGINE InnoDB');*/

    $this->execute('UPDATE `shop_category` SET `franchisee_id` = \'1\'');
    $this->addForeignKey('FK-category-to-cafe', $this->tableName, 'cafe_id', $this->cafeTableName, 'id', 'SET NULL', 'CASCADE');
    $this->addForeignKey('FK-category-to-franchisee', $this->tableName, 'franchisee_id', $this->franchiseeTableName, 'id', 'RESTRICT', 'CASCADE');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropForeignKey('FK-category-to-franchisee', $this->tableName);
    $this->dropForeignKey('FK-category-to-cafe', $this->tableName);

    $this->dropTable($this->tableName);
  }
}
