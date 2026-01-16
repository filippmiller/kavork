<?php

use yii\db\Migration;

/**
 * Class m190102_115213_fix2
 */
class m190102_115213_fix2 extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->dropColumn('visitor_log', 'notice');
    $this->addColumn('visitor_log', 'notice', $this->json()->null()->after('tip'));

    $this->addColumn('cafe', 'selfmode_banner', $this->json()->null());
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m190102_115213_fix2 cannot be reverted.\n";

    return false;
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m190102_115213_fix2 cannot be reverted.\n";

      return false;
  }
  */
}
