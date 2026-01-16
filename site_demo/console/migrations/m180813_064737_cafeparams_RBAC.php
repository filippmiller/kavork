<?php

class m180813_064737_cafeparams_RBAC extends \yii\db\Migration
{

  private $auth;

  public function up()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $role = $this->auth->getRole('root');

    $this->createPermission(
        'CafeParamsView',
        'CafeParams - просмотр (общая таблица)',
        [$role]
    );

    $this->createPermission(
        'CafeParamsUpdate',
        'CafeParams  - редактирование',
        [$role]
    );


    $this->createPermission(
        'CafeParamsCreate',
        'CafeParams - создание',
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
