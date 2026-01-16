<?php

class m180802_130243_cafe_RBAC extends \yii\db\Migration
{

  private $auth;

  public function up()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $role = $this->auth->getRole('root');

    $this->createPermission(
        'CafeView',
        'Cafe - просмотр (общая таблица)',
        [$role]
    );

    $this->createPermission(
        'CafeUpdate',
        'Cafe  - редактирование',
        [$role]
    );

    $this->createPermission(
        'CafeCreate',
        'Cafe - создание',
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
