<?php

use yii\db\Schema;

class m190403_091849_franchiseetariffs_RBAC extends \yii\db\Migration
{

  private $auth;
  public function up()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $roles = array(
      $this->auth->getRole('root'),
    );

    $this->createPermission(
      'FranchiseeTariffsView',
      'FranchiseeTariffs - просмотр (общая таблица)',
      $roles
    );

    $this->createPermission(
      'FranchiseeTariffsUpdate',
      'FranchiseeTariffs  - редактирование',
      $roles
    );
    $this->createPermission(
      'FranchiseeTariffsDelete',
      'FranchiseeTariffs - удаление',
      $roles
    );
    $this->createPermission(
      'FranchiseeTariffsCreate',
      'FranchiseeTariffs - создание',
      $roles
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
