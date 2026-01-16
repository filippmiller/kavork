<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 21.09.18
 * Time: 15:05
 */

use common\components\VatHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

if (!empty($visit)) {
  $removeUrl = Url::to(['/shop/storefront/cart-remove-unit', 'visit_id' => $visit->id]);
} else {
  $removeUrl = Url::to(['/shop/storefront/cart-remove-unit']);
}

$js = <<<JS
$('.table_cart').on('click', '.__shop_cart_remove__', function(e) {
  e.preventDefault();
  
  var el = $(this);
  var id = el.attr('data-id');
  var tr = el.closest('tr');
  var quantityField = tr.find('.__quantity__');
  var quantity = parseInt(quantityField.text());
  
  var newQuantity = quantity - 1;
  
  if (newQuantity > 0) {
    quantityField.text(newQuantity);
  } else {
    tr.remove();
  }
  
  $.ajax({
      url: '$removeUrl',
      method: 'POST',
      data: {id: id},     
  }).done(function(data) {
      if (newQuantity > 0) {
        tr.replaceWith(data.cartItemView);
      }
      
      $('.shop-cart-summary').replaceWith(data.cartSummaryView);          
  });  
})
JS;
$this->registerJs($js);

?>
<table class="table table-striped table_cart">
  <thead>
  <tr>
    <td>
      <h6><b><?= Yii::t('app', 'Item'); ?></b></h6>
    </td>
    <td>
      <h6><b><?= Yii::t('app', 'Count'); ?></b></h6>
    </td>
    <td>
      <h6><b><?= Yii::t('app', 'Price'); ?></b></h6>
    </td>
    <td>
      <h6><b><?= Yii::t('app', 'Sum'); ?></b></h6>
    </td>
    <td></td>
  </tr>
  </thead>
  <tbody>
  <?php
  foreach ($transactions as $transaction) {
    echo $this->render('_cart_item', ['transaction' => $transaction]);
  }
  ?>
  </tbody>
</table>

<div class="text-right">
  <?php

  if (!empty($transactions)) {
    $sum = array_sum(ArrayHelper::getColumn($transactions, 'sum'));
    $cost = array_sum(ArrayHelper::getColumn($transactions, 'cost'));
    $quantitySummary = array_sum(ArrayHelper::getColumn($transactions, 'quantity'));

    list($sum, , $vat, $vatSummary) = VatHelper::calculate($sum);

    echo $this->render('_cart_summary', [
        'sum' => $sum,
        'cost' => $cost,
        'vat' => $vat,
        'vatSummary' => $vatSummary,
        'quantitySummary' => $quantitySummary,
        'visit' => $visit,
        'visitor' => $visit ? $visit->visitor : null,
    ]);
  }
  ?>
</div>