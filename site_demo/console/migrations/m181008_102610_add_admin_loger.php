<?php

use yii\db\Migration;

/**
 * Class m181008_102610_add_admin_loger
 */
class m181008_102610_add_admin_loger extends Migration
{

  public $itemTableName = '{{%cafe_auth_item}}';
  public $assignmentTableName = '{{%cafe_auth_assignment}}';

  public $items = [
      "adminLog" => 'Администратор - учет рабочего времени',
      "adminReport" => 'Администратор - понедельный отчет',
      "adminTable" => 'Администратор - отдельная таблица сессий + суммы по периодам',
  ];

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->execute('SET sql_mode=(SELECT REPLACE(@@sql_mode,\'ONLY_FULL_GROUP_BY\',\'\'));');

    $this->renameTable('admin_log', 'user_log');
    $this->dropColumn('user_log', 'ses_id');
    $this->renameColumn('user_log', 'cafe', 'cafe_id');
    $this->execute('set time_zone = \'-4:00\';');

    $this->execute('ALTER TABLE `user_log` CHANGE `start` `start` TEXT NOT NULL;');
    $this->execute('UPDATE `user_log` SET `start` = from_unixtime(`start`);');
    $this->execute('ALTER TABLE `user_log` CHANGE `start` `start` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;');

    $this->execute('ALTER TABLE `user_log` CHANGE `finish` `finish` TEXT NOT NULL;');
    $this->execute('UPDATE `user_log` SET `finish` = from_unixtime(`finish`);');
    $this->execute('ALTER TABLE `user_log` CHANGE `finish` `finish` DATETIME NULL DEFAULT NULL;');

    $this->execute('DELETE FROM `user_log` WHERE `finish`< \'2016-01-01 00:00:00\'');
    $this->execute('DELETE FROM `user_log` WHERE `start`< \'2016-01-01 00:00:00\'');


    foreach ($this->items as $itemName => $itemDescription) {
      $this->insert($this->itemTableName, [
          'name' => $itemName,
          'description' => $itemDescription,
          'created_at' => time(),
          'updated_at' => time(),
      ]);
    }

    $query = new \yii\db\Query();
    $query->select(['id'])->from('{{%cafe}}')->orderBy('id');

    foreach ($query->each() as $cafe) {
      foreach ($this->items as $itemName => $itemDescription) {
        $this->insert($this->assignmentTableName, [
            'item_name' => $itemName,
            'cafe_id' => $cafe['id'],
            'created_at' => time(),
        ]);
      }
    }

    $this->execute('    ALTER TABLE `user_log` ADD CONSTRAINT `user_log_cafe_id` FOREIGN KEY (`cafe_id`) REFERENCES `cafe`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->addColumn("user_log", 'ses_id', $this->string()->null());
    $this->renameColumn('user_log', 'cafe_id', 'cafe');

    foreach ($this->items as $itemName => $itemDescription) {
      $this->delete($this->itemTableName, [
          'name' => $itemName,
      ]);
    }
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m181008_102610_add_admin_loger cannot be reverted.\n";

      return false;
  }
  */
}
