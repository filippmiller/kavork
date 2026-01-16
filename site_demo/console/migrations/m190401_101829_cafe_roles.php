<?php

use yii\db\Migration;

/**
 * Class m190401_101829_cafe_roles
 */
class m190401_101829_cafe_roles extends Migration
{
  public $tableName = '{{%cafe_auth_item}}';

  public $itemTableName = '{{%cafe_auth_item}}';
  public $assignmentTableName = '{{%cafe_auth_assignment}}';

  public $items_remove = [
      'selfServiceHybridMode' => 'Self Service - Entry and Exit',
      'shop' => 'Score',
  ];
  public $items_new = [
      "basisAll" => 'Модули основные',
      "adminAll" => 'Модули администраторов',
      "visitAll" => 'Модули визита',
      "shopAll" => 'Модули магазина',
      "shopInSession" => 'Покупки во время сессии',
      "buttonShop" => 'Кнопка магазина',
      "quickProduct" => 'Быстрое создание товара',
      "merchandiseAll" => 'Модули тавароведения',
      "mailprintAll" => 'Модули писем и принта',
      "TemplatesView" => 'Редактор шаблонов писем и чеков',
      "reportAll" => 'Модули отчётов',
      "TransactionsView" => 'Вывод модуля транзакций',
      "selfServiceAll" => 'Модули экранов',
      "Announcement" => 'Рекламный модуль',
      "Tips" => 'Чаевые администраторам с экрана',
  ];

  public $items = [
      'basisAll' => [
          1 => 'payCash',
          2 => 'payCard',
          3 => 'payNOT',
      ],
      'adminAll' => [
          1 => 'adminLog',
          2 => 'adminTable',
          3 => 'adminReport',
          4 => 'sessionAutoStart',
          5 => 'sessionStartPasswordRequest',
          6 => 'sessionStopPasswordRequest',
          7 => 'Timetable',
          8 => 'task',
      ],
      'visitAll' => [
          1 => 'startVisit',
          2 => 'AnonymousVisitor',
          3 => 'personsLimit',
          4 => 'certificate',
          5 => 'unite',
          6 => 'ChangeVisitorOnVisit',
          7 => 'Polls',
      ],
      'shopAll' => [
          1 => 'shopInSession',
          2 => 'buttonShop',
          3 => 'quickProduct',
          4 => 'shopPrintCheck',
      ],
      'merchandiseAll' => [
          1 => 'shopListToBay',
          2 => 'shopReport',
          3 => 'shopMerchantOnMain',
      ],
      'mailprintAll' => [
          1 => 'endVisitMailCheckManual',
          2 => 'endVisitPrintCheckManual',
          3 => 'endVisitPrintCheckAuto',
          4 => 'TemplatesView',
          5 => 'mails',
      ],

      'reportAll' => [
          1 => 'ReportMail',
          2 => 'ReportView',
          3 => 'TransactionsView',
      ],

      'selfServiceAll' => [
          1 => 'selfServiceLoginOnlyMode',
          2 => 'selfServiceLogoutOnlyMode',
          3 => 'Announcement',
          4 => 'Tips',
      ],
  ];


  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    foreach ($this->items_new as $itemName => $itemDescription) {
      $this->insert($this->itemTableName, [
          'name' => $itemName,
          'description' => $itemDescription,
          'created_at' => time(),
          'updated_at' => time(),
      ]);
    }

    foreach ($this->items_remove as $itemName => $itemDescription) {
      $this->delete($this->itemTableName, [
          'name' => $itemName,
      ]);
    }

    $query = new \yii\db\Query();
    $query->select(['id'])->from('{{%cafe}}')->orderBy('id');

    foreach ($query->each() as $cafe) {
      foreach ($this->items_new as $itemName => $itemDescription) {
        $this->insert($this->assignmentTableName, [
            'item_name' => $itemName,
            'cafe_id' => $cafe['id'],
            'created_at' => time(),
        ]);
      }
    }

    $dbh = Yii::$app->getDb();;
    $dbh = $dbh->createCommand("SHOW COLUMNS FROM cafe_auth_item;")->queryAll();
    $dbh = \yii\helpers\ArrayHelper::map($dbh, 'Field', 'Field');
    if (!isset($dbh['sort_index'])) {
      $this->addColumn($this->tableName, 'sort_index', $this->integer()->after('parent'));
    }

    $parent_k = 0;
    foreach ($this->items as $parent => $items) {
      $parent_k++;
      $this->update(
          $this->tableName, [
          'sort_index' => $parent_k
      ], [
          'name' => $parent
      ]);

      foreach ($items as $k => $item) {
        $this->update(
            $this->tableName, [
            'parent' => $parent,
            'sort_index' => $k
        ], [
            'name' => $item
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    foreach ($this->items_new as $itemName => $itemDescription) {
      $this->delete($this->itemTableName, [
          'name' => $itemName,
      ]);
    }

    foreach ($this->items_remove as $itemName => $itemDescription) {
      $this->insert($this->itemTableName, [
          'name' => $itemName,
          'description' => $itemDescription,
          'created_at' => time(),
          'updated_at' => time(),
      ]);
    }
  }

}
