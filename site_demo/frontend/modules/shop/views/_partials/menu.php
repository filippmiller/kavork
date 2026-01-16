<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 19.09.18
 * Time: 15:39
 */

use yii\bootstrap\Nav;

?>
<?php

$user = Yii::$app->user;
//$this->all_params['back_url'] = "/shop/sale/index";
echo Nav::widget([
    'encodeLabels' => false,
    'options' => [
        'class' => 'nav-tabs flex-tabs',
    ],
    'items' => [
      /*[
          'label'   => '<i class="fa fa-usd"></i> ' . Yii::t('main','Sale'),
          'url'     => ['/shop/sale/index'],
      ],*/
        [
            'label' => '<span class="tile-content icon"><i class="fa fa-list-ol"></i></span><span class="brand">
      <span class="tile-label">' . Yii::t('main', 'INVENTORY'). '</span></span>',
            'url' => ['/shop/inventory/index'],			
            'visible' => $user->can('ShopInventoryView'),
        ],
        [
            'label' => '<span class="tile-content icon"><i class="fa fa-list"></i></span><span class="brand">
      <span class="tile-label">' . Yii::t('main', 'CATALOGUE'). '</span></span>',
            'url' => ['/shop/catalog/index'],
            'visible' => $user->can('ShopCatalogView'),
        ],
        [
            'label' => '<span class="tile-content icon"><i class="icon-metro-upload"></i></span><span class="brand">
      <span class="tile-label">' . Yii::t('main', 'GOODS LEAVING'). '</span></span>',
            'url' => ['/shop/goods-leaving/index'],
            'visible' => $user->can('ShopGoodsLeavingView'),
        ],
        [
            'label' => '<span class="tile-content icon"><i class="icon-metro-download"></i></span><span class="brand">
      <span class="tile-label">' . Yii::t('main', 'GOODS ENTERING'). '</span></span>',
            'url' => ['/shop/goods-entering/index'],
            'visible' => $user->can('ShopGoodsEnteringView'),
        ],
        [
            'label' => '<span class="tile-content icon"><i class="icon-metro-clipboard-2"></i></span><span class="brand">
      <span class="tile-label">' . Yii::t('main', 'REPORTS'). '</span></span>',
            'url' => ['/shop/report/index'],
            'visible' => Yii::$app->cafe->can('ReportView') && Yii::$app->user->can('ReportView'),
        ],
        [
            'label' => '<span class="tile-content icon"><i class="fa fa-shopping-cart"></i></span><span class="brand">
      <span class="tile-label">' . Yii::t('main', 'LIST TO BUY'). '</span></span>',
            'url' => ['/shop/to-buy/index'],
            'visible' => Yii::$app->cafe->can('shopListToBay') && Yii::$app->user->can('ShopCatalogView'),
        ],
        [
            'label' => '<span class="tile-content icon"><i class="fa fa-truck"></i></span><span class="brand">
      <span class="tile-label">' . Yii::t('main', 'SUPPLIERS'). '</span></span>',
            'url' => ['/shop/supplier/index'],
            'visible' => $user->can('ShopSupplierView'),
        ],
        [
            'label' => '<span class="tile-content icon"><i class="fa fa-sitemap"></i></span><span class="brand">
      <span class="tile-label">' . Yii::t('main', 'CATEGORIES'). '</span></span>',
            'url' => ['/shop/category/index'],
            'visible' => $user->can('ShopCategoryView'),
        ],
    ],
]);
?>