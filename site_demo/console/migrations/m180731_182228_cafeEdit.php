<?php

use yii\db\Migration;

/**
 * Class m180731_182228_cafeEdit
 */
class m180731_182228_cafeEdit extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->renameColumn('cafe', 'adres_1', 'address');
    $this->renameColumn('cafe', 'tps', 'tps_code');
    $this->renameColumn('cafe', 'tvq', 'tvq_code');
    $this->dropColumn('cafe', 'adres_2');
    $this->addColumn("cafe", 'tps_value', $this->float()->notNull());
    $this->addColumn("cafe", 'tvq_value', $this->float()->notNull());
    $this->addColumn("cafe", 'franchisee', $this->integer()->defaultValue(1));
    $this->addColumn("cafe", 'currency', $this->string(3)->defaultValue("USD"));
    $this->addColumn("cafe", 'timeZone', $this->string(30)->defaultValue("America/New_York"));
    $this->addColumn("cafe", 'tips_var', $this->string(100)->defaultValue("0.5,1,1.5,2,2.5"));
    $this->addColumn("cafe", 'selfservice_timaout', $this->integer()->defaultValue(5));
    $this->addColumn("cafe", 'api_key', $this->string(40));
    $this->addColumn("cafe", 'initSuccessful', $this->integer(1)->defaultValue(0)->after('last_task'));

    $this->addForeignKey(
        'fk-transaction-cafe_id-cafe-id',
        '{{%transaction}}',
        'cafe_id',
        '{{%cafe}}',
        'id',
        'SET NULL',
        'CASCADE'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m180731_182228_cafeEdit cannot be reverted.\n";
    $this->renameColumn('cafe', 'address', 'adres_1');
    $this->renameColumn('cafe', 'tps_code', 'tps');
    $this->renameColumn('cafe', 'tvq_code', 'tvq');
    $this->addColumn("cafe", 'adres_2', $this->string()->null());
    $this->dropColumn('cafe', 'tps_value');
    $this->dropColumn('cafe', 'tvq_value');
    $this->dropColumn('cafe', 'franchisee');
    $this->dropColumn('cafe', 'currency');
    $this->dropColumn('cafe', 'timeZone');
    $this->dropColumn('cafe', 'tips_var');
    $this->dropColumn('cafe', 'selfservice_timaout');
    $this->dropColumn('cafe', 'api_key');

    $this->dropIndex(
        'fk-transaction-cafe_id-cafe-id',
        '{{%transaction}}'
    );
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m180731_182228_cafeEdit cannot be reverted.\n";

      return false;
  }
  */
}
