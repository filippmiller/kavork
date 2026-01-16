<?php

use yii\db\Migration;

/**
 * Class m181224_161255_update_task
 */
class m181224_161255_update_task extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->renameColumn('task', 'cafe', 'cafe_id');
    $this->addForeignKey('FK-task-to-cafe', 'task', 'cafe_id', 'cafe', 'id', 'SET NULL', 'CASCADE');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m181224_161255_update_task cannot be reverted.\n";

    return false;
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m181224_161255_update_task cannot be reverted.\n";

      return false;
  }
  */
}
