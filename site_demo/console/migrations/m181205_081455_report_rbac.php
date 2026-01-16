<?php

use yii\db\Migration;

/**
 * Class m181205_081455_report_rbac
 */
class m181205_081455_report_rbac extends Migration
{

  public $itemTableName = '{{%cafe_auth_item}}';
  public $assignmentTableName = '{{%cafe_auth_assignment}}';

  public $items = [
      "ReportView" => 'Отчеты онлайн',
      "ReportMail" => 'Отчеты на почту',
  ];

  /* @var \yii\rbac\ManagerInterface */
  public $auth;

  public $items_user = [
      [
          'name' => 'ReportView',
          'description' => 'Report - просмотр онлайн',
      ],
      [
          'name' => 'ReportMail',
          'description' => 'Report - отправка на почту',
      ],
  ];

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {

    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $defaultRoles = [
        $this->auth->getRole('root'),
    ];

    foreach ($this->items_user as $item) {
      $this->createPermission($item['name'], $item['description'], $defaultRoles);
    }

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

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {

    $this->auth = \Yii::$app->authManager;
    foreach ($this->items_user as $item) {
      $permission = $this->auth->getPermission($item['name']);
      if ($permission) {
        $this->auth->remove($permission);
      }
    }

    foreach ($this->items as $itemName => $itemDescription) {
      $this->delete($this->itemTableName, [
          'name' => $itemName,
      ]);
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
