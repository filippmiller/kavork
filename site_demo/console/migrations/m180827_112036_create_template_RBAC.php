<?php

use yii\db\Migration;

/**
 * Class m180827_112036_create_template_RBAC
 */
class m180827_112036_create_template_RBAC extends Migration
{
  /* @var \yii\rbac\ManagerInterface */
  public $auth;

  public function up()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $roles = [
        $this->auth->getRole('root'),
        $this->auth->getRole('admin')
    ];

    $this->createPermission(
        'TemplateView',
        'Template - просмотр (общая таблица)',
        $roles
    );

    $this->createPermission(
        'TemplateUpdate',
        'Template - редактирование',
        $roles
    );

    $this->createPermission(
        'TemplateDelete',
        'Template - удаление',
        $roles
    );

    $this->createPermission(
        'TemplateCreate',
        'Template - создание',
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
