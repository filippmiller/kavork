<?php

use frontend\modules\visits\models\VisitorLog;

?>
<div class="row faric">
  <div class="col-md-3 col-sm-6">
    <span class="cnt key"><?= $total['count']; ?></span>
    <div class="fara"><?= Yii::t('report', 'Payments') ?></div>
  </div>
  <div class="col-md-3 col-sm-6">
    <span class="summ key"><?= number_format($total['sum'], 2, '.', ' '); ?> <?= $currency; ?></span>
    <div class="fara"><?= Yii::t('report', 'Sum') ?></div>
  </div>
  <div class="col-md-3 col-sm-6">
    <span class="vat key"><?= number_format($total['cost'] - $total['sum'], 2, '.', ' '); ?> <?= $currency; ?></span>
    <div class="fara"><?= Yii::t('report', 'Vat') ?></div>
  </div>
  <div class="col-md-3 col-sm-6">
    <span class="cost key"><?= number_format($total['cost'], 2, '.', ' '); ?> <?= $currency; ?></span>
    <div class="fara"><?= Yii::t('report', 'Total') ?></div>
  </div>
</div>
<table class="table">
  <?php
  $date = false;
  $out = false;
  $total = 0;
  foreach ($result as $item) {
    $t_date = date(Yii::$app->params['lang']['date'], strtotime($item['finish']));
    if ($t_date != $date) {
      if ($out) {
        ?>
        <tr class="head">
          <td colspan="3"><?= $date; ?></td>
          <td style="white-space:nowrap;"><?= number_format($total, 2, '.', ' '); ?> <?= $currency; ?></td>
        </tr>
        <?= $out; ?>
        <?php
      }
      $out = '';
      $total = 0;
      $date = $t_date;
    }
    $total += $item['cost'];

    $link = '';
    if ($item['source'] == 'visit') {
      //$link = 'role="modal-remote" href="/visits/default/view?id=' . $item['id'] . '"';
    } else {
      //$link = 'role="modal-remote" href="/shop/report/view?id=' . $item['id'] . '"';;
    }
    $link = 'role="modal-remote" href="/report/admin/view?id=' . $item['id'];
    if($source==2){
      $link.='&source=2';
    }elseif($source==3){
      $link.='&source=3';
    }
    $link.='"';
    
    $out .= '<tr class="ups" ' . $link . '>
      <td><i class="fa ' . ($item['pay_state'] == VisitorLog::PAY_METHOD_CARD ? 'fa-credit-card' : 'fa-money') . ' big"></i></td>'.
      //'<td>' . Yii::t('app', $item['source']) . '</td>'.
      '<td>' . date(Yii::$app->params['lang']['time'], strtotime($item['finish'])) . '</td>
      <td>' . $controller->getUser(!empty($item['pay_man'])?$item['pay_man']:$item['visitor_id']) . '</td>
      <td style="white-space:nowrap;">' . number_format($item['cost'], 2, '.', ' ') . ' ' . $currency . '</td>
      </tr>';
  }

  if (!empty($out)) {
    ?>
    <tr class="head">
      <td colspan="3"><?= $date; ?></td>
      <td style="white-space:nowrap;"><?= number_format($total, 2, '.', ' '); ?> <?= $currency; ?></td>
    </tr>
    <?= $out; ?>
    <?php
  }
  ?>
</table>
