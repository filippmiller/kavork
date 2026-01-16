<?php

use yii\db\Migration;

/**
 * Class m181214_075654_update_polls
 */
class m181214_075654_update_polls extends Migration
{


  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->execute('set time_zone = \'-4:00\';');

    $this->alterColumn('polls', 'answers', $this->json()->null());

    $this->execute('UPDATE `polls` SET `user_status` = \'-1\' WHERE `user_status` = 2;');
    $this->execute('UPDATE `polls` SET `user_status` = `user_status`+1 WHERE `user_status` >= 0;');

    $this->execute('UPDATE `polls_ans` SET `ans` = `ans`+1 WHERE `ans` >= 0;');

    $this->execute('ALTER TABLE `polls` CHANGE `created` `created` TEXT NOT NULL;');
    $this->execute('UPDATE `polls` SET `created` = from_unixtime(`created`);');
    $this->execute('ALTER TABLE `polls` CHANGE `created` `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m181214_075654_update_polls cannot be reverted.\n";

    return false;
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m181214_075654_update_polls cannot be reverted.\n";

      return false;
  }
  */
}
