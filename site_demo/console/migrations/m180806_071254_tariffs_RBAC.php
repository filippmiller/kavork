<?php

class m180806_071254_tariffs_RBAC extends \yii\db\Migration
{

  private $auth;

  public function up()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $role = $this->auth->getRole('root');

    $this->createPermission(
        'TariffsView',
        'Tariffs - просмотр (общая таблица)',
        [$role]
    );

    $this->createPermission(
        'TariffsUpdate',
        'Tariffs  - редактирование',
        [$role]
    );

    $this->createPermission(
        'TariffsDelete',
        'Tariffs - удаление',
        [$role]
    );

    $this->createPermission(
        'TariffsCreate',
        'Tariffs - создание',
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
