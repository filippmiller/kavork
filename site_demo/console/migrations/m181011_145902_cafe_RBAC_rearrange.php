<?php

use yii\db\Migration;

/**
 * Class m181011_145902_cafe_RBAC_rearrange
 */
class m181011_145902_cafe_RBAC_rearrange extends Migration
{
  public $tableName = '{{%cafe_auth_item}}';

  public $items = [
      'adminLog' => [
          'adminReport',
          'adminTable',
          'adminTable',
          'sessionAutoStart',
          'sessionStartPasswordRequest',
          'sessionStopPasswordRequest',
      ],
      'shop' => [
          'shopListToBay',
          'shopMerchantOnMain',
          'shopPrintCheck',
          'shopReport',
      ],
      'startVisit' => [
          'unite',
          'UpdateVisitorOnVisit',
          'certificate',
          'personsLimit',
          'endVisitPrintCheckAuto',
          'endVisitPrintCheckManual',
          'endVisitMailCheckManual',
          'ChangeVisitorOnVisit',
      ],
  ];

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    foreach ($this->items as $parent => $items) {
      $this->update($this->tableName, ['parent' => $parent], ['name' => $items]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    foreach ($this->items as $parent => $items) {
      $this->update($this->tableName, ['parent' => null], ['name' => $items]);
    }
  }
}
