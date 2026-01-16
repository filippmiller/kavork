<?php

class m181008_112616_userlog_RBAC extends \yii\db\Migration
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
        'UserLogView',
        'UserLog - просмотр (общая таблица)',
        $roles
    );

    $this->createPermission(
        'UserLogWeakReport',
        'UserLog - просмотр понедельных отчетов',
        $roles
    );

    $this->createPermission(
        'UserTable',
        'UserLog - отдельная таблица сессий + суммы по периодам',
        $roles
    );

    $this->createPermission(
        'UserLogUpdate',
        'UserLog  - редактирование',
        $roles
    );
    $this->createPermission(
        'UserLogDelete',
        'UserLog - удаление',
        $roles
    );
    $this->createPermission(
        'UserLogCreate',
        'UserLog - создание',
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
