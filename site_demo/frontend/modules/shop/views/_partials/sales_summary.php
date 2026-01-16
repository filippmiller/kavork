<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 26.09.18
 * Time: 18:37
 */

if (empty($sales->total)) {
  return '';
}

?>
<div style="background-color: rgba(255,255,255,1);">
  <div class="bk-widget bk-bd-off">
    <div class="bk-fg-inverse bk-fg-darken bk-ltr">

      <div class="bk-pd-off-t text-uppercase">
        <div class=" flex_cash">
          <div class="padding-off border-bottom-0">
            <div class="font-weight-700"><?= Yii::t('main', 'ITEM'); ?>:</div>
            <div class="font-weight-700"><?= Yii::t('main', 'COAST'); ?>:</div>
            <div class="font-weight-700"><?= Yii::t('main', 'VAT'); ?>:</div>
            <?php
            foreach ($sales->total['vat'] as $vat) {
              echo '<div class="font-weight-700">' . $vat['name'] . '</div>';
            } ?>
            <div class="font-weight-700 border-bottom-0"><?= Yii::t('main', 'TOTAL'); ?>:</div>
          </div>
          <div class="text-right border-bottom-0">
            <div>
              <span class="cash_item"><?= $sales->totalQuantity ? $sales->totalQuantity : 0; ?></span>
            </div>
            <div>
							<span
                  class="cash_cost"><?= $sales->total['sum']; ?></span> <?= Yii::$app->cafe->currency; ?>
            </div>
            <div>
							<span
                  class="cash_vat"><?= $sales->total['vat_']; ?></span> <?= Yii::$app->cafe->currency; ?>
            </div>
            <?php
            foreach ($sales->total['vat'] as $vat) {
              echo '<div class="cash_' . $vat['name'] . '">' . $vat['vat'] . ' ' . Yii::$app->cafe->currency . '</div>';
            } ?>
            <div class="border-bottom-0">
              <span class="cash_total"><?= $sales->total['cost']; ?></span> <?= Yii::$app->cafe->currency; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
