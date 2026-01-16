<?php

class m181218_090223_usertimetable_RBAC extends \yii\db\Migration
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
        'UserTimetableView',
        'UserTimetable - просмотр',
        $roles
    );

    $this->createPermission(
        'UserTimetableUpdate',
        'UserTimetable  - редактирование',
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
