<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 21.09.18
 * Time: 17:35
 */
?>
<div class="row pay_shop">
<div class="col-md-12">
<?php if ($visitor): ?>
  <h6 class="vid_check"><?= Yii::t('main', 'Visitor'); ?>: <span
        class="pull-right"><?= $visitor->f_name; ?> <?= $visitor->l_name; ?></span></h6>
  <h6 class="vid_check"><?= Yii::t('main', 'Email'); ?>: <span class="pull-right"><?= $visitor->email; ?></span></h6>
  <h6 class="vid_check"><?= Yii::t('main', 'Phone'); ?>: <span class="pull-right"><?= $visitor->phone; ?></span></h6>
<?php else: ?>
  <h6 class="vid_check"><?= Yii::t('main', 'Visitor'); ?>: <span
        class="pull-right"><?= Yii::t('app', 'Anonymous'); ?></span></h6>
<?php endif; ?>
<hr class="vid_check_hr push-down-margin-thin">
  <h4 class="vid_check font-weight-700"><?= Yii::t('main', 'Shopping'); ?>: <span
          class="pull-right"><?= number_format($cost, 2, '.', ' '); ?> <?= Yii::$app->cafe->currency; ?></span></h4>
</div>
</div>
<?php /*
        <!-- <h6 class="quantity vid_check">
          <b><?= Yii::t('main', 'Item'); ?>,<?= Yii::t('main', 'pcs'); ?>: <span class="pull-right"><?= $quantitySummary ?> </span></b>
        </h6>
        <h5 class="sum vid_check">
          <b><?= Yii::t('main', 'Sum item'); ?>: <span class="pull-right"><?= $sum ?> <?= Yii::$app->cafe->currency; ?></span></b>
        </h5>
        <h5 class="vat">
          <b><?= Yii::t('main', 'Tax'); ?>: <span class="pull-right"><?= $vatSummary ?> <?= Yii::$app->cafe->currency; ?></span></b>
        </h5>
		<hr class="vid_check_hr">
        <h4 class="tot total_pursh">
          <b><?= Yii::t('main', 'Total for purchase'); ?>: <span class="pull-right"><?= $cost ?> <?= Yii::$app->cafe->currency; ?></span></b>
        </h4>-->

 */ ?>