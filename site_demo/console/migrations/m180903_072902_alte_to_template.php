<?php

use yii\db\Migration;

/**
 * Class m180903_072902_alte_to_template
 */
class m180903_072902_alte_to_template extends Migration
{
  public $tableName = '{{%template}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->addColumn("cafe", 'width', $this->integer()->null()->defaultValue(82));
    $this->addColumn($this->tableName, 'width', $this->integer()->null()->defaultValue(600)->after('content'));
    $this->addColumn($this->tableName, 'background', $this->string()->null()->defaultValue("#ffffff")->after('width'));
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m180903_072902_alte_to_template cannot be reverted.\n";
    $this->dropColumn("cafe", 'width');
    $this->dropColumn($this->tableName, 'width');
    $this->dropColumn($this->tableName, 'background');
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m180903_072902_alte_to_template cannot be reverted.\n";

      return false;
  }
  */
}
