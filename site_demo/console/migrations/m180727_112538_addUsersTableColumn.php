<?php

use yii\db\Migration;

/**
 * Class m180727_112538_addUsersTableColumn
 */
class m180727_112538_addUsersTableColumn extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->createTable('user_column_table', [
        'id' => $this->primaryKey(),
        'user_id' => $this->integer()->notNull(),
        'table_name' => $this->string()->notNull(),
        'columns_show' => $this->string()->notNull(),
    ], 'ENGINE InnoDB');

    $this->addForeignKey(
        'fk_users_column_to_user',
        'user_column_table',
        'user_id',
        'user',
        'id'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('user_column_table');
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m180727_112538_addUsersTableColumn cannot be reverted.\n";

      return false;
  }
  */
}
