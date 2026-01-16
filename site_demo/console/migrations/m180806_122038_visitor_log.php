<?php

use yii\db\Migration;

/**
 * Class m180806_122038_visitor_log
 */
class m180806_122038_visitor_log extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->renameColumn('visitor_log', 'cafe', 'cafe_id');

    $this->execute('set time_zone = \'-4:00\';');

    $this->addColumn("visitor_log", 'vat', $this->json()->after('sum'));
    $this->execute('UPDATE `visitor_log` SET `vat` = CONCAT(\'[{"name":"tps","vat":\',tps,\'},{"name":"tvq","vat":\',tvq,\'}]\');');
    $this->dropColumn('visitor_log', 'tps');
    $this->dropColumn('visitor_log', 'tvq');


    $this->execute('UPDATE `visitor_log` SET `finish_time` = ' . time() . ' WHERE finish_time<100;');
    $this->execute('UPDATE `visitor_log` SET `pause_start` = 0 WHERE finish_time>0;');
    $this->execute('UPDATE `visitor_log` SET `pay_state` = `pay_state`+1 WHERE pay_state>=0;');

    $this->execute('ALTER TABLE `visitor_log` CHANGE `add_time` `add_time` TEXT NOT NULL;');
    $this->execute('UPDATE `visitor_log` SET `add_time` = from_unixtime(`add_time`);');
    $this->execute('ALTER TABLE `visitor_log` CHANGE `add_time` `add_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;');

    $this->execute('ALTER TABLE `visitor_log` CHANGE `finish_time` `finish_time` TEXT NOT NULL;');
    $this->execute('UPDATE `visitor_log` SET `finish_time` = from_unixtime(`finish_time`);');
    $this->execute('ALTER TABLE `visitor_log` CHANGE `finish_time` `finish_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;');

    $this->execute("ALTER TABLE `visitor_log` CHANGE `visitor_id` `visitor_id` INT(11) NULL;");
    $this->execute("UPDATE `visitor_log` SET `visitor_id` = NULL WHERE `visitor_id` = 0;");
    $this->execute("UPDATE `do_task` SET `user_id` = NULL WHERE `user_id` = 0;");
    $this->execute("ALTER TABLE `visitor_log` CHANGE `user_id` `user_id` INT(11) NULL;;");
    $this->execute("UPDATE `visitor_log` SET `user_id` = NULL WHERE `user_id` = 0;");
    $this->dropTable('user_log');

    $this->execute("DELETE FROM `user` WHERE `user`.`id` = 0");


    $this->execute('ALTER TABLE `visitor_log` CHANGE `finish_time` `finish_time` DATETIME NULL;');
    $this->execute('ALTER TABLE `visitor_log` CHANGE `finish_time` `finish_time` DATETIME NULL;');

    $this->execute("INSERT INTO `transaction` (`cafe_id`,`sum`,`cost`, `method`, `visit_id`, `sale_id`, `visitor_id`, `created_at`,`pay_man`,`terminal_ans`) 
        SELECT cafe_id,`sum`,cost,pay_state,visitor_log.id,null,visitor_id,finish_time,`pay_man`,`terminal_ans` FROM `visitor_log` WHERE pay_state>0 && cost>0 && finish_time>0;");
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->renameColumn('visitor_log', 'cafe_id', 'cafe');
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m180806_122038_visitor_log cannot be reverted.\n";

      return false;
  }
  */
}
