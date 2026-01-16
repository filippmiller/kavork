<?php

use yii\db\Migration;

/**
 * Class m181218_085051_time_table_update
 */
class m181218_085051_time_table_update extends Migration
{

  public $items = [
      "Timetable" => 'Расписание выходов администраторов',
  ];
  public $assignmentTableName = '{{%cafe_auth_assignment}}';
  public $itemTableName = '{{%cafe_auth_item}}';


  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->execute('set time_zone = \'-4:00\';');

    $this->execute('ALTER TABLE `user_timetable` CHANGE `start` `start` TEXT NOT NULL;');
    $this->execute('UPDATE `user_timetable` SET `start` = from_unixtime(`start`);');
    $this->execute('ALTER TABLE `user_timetable` CHANGE `start` `start` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;');

    $this->execute('ALTER TABLE `user_timetable` CHANGE `end` `end` TEXT NOT NULL;');
    $this->execute('UPDATE `user_timetable` SET `end` = from_unixtime(`end`);');
    $this->execute('ALTER TABLE `user_timetable` CHANGE `end` `end` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;');


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
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m181218_085051_time_table_update cannot be reverted.\n";

    return false;
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m181218_085051_time_table_update cannot be reverted.\n";

      return false;
  }
  */
}
