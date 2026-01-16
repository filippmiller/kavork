<?php

use yii\db\Migration;

/**
 * Class m181017_154939_self_service_cafe_RBAC
 */
class m181017_154939_self_service_cafe_RBAC extends Migration
{
  public $itemTableName = '{{%cafe_auth_item}}';
  public $assignmentTableName = '{{%cafe_auth_assignment}}';

  public $items = [
      "selfServiceLoginOnlyMode" => 'Самообслуживание - Только Вход',
      "selfServiceLogoutOnlyMode" => 'Самообслуживание - Только Выход',
      "selfServiceHybridMode" => 'Самообслуживание - Вход и Выход',
  ];

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
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
    foreach ($this->items as $itemName => $itemDescription) {
      $this->delete($this->itemTableName, [
          'name' => $itemName,
      ]);
    }
  }
}
