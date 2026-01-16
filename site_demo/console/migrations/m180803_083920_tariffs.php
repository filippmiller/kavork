<?php

use frontend\modules\tariffs\models\Tariffs;
use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m180803_083920_tarifs
 */
class m180803_083920_tariffs extends Migration
{

  public $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
  public $tableName = 'tariffs';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {

    $this->createTable($this->tableName, [
        'id' => Schema::TYPE_PK,
        'cafe_id' => Schema::TYPE_INTEGER . ' NOT NULL',
        'franchisee_id' => $this->integer()->defaultValue(1),
        'min_sum' => Schema::TYPE_FLOAT . ' NOT NULL',
        'max_sum' => Schema::TYPE_FLOAT . ' NOT NULL',
        'first_hour' => Schema::TYPE_FLOAT . ' NOT NULL',
        'data' => Schema::TYPE_JSON,
        'start_visit' => Schema::TYPE_FLOAT . ' NULL DEFAULT 1',
        'active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
    ], $this->tableOptions);

    $this->addForeignKey(
        'fk_tariffs_cafe_id',
        $this->tableName,
        'cafe_id',
        'cafe',
        'id'
    );

    $this->addColumn($this->tableName, 'type_id', $this->smallInteger()->notNull()->defaultValue(Tariffs::TYPE_CAFE_ORIENTED)->after('id'));
    $this->addColumn($this->tableName, 'params_id', $this->integer()->null()->after('cafe_id'));

    $this->dropForeignKey('fk_tariffs_cafe_id', $this->tableName);
    $this->alterColumn($this->tableName, 'cafe_id', $this->integer()->null());
    $this->addForeignKey('fk_tariffs_cafe_id', $this->tableName, 'cafe_id', '{{%cafe}}', 'id', 'SET NULL', 'CASCADE');

    /*$tariff=new Tariffs();
    $tariff->type_id=Tariffs::TYPE_REGIONAL;
    $tariff->params_id=1;
    $tariff->min_sum=4;
    $tariff->max_sum=10;
    $tariff->first_hour=3;
    $tariff->next_hour=2;
    $tariff->start_visit=0;
    $tariff->active=0;
    $tariff->cafe_id=null;*/

    //$tariff->save();
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('tariffs');
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m180803_083920_tariffs cannot be reverted.\n";

      return false;
  }
  */
}
