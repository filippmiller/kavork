<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 20.09.18
 * Time: 19:20
 */

use frontend\modules\shop\models\ShopProduct;
use kartik\touchspin\TouchSpin;
use yii\helpers\Html;

/* @var $model ShopProduct */

$id = $model->id;

$quantity = $model->getQuantity();

$isActive = is_null($quantity) || $quantity > 0;

$value = isset($produnct_buy[$id]) ? $produnct_buy[$id]['quantity'] : false;;

if (!function_exists('clean_str_for_find')) {
  function clean_str_for_find($txt)
  {
    $txt = str_replace('.', '', $txt);
    $txt = str_replace(' ', '', $txt);
    $txt = mb_strtolower($txt);
    return $txt;
  }
}
?>
<div
    class="col-sm-4 col-md-3 col-lg-2 shop-modal-list-products-product shop_item <?= ($isActive) ? 'is-active' : 'is-not-active'; ?>"
    data-category="<?= $model->category ? $model->category->title : ''; ?>"
    data-title="<?= clean_str_for_find($model->title); ?>"
    data-barcode="<?= clean_str_for_find($model->barcode); ?>"
>
  <div class="tile modernui-bg tile bg-white<?= $value ? ' selected' : ''; ?>"
       style="background:url('<?= $model->getImageUrl(); ?>') center center no-repeat;">
  </div>
  <div class="text_shop">
    <?= $model->title; ?>
  </div>


  <?php
  if ($isActive) {
    echo TouchSpin::widget([
        'name' => "products[$id][quantity]",
        'value' => $value ? $value : 1,
        'pluginOptions' => [
            'step' => 1,
            'min' => 1,
            'max' => is_null($quantity) ? 99999 : $quantity,
            'buttonup_txt' => '<i class="fa fa-plus"></i>',
            'buttondown_txt' => '<i class="fa fa-minus"></i>',
        ],
        'options' => [
            'disabled' => $value ? false : 'disabled',
        ],
    ]);
  } else {
    echo Html::tag('div', Yii::t('main', 'Out of stock'), [
        'class' => 'text-center out_of_stock',
    ]);
  }
  ?>


  <?php
  echo Html::a(Yii::t('main', 'VIEW INFO'), '#', [
      'class' => 'btn btn-default btn-sm btn-block shop_view',
      'role' => 'button',
      'data' => [
          'toggle' => 'popover',
          'placement' => 'top',
          'content' => $this->render('_product_item_popover', ['model' => $model]),
      ],
  ]);
  ?>

  <h5 class="shop_price">
    <?= $model->price; ?> $
  </h5>
</div>
