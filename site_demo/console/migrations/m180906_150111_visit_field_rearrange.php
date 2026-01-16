<?php

use yii\db\Migration;

/**
 * Class m180906_150111_visit_field_rearrange
 */
class m180906_150111_visit_field_rearrange extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $sql = <<<SQL
          UPDATE {{%visitor_log}} SET comment = CONCAT(comment, ' ', notice), notice = '' WHERE notice != '' OR comment != '';
SQL;

    Yii::$app->getDb()->createCommand($sql)->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    return true;
  }

}
