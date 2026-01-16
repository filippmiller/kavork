<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 20.09.18
 * Time: 19:06
 */

use yii\bootstrap\Tabs;

if (!isset($produnct_buy)) {
  $produnct_buy = false;
}

$visitor = !empty($visitor) ? $visitor : (isset($visit) ? $visit->visitor : null);
?>
<div id="__shop_new_item_view__" class="hidden">
  <?= $this->render('_new_fake_item_form'); ?>
</div>
<div id="__shop_default_view__">
  <div id="shop-modal-list">
    <?php
    if (!$visit_data) $visit_data = false;
    $items = [
        [
            'label' => '<div class="shop_label"><span class="icon-metro-cart"></span> ' . Yii::t('app', 'Shop Products') . '</div>',
            'content' => $this->render('_tab_products', [
                    'visit' => $visit,
                    'products' => $products,
                    'visitor' => $visitor,
                    'visit_data' => $visit_data,
                    'produnct_buy' => $produnct_buy,
                ]) .
                $this->render('footer', [
                    'cafe' => $cafe,
                    'visitor' => $visitor,
                    'visit' => $visit,
                    'is_visit' => $is_visit,
                ])
          ,
            'active' => !$isCart,
			'encode'=>false,
        ],
    ];

    if ($visit && !empty($transactions)) {
      $items[] = [
          'label' => '<div class="shop_label"><span class="icon-metro-basket"></span> ' . Yii::t('app', 'Cart') . '</div>',
          'content' => $this->render('_tab_cart', [
              'cafe' => $cafe,
              'transactions' => $transactions,
              'visitor' => $visitor,
              'visit' => $visit,
          ]),
          'active' => $isCart,
		  'encode'=>false,
      ];
    }
	  
	  echo Tabs::widget([
        'options' => [
            'class' => 'nav-justified',
        ],
        'items' => $items,]);
    ?>
  </div>
</div>
