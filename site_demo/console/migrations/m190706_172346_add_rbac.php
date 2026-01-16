<?php

use yii\db\Migration;


/**
 * Class m190706_172346_add_rbac
 */
class m190706_172346_add_rbac extends Migration
{

  private $auth;
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $roles = array(
        $this->auth->getRole('root'),
    );

    $this->createPermission(
        'Announcement',
        'Редактирование банера саморегистрации',
        $roles
    );

    $this->createPermission(
        'ShopView',
        'Магазин',
        $roles
    );

    $this->createPermission(
        'TransactionView',
        'Отчет по транзакциям',
        $roles
    );

    $this->createPermission(
        'ShopInventoryDelete',
        'Удаление товаров в товароведении',
        $roles
    );


  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m190706_172346_add_rbac cannot be reverted.\n";

    return false;
  }

  private function createPermission($name, $description = '', $roles = [])
  {
    $permit = $this->auth->createPermission($name);
    $permit->description = $description;
    $this->auth->add($permit);
    foreach ($roles as $role) {
      $this->auth->addChild($role, $permit);//Связываем роль и привелегию
    }
  }

  /*
  // Use up()/down() to run migration code without a transaction.
  public function up()
  {

  }

  public function down()
  {
      echo "m190706_172346_add_rbac cannot be reverted.\n";

      return false;
  }
  */
}
