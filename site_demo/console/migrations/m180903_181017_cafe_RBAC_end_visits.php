<?php

use yii\db\Migration;

/**
 * Class m180903_181017_cafe_RBAC_end_visits
 */
class m180903_181017_cafe_RBAC_end_visits extends Migration
{

  public $itemTableName = '{{%cafe_auth_item}}';
  public $assignmentTableName = '{{%cafe_auth_assignment}}';

  public $items = [
      "endVisitPrintCheckManual" => 'Печать чека по завершению визита вручную',
      "endVisitMailCheckManual" => 'Отправка чека письмом на почту по завершению визита вручную',
      "endVisitPrintCheckAuto" => 'Печать чека по завершению визита автоматом',
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
