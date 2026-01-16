<?php

use frontend\modules\users\models\UserCafe;
use yii\db\Migration;

/**
 * Class m180727_105151_addTableUserCafe
 */
class m180727_105151_addTableUserCafe extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->execute('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=\'TRADITIONAL,ALLOW_INVALID_DATES\';');
    $this->execute('SET SQL_MODE=\'ALLOW_INVALID_DATES\';');

    $userRole = Yii::$app->authManager->getRole('root');
    Yii::$app->authManager->assign($userRole, 2);
    Yii::$app->authManager->assign($userRole, 1);

    $this->createTable('user_cafe', [
        'id' => $this->primaryKey(),
        'cafe_id' => $this->integer()->notNull(),
        'user_id' => $this->integer()->notNull()
    ], 'ENGINE InnoDB');


    $this->addForeignKey(
        'fk_users_cafe_to_user',
        'user_cafe',
        'user_id',
        'user',
        'id'
    );

    $this->addForeignKey(
        'fk_users_cafe_to_cafe',
        'user_cafe',
        'cafe_id',
        'cafe',
        'id'
    );

    $users = \frontend\modules\users\models\Users::find()->all();
    foreach ($users as $user) {
      if (!$user->cafe_id || strlen($user->cafe_id) < 1) continue;
      $cafes = explode(',', $user->cafe_id);
      for ($i = 0; $i < count($cafes); $i++) {
        $uk = new UserCafe();
        $uk->user_id = $user->id;
        $uk->cafe_id = (int)$cafes[$i];
        $uk->save();
      }
    }

    $this->dropColumn('user', 'cafe_id');
    $this->dropColumn('user', 'role');
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->execute('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=\'TRADITIONAL,ALLOW_INVALID_DATES\';');
    $this->execute('SET SQL_MODE=\'ALLOW_INVALID_DATES\';');

    $this->dropForeignKey('fk_users_cafe_to_user', 'user_cafe');
    $this->dropForeignKey('fk_users_cafe_to_cafe', 'user_cafe');
    $this->dropTable('user_cafe');
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m180727_105151_addTableUserCafe cannot be reverted.\n";

      return false;
  }
  */
}
