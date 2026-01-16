<?php

class m181202_103312_mailslog_RBAC extends \yii\db\Migration
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
        'MailsLogView',
        'MailsLog - просмотр (общая таблица)',
        $roles
    );

    $this->createPermission(
        'MailsLogUpdate',
        'MailsLog  - редактирование',
        $roles
    );
    $this->createPermission(
        'MailsLogDelete',
        'MailsLog - удаление',
        $roles
    );
    $this->createPermission(
        'MailsLogCreate',
        'MailsLog - создание',
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
