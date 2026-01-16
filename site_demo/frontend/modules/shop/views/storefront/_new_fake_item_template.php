<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 03.10.18
 * Time: 18:31
 */

$currency = Yii::$app->cafe->getCurrency();

$id = !empty($model->id) ? $model->id : '{ randId }';
$title = !empty($model->title) ? $model->title : '{ title }';
$price = !empty($model->title) ? $model->price : '{ price }';
$quantity = !empty($model->quantity) ? $model->quantity : '{ quantity }';

?>
<div class="col-sm-4 col-md-3 col-lg-2 shop-modal-list-products-product shop_item is-active">
  <input type="hidden" class="__shop_new_product_template__title_" name="products[<?= $id ?>][title]"
         value="<?= $title; ?>">
  <input type="hidden" class="__shop_new_product_template__price_" name="products[<?= $id ?>][price]"
         value="<?= $price; ?>">
  <input type="hidden" class="__shop_new_product_template__tax_required_" name="products[<?= $id ?>][tax_required]"
         value="<?= $model->tax_required; ?>">
  <div class="tile modernui-bg tile bg-white selected"
       style="background:url('/img/img_blank.png') center center no-repeat;">
  </div>
  <div class="__shop_new_product_template__title_block_ text_shop"><?= $title; ?></div>
  <input type="text" class="__shop_new_product_template__quantity_" id=products_quantity<?= $id ?>
         name="products[<?= $id ?>][quantity]" value="<?= $quantity; ?>" class="form-control">
  <div class="btn btn-default btn-sm btn-block shop_view"><?= Yii::t('app', 'NO INFO'); ?></div>
  <h5 class="__shop_new_product_template__price_block_ shop_price">
    <?= $price; ?> <?= $currency; ?>
  </h5>
</div>
<script>
  jQuery('#products_quantity<?= $id ?>').TouchSpin({
    'buttondown_class': 'btn btn-default',
    'buttondown_txt': '<i class="fa fa-minus"></i>',
    'buttonup_class': 'btn btn-default',
    'buttonup_txt': '<i class="fa fa-plus"></i>',
    'max': 1000,
    'min': 1,
    'step': 1,
  })
</script>
