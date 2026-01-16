<?php

use yii\db\Migration;

/**
 * Class m180916_124322_add_shop_RBAC
 */
class m180916_114858_add_shop_RBAC extends Migration
{
  /* @var \yii\rbac\ManagerInterface */
  public $auth;

  public $items = [
    // Supplier START
      [
          'name' => 'ShopSupplierView',
          'description' => 'Shop - просмотр поставщиков',
      ],
      [
          'name' => 'ShopSupplierUpdate',
          'description' => 'Shop - редактирование поставщиков',
      ],
      [
          'name' => 'ShopSupplierDelete',
          'description' => 'Shop - удаление поставщиков',
      ],
      [
          'name' => 'ShopSupplierCreate',
          'description' => 'Shop - создание поставщиков',
      ],
    // Category START
      [
          'name' => 'ShopCategoryView',
          'description' => 'Shop - просмотр категорий',
      ],
      [
          'name' => 'ShopCategoryUpdate',
          'description' => 'Shop - редактирование категорий',
      ],
      [
          'name' => 'ShopCategoryDelete',
          'description' => 'Shop - удаление категорий',
      ],
      [
          'name' => 'ShopCategoryCreate',
          'description' => 'Shop - создание категорий',
      ],
    // Inventory START
      [
          'name' => 'ShopInventoryCreate',
          'description' => 'Shop - создание товаров в INVENTORY(shop)',
      ],
      [
          'name' => 'ShopInventoryView',
          'description' => 'Shop - просмотр товаров в INVENTORY',
      ],
      [
          'name' => 'ShopInventoryUpdate',
          'description' => 'Shop - редактирование товаров в INVENTORY',
      ],
    // Catalog START
      [
          'name' => 'ShopCatalogView',
          'description' => 'Shop - просмотр товаров в CATALOG',
      ],
      [
          'name' => 'ShopCatalogUpdate',
          'description' => 'Shop - редактирование товаров в CATALOG',
      ],
      [
          'name' => 'ShopCatalogDelete',
          'description' => 'Shop - удаление товаров в CATALOG',
      ],
      [
          'name' => 'ShopCatalogCreate',
          'description' => 'Shop - создание товаров в CATALOG',
      ],
    // GOODS LEAVING START
      [
          'name' => 'ShopGoodsLeavingView',
          'description' => 'Shop - просмотр Goods Leaving',
      ],
      [
          'name' => 'ShopGoodsLeavingDelete',
          'description' => 'Shop - удаление Goods Leaving',
      ],
    // GOODS ENTERING START
      [
          'name' => 'ShopGoodsEnteringView',
          'description' => 'Shop - просмотр Goods Entering',
      ],
      [
          'name' => 'ShopGoodsEnteringDelete',
          'description' => 'Shop - удаление Goods Entering',
      ],
    // Sale START
      [
          'name' => 'ShopSaleView',
          'description' => 'Shop - просмотр продаж',
      ],
      [
          'name' => 'ShopSaleUpdate',
          'description' => 'Shop - редактирование продаж',
      ],
      [
          'name' => 'ShopSaleDelete',
          'description' => 'Shop - удаление продаж',
      ],
      [
          'name' => 'ShopSaleCreate',
          'description' => 'Shop - создание продаж',
      ],
    // Transaction START
      [
          'name' => 'ShopTransactionView',
          'description' => 'Shop - просмотр транзакций',
      ],
      [
          'name' => 'ShopTransactionUpdate',
          'description' => 'Shop - редактирование транзакций',
      ],
      [
          'name' => 'ShopTransactionDelete',
          'description' => 'Shop - удаление транзакций',
      ],
      [
          'name' => 'ShopTransactionCreate',
          'description' => 'Shop - создание транзакций',
      ],
      [
          'name' => 'ShopReportView',
          'description' => 'Shop - отчет движения товаров',
      ],
  ];

  public function up()
  {
    //применить миграцию
    $this->auth = \Yii::$app->authManager;
    $defaultRoles = [
        $this->auth->getRole('root'),
    ];

    foreach ($this->items as $item) {
      $this->createPermission($item['name'], $item['description'], $defaultRoles);
    }
  }

  public function down()
  {
    $this->auth = \Yii::$app->authManager;
    foreach ($this->items as $item) {
      $permission = $this->auth->getPermission($item['name']);
      if ($permission) {
        $this->auth->remove($permission);
      }
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
