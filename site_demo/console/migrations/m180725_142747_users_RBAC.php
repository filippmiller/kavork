<?php

class m180725_142747_users_RBAC extends \yii\db\Migration
{

  private $auth;

  public function up()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $role = $this->auth->createRole('admin');
    $role->description = 'Cafe admin';
    $this->auth->add($role);

    $role = $this->auth->getRole('root');

    $roleAll = [
        $role,
        $this->auth->getRole('admin')
    ];

    $this->createPermission(
        'MainAdmin',
        'Main - отображение админ блока на стартовой',
        [$role]
    );

    $this->createPermission(
        'UsersView',
        'Users - просмотр (общая таблица)',
        [$role]
    );

    $this->createPermission(
        'UsersUpdate',
        'Users  - редактирование',
        [$role]
    );

    $this->createPermission(
        'UsersDelete',
        'Users - блокировка',
        [$role]
    );

    $this->createPermission(
        'UsersCreate',
        'Users - создание',
        [$role]
    );

    $this->createPermission(
        'AllFranchisee',
        'Видет данные от всех франчази и управляет ими',
        [$role]
    );

    $this->createPermission(
        'AllChange',
        'Расширеные права для смены параметров (валюта,часовой пояс)',
        $roleAll
    );

    $this->createPermission(
        'AllCafeShow',
        'Отображать все кафе',
        [$role]
    );

    $this->createPermission(
        'CanChangeCafe',
        'Users - может менять кафе пользователю и в системе',
        [$role]
    );

    $this->createPermission(
        'ChooseCafe',
        'Cafe - Менять текущее кафе без выхода из системы',
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
