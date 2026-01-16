<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 21.09.18
 * Time: 16:14
 */

$currency = Yii::$app->cafe->getCurrency();

?>
<div class="shop-cart-summary">
  <div class="caclc_summ_cart">
    <div class="row">
      <div class="col-md-6 text-left">
        <h5 class="buyer_info">
          <?= Yii::t('main', 'Buyer'); ?>
        </h5>
        <?php if ($visitor): ?>
          <h6 class="vid_check"><?= Yii::t('main', 'Visitor'); ?>: <span
                class="pull-right"><?= $visitor->f_name; ?> <?= $visitor->l_name; ?></span></h6>
          <h6 class="vid_check"><?= Yii::t('main', 'Email'); ?>: <span
                class="pull-right"> <?= $visitor->email; ?></span></h6>
          <h6 class="vid_check"><?= Yii::t('main', 'Phone'); ?>: <span class="pull-right"><?= $visitor->phone; ?></span>
          </h6>
        <?php else: ?>
          <h6 class="vid_check"><?= Yii::t('main', 'Visitor'); ?>: <span
                class="pull-right"><?= Yii::t('app', 'Anonymous'); ?></span></h6>
        <?php endif; ?>
      </div>
      <div class="col-md-6 text-left">
        <h5 class="price_calc">
          <?= Yii::t('app', 'Cart'); ?>
        </h5>
        <h6 class="quantity vid_check font-weight-700">
         <?= Yii::t('main', 'Item'); ?>,<?= Yii::t('main', 'pcs'); ?>: <span
                class="pull-right"><?= $quantitySummary ?> </span>
        </h6>
        <h5 class="sum vid_check font-weight-700">
          <?= Yii::t('main', 'Sum item'); ?>: <span class="pull-right"><?= $sum ?> <?= $currency; ?></span>
        </h5>
        <h5 class="vat font-weight-700">
          <?= Yii::t('main', 'Tax'); ?>: <span class="pull-right"><?= $vatSummary ?> <?= $currency; ?></span>
        </h5>
        <hr class="vid_check_hr">
        <h4 class="tot total_pursh font-weight-700">
          <?= Yii::t('main', 'Total for purchase'); ?>: <span
                class="pull-right"><?= $cost ?> <?= $currency; ?></span>
        </h4>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <?php
      echo \yii\helpers\Html::a('<i class="icon-metro-arrow-left"></i> ' .Yii::t('app', 'Back'). '',
          ['/visits/view', 'id' => $visit->id],
          ['class' => 'btn btn-default pull-left', "role" => "modal-remote"]
      );
      ?>
    </div>
  </div>
</div>
