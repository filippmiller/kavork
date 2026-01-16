<?php

use frontend\modules\users\models\Users;
use yii\db\Migration;

/**
 * Class m180724_181935_edit_user_table
 */
class m180724_181935_edit_user_table extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {

    $this->renameColumn('user', 'user', 'name');
    $this->renameColumn('user', 'cafe', 'cafe_id');
    $this->addColumn('user', "lg", $this->string(10)->defaultValue('en-EN'));
    $this->addColumn("user", 'franchisee_id', $this->integer()->defaultValue(1));
    $this->addColumn("user", 'phone', $this->string(20)->defaultValue("")->after('email'));

    $users = Users::find()->all();
    foreach ($users as $user) {
      $user->new_password = $user->pass;
      $user->save();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m180724_181935_edit_user_table cannot be reverted.\n";
    $this->renameColumn('user', 'name', 'user');
    $this->renameColumn('user', 'cafe_id', 'cafe');
    $this->dropColumn('user', 'franchisee');
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
