<?php

use yii\db\Migration;

/**
 * Class m180917_140053_cafe_RBAC_shop
 */
class m180915_140053_cafe_RBAC_shop extends Migration
{
  public $itemTableName = '{{%cafe_auth_item}}';
  public $assignmentTableName = '{{%cafe_auth_assignment}}';

  public $items = [
      "shop" => 'Магазин',
      "shopMerchantOnMain" => 'Магазин - отображение блока приход/расход на стартовой',
      "shopListToBay" => 'Магазин - Список покупок',
      "shopReport" => 'Магазин - отчет движения товаров',
      "shopPrintCheck" => 'Магазин - автоматическая печать чека',
  ];

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->execute('SET sql_mode=(SELECT REPLACE(@@sql_mode,\'ONLY_FULL_GROUP_BY\',\'\'));');

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
