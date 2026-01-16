<div class="row">
  <div class="col-md-12">
    <div id="pull_tot">
      <div class="head">
        <span class="name"></span>
        <span class="time"><?= Yii::t('app', 'TIME'); ?></span>
        <span class="cost"><?= Yii::t('app', 'SUM'); ?></span><span class="plus"></span>
        <span class="cost"><?= Yii::t('app', 'VAT'); ?></span><span class="rawn"></span>
        <span class="cost"><?= Yii::t('app', 'TOTAL'); ?></span>
        <span class="count"><?= Yii::t('app', 'COUNT'); ?></span>
      </div>
      <hr class="hrmin">

      <?php if (Yii::$app->cafe->can('payNOT')) { ?>
        <div>
          <span class="name"><?= Yii::t('app', 'Not pay'); ?> :</span>
          <span class="time"><?= Yii::$app->helper->echo_time($total['by_pay'][-1]['dt']); ?></span>
          <span class="cost"><?= $total['by_pay'][-1]['sum']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="plus"><i class="fa fa-plus"></i></span>
          <span class="cost"><?= $total['by_pay'][-1]['vat']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="rawn">=</span>
          <span class="cost"><?= $total['by_pay'][-1]['cost']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="count"><?= $total['by_pay'][-1]['cnt']; ?></span>
        </div>
      <?php }; ?>
      <?php if (isset($total['by_pay'][-2]) && isset($total['by_pay'][-2]['sum'])) { ?>
        <div>
          <span class="name"><?= Yii::t('app', 'Free visit'); ?> :</span>
          <span class="time"><?= Yii::$app->helper->echo_time($total['by_pay'][-2]['dt']); ?></span>
          <span class="cost"><?= $total['by_pay'][-2]['sum']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="plus"><i class="fa fa-plus"></i></span>
          <span class="cost"><?= $total['by_pay'][-2]['vat']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="rawn">=</span>
          <span class="cost"><?= $total['by_pay'][-2]['cost']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="count"><?= $total['by_pay'][-2]['cnt']; ?></span>
        </div>
      <?php }; ?>
      <?php if (Yii::$app->cafe->can('payCash')) { ?>
        <div>
          <span class="name"><?= Yii::t('app', 'Cash'); ?> :</span>
          <span class="time"><?= Yii::$app->helper->echo_time($total['by_pay'][0]['dt']); ?></span>
          <span class="cost"><?= $total['by_pay'][0]['sum']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="plus"><i class="fa fa-plus"></i></span>
          <span class="cost"><?= $total['by_pay'][0]['vat']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="rawn">=</span>
          <span class="cost"><?= $total['by_pay'][0]['cost']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="count"><?= $total['by_pay'][0]['cnt']; ?></span>
        </div>
      <?php }; ?>
      <?php if (Yii::$app->cafe->can('payCard')) { ?>
        <div>
          <span class="name"><?= Yii::t('app', 'Card admin'); ?> :</span>
          <span class="time"><?= Yii::$app->helper->echo_time($total['by_pay'][1]['dt']); ?></span>
          <span class="cost"><?= $total['by_pay'][1]['sum']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="plus"><i class="fa fa-plus"></i></span>
          <span class="cost"><?= $total['by_pay'][1]['vat']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="rawn">=</span>
          <span class="cost"><?= $total['by_pay'][1]['cost']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="count"><?= $total['by_pay'][1]['cnt']; ?></span>
        </div>
      <?php }; ?>
      <?php if (Yii::$app->cafe->can('selfServiceHybridMode')||Yii::$app->cafe->can('selfServiceLogoutOnlyMode')) { ?>
        <div>
          <span class="name"><?= Yii::t('app', 'Card self-checkout'); ?> :</span>
          <span class="time"><?= Yii::$app->helper->echo_time($total['by_pay'][2]['dt']); ?></span>
          <span class="cost"><?= $total['by_pay'][2]['sum']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="plus"><i class="fa fa-plus"></i></span>
          <span class="cost"><?= $total['by_pay'][2]['vat']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="rawn">=</span>
          <span class="cost"><?= $total['by_pay'][2]['cost']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="count"><?= $total['by_pay'][2]['cnt']; ?></span>
        </div>
      <?php }; ?>

      <hr class="hrmin">
      <div>
        <span class="name"><?= Yii::t('app', 'Total for absent'); ?> :</span>
        <span class="time"><?= Yii::$app->helper->echo_time($total['absent']['dt']); ?></span>
        <span class="cost"><?= $total['absent']['sum']; ?> <?= Yii::$app->cafe->currency; ?></span>
        <span class="plus"><i class="fa fa-plus"></i></span>
        <span class="cost"><?= $total['absent']['vat']; ?> <?= Yii::$app->cafe->currency; ?></span>
        <span class="rawn">=</span>
        <span class="cost"><?= $total['absent']['cost']; ?> <?= Yii::$app->cafe->currency; ?></span>
        <span class="count"><?= $total['absent']['cnt']; ?></span>
      </div>
      <div>
        <span class="name"><?= Yii::t('app', 'Total for present'); ?> :</span>
        <span class="time"><?= Yii::$app->helper->echo_time($total['present']['dt']); ?></span>
        <span class="cost"><?= $total['present']['sum']; ?> <?= Yii::$app->cafe->currency; ?></span>
        <span class="plus"><i class="fa fa-plus"></i></span>
        <span class="cost"><?= $total['present']['vat']; ?> <?= Yii::$app->cafe->currency; ?></span>
        <span class="rawn">=</span>
        <span class="cost"><?= $total['present']['cost']; ?> <?= Yii::$app->cafe->currency; ?></span>
        <span class="count"><?= $total['present']['cnt']; ?></span>
      </div>
      <div class="tt">
        <span class="name"><?= Yii::t('app', 'Total'); ?> :</span>
        <span class="time"><?= Yii::$app->helper->echo_time($total['total']['dt']); ?></span>
        <span class="cost"><?= $total['total']['sum']; ?> <?= Yii::$app->cafe->currency; ?></span>
        <span class="plus"><i class="fa fa-plus"></i></span>
        <span class="cost"><?= $total['total']['vat']; ?> <?= Yii::$app->cafe->currency; ?></span>
        <span class="rawn">=</span>
        <span class="cost"><?= $total['total']['cost']; ?> <?= Yii::$app->cafe->currency; ?></span>
        <span class="count"><?= $total['total']['cnt']; ?></span>
      </div>

      <?php if ($total['tip']) { ?>
        <div>
          <span class="name"><?= Yii::t('app', 'Tips administrator(s)'); ?> :</span>
          <span class="time"></span>
          <span class="cost"></span>
          <span class="plus"></span>
          <span class="cost"></span>
          <span class="rawn">=</span>
          <span class="cost"><?= $total['tip']; ?> <?= Yii::$app->cafe->currency; ?></span>
          <span class="count"></span>
        </div>

      <?php }; ?>
    </div>
  </div>
</div>