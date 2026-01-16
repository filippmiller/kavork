<?php

use yii\db\Migration;

/**
 * Class m180819_102259_franchisee_RBAC
 */
class m180819_102259_franchisee_RBAC extends Migration
{
  /* @var \yii\rbac\ManagerInterface */
  public $auth;

  public function up()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $role = $this->auth->getRole('root');

    $this->createPermission(
        'FranchiseeView',
        'Franchisee - просмотр (общая таблица)',
        [$role]
    );

    $this->createPermission(
        'FranchiseeUpdate',
        'Franchisee - редактирование',
        [$role]
    );

    $this->createPermission(
        'FranchiseeDelete',
        'Franchisee - удаление',
        [$role]
    );

    $this->createPermission(
        'FranchiseeCreate',
        'Franchisee - создание',
        [$role]
    );
  }

  public function down()
  {
    //откат миграции
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
}
