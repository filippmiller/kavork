<?php

use yii\db\Migration;

/**
 * Class m181017_154926_self_service_user_RBAC
 */
class m181017_154926_self_service_user_RBAC extends Migration
{
  /* @var \yii\rbac\ManagerInterface */
  public $auth;

  public $items = [
      [
          'name' => 'SelfServiceModeEnter',
          'description' => 'SelfService - Возможность перейти в режим Самообслуживания',
      ],
  ];

  public function up()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $defaultRoles = [
        $this->auth->getRole('root'),
    ];

    foreach ($this->items as $item) {
      $this->createPermission($item['name'], $item['description'], $defaultRoles);
    }
  }

  public function down()
  {
    $this->auth = \Yii::$app->authManager;
    foreach ($this->items as $item) {
      $permission = $this->auth->getPermission($item['name']);
      if ($permission) {
        $this->auth->remove($permission);
      }
    }
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
