<?php

use yii\db\Migration;

/**
 * Class m180724_181935_edit_user_table
 */
class m180724_181934_fixs extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {

    $encode = [
        'visitor' => ['f_name', 'l_name', 'notice'],
        'polls_ans' => ['txt'],
        'polls' => ['question', 'answers'],
        'visitor_log' => ['notice', 'comment'],
    ];

    $dbname = ($this->getDsnAttribute('dbname', Yii::$app->getDb()->dsn));
    $this->execute('ALTER DATABASE `' . $dbname . '` DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;');
    $this->execute('SET default_storage_engine=InnoDB;');
    $this->execute('ALTER TABLE event_data ENGINE=InnoDB;
ALTER TABLE goods_transit ENGINE=InnoDB;
ALTER TABLE order_list ENGINE=InnoDB;
ALTER TABLE payouts ENGINE=InnoDB;
ALTER TABLE report_day ENGINE=InnoDB;
ALTER TABLE sales ENGINE=InnoDB;
ALTER TABLE shop_category ENGINE=InnoDB;
ALTER TABLE shop_items ENGINE=InnoDB;
ALTER TABLE suppliers ENGINE=InnoDB;
ALTER TABLE visitor_prereg ENGINE=InnoDB;');

    $limit = 500;

    echo date('H:i:s') . '  Start' . "\n";

    $this->execute('ALTER TABLE `visitor` CHANGE `f_name` `f_name` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;');
    $this->execute('ALTER TABLE `visitor` CHANGE `l_name` `l_name` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;');

    foreach ($encode as $table => $rows) {
      $last_id = 0;
      $base_sql = 'select id,`' . implode('`,`', $rows) . '` FROM ' . $table . ' WHERE (`' .
          implode('` like \'%©%\' or `', $rows) . '` like \'%©%\' or `' .
          implode('` like \'%Ã%\' or `', $rows) . '` like \'%Ã%\' or `' .
          implode('` like \'%Г%\' or `', $rows) . '` like \'%Г%\' or `' .
          implode('` like \'%§%\' or `', $rows) . '` like \'%§%\')';

      $sql = $base_sql . ' AND id>' . $last_id . ' limit ' . $limit;
      $data = Yii::$app->db->createCommand($sql)
          ->queryAll();

      while (!empty($data)) {
        foreach ($data as $d) {
          //d($d);
          foreach ($d as $name => &$item) {
            if ($name == 'id' || empty($item)) continue;
            //$item = str_replace('Ã©','Г©',$item);//
            //$item = str_replace('Ã‰','Г‰',$item);//É
            //$item = str_replace('Ã§','Г§',$item);//ç
            //$item = str_replace('Ã','Г',$item);
            $code = strpos($item, 'Ã') !== false
                ? 'Windows-1252' : 'Windows-1251';
            $item = mb_convert_encoding($item, $code, "UTF-8");
            //d($code,$d);
          }
          Yii::$app->db->createCommand()
              ->update($table, $d, 'id = ' . $d['id'])->execute();
          $last_id = $d['id'];
        }

        echo date('H:i:s') . '  ' . $last_id . "\n";

        $sql = $base_sql . ' AND id>' . $last_id . ' limit ' . $limit;
        $data = Yii::$app->db->createCommand($sql)
            ->queryAll();
        d($sql);
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {

  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m180724_181935_edit_user_table cannot be reverted.\n";

      return false;
  }
  */

  private function getDsnAttribute($name, $dsn)
  {
    if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
      return $match[1];
    } else {
      return null;
    }
  }
}
