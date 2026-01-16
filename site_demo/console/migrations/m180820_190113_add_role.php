<?php

use yii\db\Migration;

/**
 * Class m180820_190113_add_role
 */
class m180820_190113_add_role extends Migration
{
  /* @var \yii\rbac\ManagerInterface */
  public $auth;

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->auth = \Yii::$app->authManager;

    $role = $this->auth->getRole('admin');

    $this->addPermission('CafeCreate', $role);
    $this->addPermission('AllCafeShow', $role);
    $this->addPermission('CafeUpdate', $role);
    $this->addPermission('CafeView', $role);
    $this->addPermission('CanChangeCafe', $role);
    $this->addPermission('ChooseCafe', $role);
    $this->addPermission('MainAdmin', $role);
    $this->addPermission('TariffsCreate', $role);
    $this->addPermission('TariffsDelete', $role);
    $this->addPermission('TariffsUpdate', $role);
    $this->addPermission('TariffsView', $role);
    $this->addPermission('UsersCreate', $role);
    $this->addPermission('UsersDelete', $role);
    $this->addPermission('UsersView', $role);
    $this->addPermission('VisitorCreate', $role);
    $this->addPermission('VisitorDelete', $role);
    $this->addPermission('VisitorUpdate', $role);
    $this->addPermission('VisitorView', $role);
    $this->addPermission('VisitorLogCreate', $role);
    $this->addPermission('VisitorLogDelete', $role);
    $this->addPermission('VisitorLogUpdate', $role);
    $this->addPermission('VisitorLogView', $role);
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    echo "m180820_190113_add_role cannot be reverted.\n";

    return false;
  }

  private function addPermission($name, $role)
  {
    $permit = $this->auth->getPermission($name);
    $this->auth->addChild($role, $permit);//Связываем роль и привелегию
  }

}
