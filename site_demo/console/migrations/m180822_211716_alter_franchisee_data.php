<?php

use frontend\modules\cafe\models\CafeAuthItem;
use yii\db\Migration;

/**
 * Class m180822_211716_alter_franchisee_data
 */
class m180822_211716_alter_franchisee_data extends Migration
{
  public $tableName = '{{%franchisee}}';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $roles = CafeAuthItem::find()->select('name')->column();

    $this->update($this->tableName, [
        'roles' => implode(',', $roles),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    return true;
  }

}
