<?php

use frontend\modules\cafe\models\CafeParams;
use yii\db\Migration;

/**
 * Class m180813_092631_cafe_params
 */
class m180813_092631_cafe_params extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->execute('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=\'TRADITIONAL,ALLOW_INVALID_DATES\';');
    $this->execute('SET SQL_MODE=\'ALLOW_INVALID_DATES\';');

    $this->addColumn("cafe", 'params_id', $this->integer()->notNull()->defaultValue(1));
    $this->addColumn("cafe", 'vat_code', $this->string());
    $this->addColumn("cafe", 'logo', $this->string());

    $this->createTable('cafe_params', [
        'id' => $this->primaryKey(),
        'name' => $this->string(20)->notNull(),
        'vat_list' => $this->string(300),
        "banknote_list" => $this->text(),
        'show_sum' => $this->integer(1)->defaultValue(1),
        'first_weekday' => $this->integer(0)->defaultValue(0),
        'time_zone' => $this->string(30)->defaultValue("America/New_York"),
        'time_format' => $this->integer(1)->defaultValue(CafeParams::TIME_12),
        'date_format' => $this->integer(1)->defaultValue(CafeParams::DATE_MM_DD_YYYY),
        'show_second' => $this->integer(1)->defaultValue(CafeParams::TIME_SECOND_HIDDEN),
      /* 'datetime'=> $this->string(30)->defaultValue("Y-m-d g:i:s A"),
       'datetime_js'=> $this->string(30)->defaultValue("YYYY-MM-DD"),
       'datetime_short'=> $this->string(30)->defaultValue("Y-m-d"),
       'datetime_short_js'=> $this->string(30)->defaultValue("Y-m-d"),
       'date'=> $this->string(30)->defaultValue("Y-m-d"),
       'date_js'=> $this->string(30)->defaultValue("YYYY-MM-DD"),
       'time'=> $this->string(30)->defaultValue("g:i A"),
       'time_js'=> $this->string(30)->defaultValue("g:i A")*/
    ], 'ENGINE InnoDB');

    $this->dropColumn('cafe', 'timeZone');
    $this->dropColumn('cafe', 'tps_code');
    $this->dropColumn('cafe', 'tvq_code');
    $this->dropColumn('cafe', 'tps_value');
    $this->dropColumn('cafe', 'tvq_value');

    $params = new CafeParams();
    $params->id = 1;
    $params->name = "Canada(Quebec)";
    $params->banknote_list = "1,2,10,50,100";
    $params->vat_list = '[{"name":"tps","value":"1","add_to_cost":"1","only_for_base_cost":"1"},{"name":"tvq","value":"0.5","add_to_cost":"1","only_for_base_cost":"1"}]';
    $params->save();

    $params = new CafeParams();
    $params->id = 2;
    $params->name = "Russia";
    $params->banknote_list = "5,10,50,100,200,500,1000,2000,5000";
    $params->vat_list = '[{"name":"НДС","value":"20","add_to_cost":"0","only_for_base_cost":"1"}]';
    $params->time_format = CafeParams::TIME_24;
    $params->date_format = CafeParams::DATE_DD_MM_YYYY;
    $params->first_weekday = 1;
    $params->save();

    $this->addForeignKey(
        'fk_cafe_to_params_id',
        'cafe',
        'params_id',
        'cafe_params',
        'id'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m180813_092631_cafe_params cannot be reverted.\n";

    return false;
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m180813_092631_cafe_params cannot be reverted.\n";

      return false;
  }
  */
}
