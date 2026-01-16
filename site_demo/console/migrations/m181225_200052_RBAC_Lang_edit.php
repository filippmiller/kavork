<?php

use yii\db\Migration;

/**
 * Class m181225_200052_RBAC_Lang_edit
 */
class m181225_200052_RBAC_Lang_edit extends Migration
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
        'Lang_edit',
        'Language - редактирование языковых',
        $roles
    );
  }

  /**
   * {@inheritdoc}
   */
  public function down()
  {

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

  private function removePermission($name, $roles = [])
  {
    $permit = $this->auth->getPermission($name);
    foreach ($roles as $role) {
      $this->auth->removeChild($role, $permit);
    }
    $this->auth->remove($permit);
  }
}
