<?php

class m181214_033309_polls_RBAC extends \yii\db\Migration
{

  public $items = [
      "Polls" => 'Опросы',
  ];
  public $assignmentTableName = '{{%cafe_auth_assignment}}';
  public $itemTableName = '{{%cafe_auth_item}}';

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
        'PollsView',
        'Polls - просмотр (общая таблица)',
        $roles
    );

    $this->createPermission(
        'PollsUpdate',
        'Polls  - редактирование',
        $roles
    );
    $this->createPermission(
        'PollsDelete',
        'Polls - удаление',
        $roles
    );
    $this->createPermission(
        'PollsCreate',
        'Polls - создание',
        $roles
    );

    foreach ($this->items as $itemName => $itemDescription) {
      $this->insert($this->itemTableName, [
          'name' => $itemName,
          'description' => $itemDescription,
          'created_at' => time(),
          'updated_at' => time(),
      ]);
    }

    $query = new \yii\db\Query();
    $query->select(['id'])->from('{{%cafe}}')->orderBy('id');

    foreach ($query->each() as $cafe) {
      foreach ($this->items as $itemName => $itemDescription) {
        $this->insert($this->assignmentTableName, [
            'item_name' => $itemName,
            'cafe_id' => $cafe['id'],
            'created_at' => time(),
        ]);
      }
    }
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
