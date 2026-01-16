<?php

use yii\db\Schema;

class m190403_091739_franchiseepayments_RBAC extends \yii\db\Migration
{

  private $auth;
  public function up()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $roles = array(
      $this->auth->getRole('root'),
      $this->auth->getRole('admin'),
    );

    $this->createPermission(
      'FranchiseePaymentsView',
      'FranchiseePayments - просмотр (общая таблица)',
      $roles
    );

    $this->createPermission(
      'FranchiseePaymentsCreate',
      'FranchiseePayments - создание',
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
