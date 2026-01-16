<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 21.09.18
 * Time: 15:05
 */

use frontend\modules\shop\models\ShopProduct;
use yii\bootstrap\ActiveForm;

$js = <<<JS
shop_recalculate();
JS;

$this->registerJs($js);

$actionUrlParams = ['/shop/storefront/index'];

if ($visit) {
  $actionUrlParams['visit_id'] = $visit->id;
}

?>

<div class="row">
  <div class="col-lg-4 col-md-6">
    <?= \yii\helpers\Html::input('text', 'filter_name', '', ["placeholder" => Yii::t('app', "Search by name and barcode"), "class" => 'form-control']); ?>
  </div>
  <div class="col-lg-4 col-md-6">
    <?= \yii\helpers\Html::dropDownList('filter_cat', '', ["" => Yii::t('app', "All categories")], ["class" => 'form-control']); ?>
  </div>

</div>
<div class="row shop-modal-list-products">
  <?php $form = ActiveForm::begin([
      'id' => 'shop-modal-list-products-form',
      'action' => $actionUrlParams,
      'enableClientValidation' => false,
  ]); ?>
  <?php
  if ($visit_data) {
    echo \yii\helpers\Html::input('hidden', 'visit_data', $visit_data);
    if ($visitor)
      echo \yii\helpers\Html::input('hidden', 'StartVisit[id]', $visitor->id);
  };
  ?>
  <?php
  if (!empty($produnct_buy)) {
    foreach ($produnct_buy as $productId => $productAttributes) {
      if ($productId > 0) {
        continue;
      }

      $fakeModel = new ShopProduct();
      $fakeModel->setAttributes($productAttributes);
      $fakeModel->id = $productId;
      echo $this->render('_new_fake_item_template', ['model' => $fakeModel]);
    }
  }
  foreach ($products as $product) {
    echo $this->render('_product_item', ['model' => $product, 'produnct_buy' => $produnct_buy]);
  }
  ?>
  <?php ActiveForm::end(); ?>
</div>

<script>
  pruduct_filter_init();
</script>