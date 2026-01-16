<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 20.09.18
 * Time: 15:53
 */

use yii\helpers\Url;

?>

<div class="row shop-sale-before-grid-menu">
  <div class="col-md-3 col-sm-hidden">
<?php if (false) /*(Yii::$app->user->can('ShopInventoryView'))*/:?>
      <a href="<?= Url::to(['/shop/inventory/index']); ?>" class="tile bg-white" data-click="transform" data-pjax="0">
          <span class="tile-content icon ">
        <span class="icon icon-forklift-with-boxes"></span>
      </span>
        <span class="brand">
      <span class="tile-label modernui-neutral"><?= Yii::t('main', 'Commodity research'); ?></span>
    </span>
      </a>

      <!--<div class="bk-widget bk-bd-off bk-webkit-fix">
          <a href="/shop/inventory/index" class="panel-body bk-bg-primary bk-bg-darken bk-fg-white" data-pjax="0">
            <div class="row">
              <div class="col-xs-9 text-left">
                <h3 class="bk-mg-off bk-300"><?= $inventory->totalQuantity ? $inventory->totalQuantity : 0 ?></h3>
                <span class="bk-sml text-uppercase"><?= Yii::t('main', 'items for sale'); ?></span><br>
                <hr class="bk-hr-white bk-opacity-2 margin-off-top bk-mg-b-5">
                <span class="bk-600 text-uppercase"><?= Yii::t('main', 'Commodity research'); ?></span>
              </div>
              <div class="col-xs-3 text-right">
                <i class="icon icon-forklift-with-boxes fa-3x bk-mg-r-5"></i>
              </div>
            </div>
          </a>
        </div>-->
    <?php endif; ?>
  </div>
  <div class="col-md-3 col-sm-6">
    <?php if (Yii::$app->user->can('ShopInventoryView')): ?>
      <div class="bk-widget bk-bd-off bk-webkit-fix">
        <a href="/shop/inventory/shop" class="panel-body bk-bg-primary bk-bg-darken bk-fg-white" data-pjax="0">
          <div class="row">
            <div class="col-xs-8 text-left">
              <h3 class="bk-mg-off bk-300"><?= $inventory->totalQuantity ? $inventory->totalQuantity : 0 ?></h3>
              <span class="bk-sml text-uppercase"><?= Yii::t('main', 'items for sale'); ?></span><br>
              <hr class="bk-hr-white bk-opacity-2 margin-off-top bk-mg-b-5">
              <span class="bk-600 text-uppercase"><?= Yii::t('main', 'products'); ?></span>
            </div>
            <div class="col-xs-4 text-right">
              <i class="icon-metro-box fa-3x bk-mg-r-5"></i>
            </div>
          </div>
        </a>
      </div>
    <?php endif; ?>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="bk-widget bk-bd-off bk-webkit-fix">
      <a href="/shop/sale/index" class="panel-body bk-bg-success bk-bg-darken bk-fg-white" data-pjax="0">
        <div class="row">
          <div class="col-xs-8 text-left">
            <h3 class="bk-mg-off bk-300"><?= $sales->totalQuantity ? $sales->totalQuantity : 0; ?></h3>
            <span class="bk-sml text-uppercase"><?= Yii::t('main', 'sold items'); ?></span><br>
            <hr class="bk-hr-white bk-opacity-2 margin-off-top bk-mg-b-5">
            <span class="bk-600 text-uppercase"><?= Yii::t('main', 'sales'); ?></span>
          </div>
          <div class="col-xs-4 text-right">
            <i class="icon-metro-basket fa-3x bk-mg-r-5"></i>
          </div>
        </div>
      </a>
    </div>
  </div>
  <div class="col-md-3 col-sm-12">
    <?php
    if (isset($is_sale_page) && $is_sale_page) {
      echo $this->render('sales_summary', ['sales' => $sales]);
    }
    ?>
  </div>
</div>
<br>