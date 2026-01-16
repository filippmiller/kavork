<?php

use yii\db\Migration;

/**
 * Class m180822_162324_fill_cafe_auth_tables
 */
class m180822_162324_fill_cafe_auth_tables extends Migration
{
  public $itemTableName = '{{%cafe_auth_item}}';
  public $assignmentTableName = '{{%cafe_auth_assignment}}';

  public $items = [
      "startVisit" => 'Работа с визитами',
      "AnonymousVisitor" => 'Анонимный пользователь',
      "payCash" => 'Оплата наличкой',
      "payCard" => 'Оплата картой',
      "payNOT" => 'Отказ оплаты',
      "certificate" => 'Сертификаты',
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
